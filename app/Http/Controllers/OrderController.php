<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Services\MidtransPaymentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        private readonly MidtransPaymentService $midtrans,
    ) {
    }

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['package', 'videoOrder.video'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('orders.index', compact('orders'));
    }

    public function adminShow($id)
    {
        $order = Order::with(['user', 'package', 'videoOrder.video'])
            ->where('id', $id)
            ->firstOrFail();

        $enrollment = $order->enrollment ?? [];
        $paymentHistory = $this->getPaymentHistory($order);
        $packages = Package::all();

        return view('admin.orders.detail', compact('order', 'enrollment', 'paymentHistory', 'packages'));
    }

    private function getPaymentHistory($order)
    {
        $history = [];

        if ($order->payment_time) {
            $history[] = [
                'status' => $order->payment_status,
                'time' => $order->payment_time,
                'note' => 'Pembayaran ' . ($order->payment_status === 'paid' ? 'berhasil' : 'diproses'),
                'type' => $order->payment_type ? MidtransPaymentService::methodLabel($order->payment_type) : '-',
            ];
        }

        if ($order->created_at) {
            $history[] = [
                'status' => 'created',
                'time' => $order->created_at,
                'note' => 'Pesanan dibuat',
                'type' => 'System',
            ];
        }

        usort($history, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return $history;
    }

    public function adminIndex()
    {
        $orders = Order::with(['user', 'package', 'videoOrder.video'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function create(Request $request, Package $package)
    {
        $user = Auth::user();

        // Order paid terbaru untuk paket ini (jika ada) — dipakai untuk menentukan
        // apakah ini pembelian baru atau perpanjangan membership.
        $existingOrder = Order::latestPaidFor($user->id, $package->id);
        $isRenewal = false;

        if ($existingOrder) {
            if ($existingOrder->isMembershipActive() && !$existingOrder->isMembershipExpiringSoon()) {
                $endDate = $existingOrder->membership_end
                    ? $existingOrder->membership_end->format('d M Y')
                    : '-';
                return redirect()
                    ->route('packages.show', $package->id)
                    ->with('error', 'Anda masih memiliki akses aktif ke paket ini hingga ' . $endDate . '.');
            }

            $isRenewal = true;
        }

        $totalPrice = $package->final_price;
        $isCustomAmount = false;

        if ($package->is_pay_what_you_want) {
            $minAmount = $package->minimumPayAmount();

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:' . $minAmount,
            ], [
                'amount.required' => 'Silakan masukkan nominal pembayaran.',
                'amount.numeric' => 'Nominal pembayaran harus berupa angka.',
                'amount.min' => 'Nominal minimal adalah Rp ' . number_format($minAmount, 0, ',', '.') . '.',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route('packages.show', $package->id)
                    ->withErrors($validator)
                    ->with('error', $validator->errors()->first('amount'));
            }

            $totalPrice = round((float) $request->amount);
            $isCustomAmount = true;
        }

        if ($totalPrice < 1) {
            return redirect()
                ->route('packages.show', $package->id)
                ->with('error', 'Nominal pembayaran tidak valid.');
        }

        $orderNumber = 'ORD-' . date('YmdHis') . '-' . strtoupper(Str::random(8));

        $enrollment = [
            'key' => 'ENR-' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(4)),
            'activated' => false,
            'activated_at' => null,
            'activated_by' => null,
            'sent_by_admin' => false,
            'sent_at' => null,
            'unlocked' => false,
            'unlocked_at' => null,
        ];

        $notes = [];
        if ($isRenewal) {
            $notes[] = 'Perpanjangan membership paket';
        }
        if ($isCustomAmount) {
            $notes[] = 'Pembayaran seikhlasnya oleh user';
        }

        $order = Order::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'order_number' => $orderNumber,
            'total_price' => $totalPrice,
            'is_custom_amount' => $isCustomAmount,
            'payment_status' => 'pending',
            'payment_notes' => $notes ? implode(' • ', $notes) : null,
            'enrollment' => $enrollment,
        ]);

        // Notify all admins about new order
        $admins = User::where('role', 'admin')->pluck('id');
        foreach ($admins as $adminId) {
            NotificationService::create(
                $adminId,
                'order',
                'Pesanan Baru',
                $user->name . ' memesan paket "' . ($package->title ?? '-') . '" seharga Rp ' . number_format($totalPrice, 0, ',', '.') . '. Menunggu pembayaran.',
                ['order_id' => $order->id, 'order_number' => $orderNumber, 'action_url' => route('admin.orders.index')],
                true
            );
        }

        return redirect()->route('orders.process-payment', $order);
    }

    /**
     * Halaman checkout: ringkasan pesanan + Snap token Midtrans.
     * Snap token dibuat di sini agar popup Midtrans langsung siap dibuka
     * tanpa POST tambahan dari halaman payment.
     */
    public function processPayment(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        if ($order->isVideoOrder()) {
            $videoOrder = $order->videoOrder;

            if ($videoOrder && $videoOrder->video) {
                return redirect()->route('videos.pay', ['video' => $videoOrder->video_id, 'videoOrder' => $videoOrder->id]);
            }

            return redirect()->route('orders.index')->with('error', 'Pesanan video tidak ditemukan.');
        }

        $user   = Auth::user();
        $amount = (int) $order->total_price;

        // Buat order_id Midtrans: nomor pesanan + nominal (deterministik)
        $midtransOrderId = $order->order_number . '-' . $amount;

        // Coba pakai Snap Token yang sudah ada jika belum berubah
        $snapToken = null;
        if ($order->payment_status === 'pending' && filled($order->payment_url)) {
            // payment_url kita gunakan untuk menyimpan snap_token
            $snapToken = $order->payment_url;
        }

        if (!$snapToken) {
            if (!$this->midtrans->isConfigured()) {
                return view('orders.payment', [
                    'order' => $order,
                    'snapToken' => null,
                    'clientKey' => null,
                    'snapJsUrl' => $this->midtrans->snapJsUrl(),
                    'gatewayConfigured' => false,
                    'sandboxMode' => !$this->midtrans->isProduction(),
                ]);
            }

            $params = $this->midtrans->buildSnapParams(
                orderId: $midtransOrderId,
                grossAmount: $amount,
                firstName: $user->name,
                email: $user->email,
                phone: (string) ($user->phone ?? ''),
                itemName: Str::limit($order->package?->title ?? 'Paket Bank Soal', 50),
                itemId: (string) ($order->package_id ?? ''),
                finishUrl: url('/payment/finish?order_ref=' . $order->id),
                unfinishUrl: url('/payment/finish?order_ref=' . $order->id),
                errorUrl: url('/payment/error?order_ref=' . $order->id),
            );

            try {
                $snapToken = $this->midtrans->createSnapToken($params);
            } catch (\Throwable $e) {
                Log::error('Gagal membuat Snap Token Midtrans (paket)', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->back()->with('error', '❌ Gagal menghubungi gateway pembayaran. Silakan coba lagi.');
            }

            $order->update([
                'transaction_id' => $midtransOrderId,
                'payment_url' => $snapToken,
            ]);
        }

        return view('orders.payment', [
            'order' => $order,
            'snapToken' => $snapToken,
            'clientKey' => config('midtrans.client_key'),
            'snapJsUrl' => $this->midtrans->snapJsUrl(),
            'gatewayConfigured' => $this->midtrans->isConfigured(),
            'sandboxMode' => !$this->midtrans->isProduction(),
        ]);
    }

    /**
     * Endpoint POST untuk regenerate Snap Token jika diperlukan
     * (dipakai jika user menekan "Bayar Sekarang" dari view payment).
     */
    public function pay(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return back()->with('error', 'Akses ditolak!');
        }

        if ($order->isVideoOrder()) {
            return redirect()->route('orders.index')->with('error', 'Gunakan halaman pembayaran video.');
        }

        // Arahkan ulang ke processPayment yang sudah mengurus token
        return redirect()->route('orders.process-payment', $order);
    }

    private function authorizeOrderAccess(Order $order)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu!');
        }

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak!');
        }

        return null;
    }

    /**
     * Resolve order dari parameter callback.
     */
    private function resolveOrderFromCallback(Request $request): ?Order
    {
        $orderRef = $request->get('order_ref');

        if (ctype_digit((string) $orderRef)) {
            $order = Order::find((int) $orderRef);
            if ($order) {
                return $order;
            }
        }

        if (ctype_digit((string) $request->get('order_id'))) {
            return Order::find((int) $request->get('order_id'));
        }

        return null;
    }

    /**
     * Halaman setelah pembayaran Midtrans selesai.
     * Midtrans mengembalikan parameter `order_id` dan `transaction_status`
     * di URL finish — kita TIDAK percaya parameter ini langsung; selalu
     * diverifikasi ulang ke API Midtrans.
     */
    public function paymentFinish(Request $request)
    {
        $order = $this->resolveOrderFromCallback($request);

        if (!$order) {
            return redirect()->route('orders.index')->with('error', 'Pesanan tidak ditemukan!');
        }

        if ($redirect = $this->authorizeOrderAccess($order)) {
            return $redirect;
        }

        // Sudah lunas sebelumnya (mis. lewat webhook) -> langsung tampilkan.
        if ($order->payment_status === 'paid') {
            return view('payment.finish', ['order' => $order]);
        }

        // Verifikasi ke API Midtrans menggunakan transaction_id (order_id Midtrans)
        $midtransOrderId = $order->transaction_id ?? ($order->order_number . '-' . (int) $order->total_price);
        $status = $this->midtrans->checkStatus($midtransOrderId);

        if (!$status) {
            return redirect()->route('orders.index')
                ->with('warning', '⏳ Status pembayaran belum dapat diverifikasi. Jika Anda sudah membayar, tunggu sebentar — status akan diperbarui otomatis oleh sistem.');
        }

        $mapped = MidtransPaymentService::mapStatus($status);

        if ($mapped === 'paid') {
            $updateData = ['payment_time' => now()];

            if (!empty($status->payment_type)) {
                $updateData['payment_type'] = $status->payment_type;
            }

            $order->markPaid($updateData);

            $this->logUserActivity($order->user, [
                'action' => 'Payment Success via Redirect',
                'order' => $order->order_number,
                'package' => $order->package->title ?? 'Package',
                'payment_type' => $status->payment_type ?? null,
                'transaction_id' => $status->transaction_id ?? null,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return view('payment.finish', ['order' => $order]);
        }

        if ($mapped === 'pending') {
            return redirect()->route('orders.index')
                ->with('warning', '⏳ Pembayaran sedang diproses. Silakan tunggu konfirmasi.');
        }

        return redirect()->route('orders.index')
            ->with('error', '❌ Pembayaran gagal atau belum selesai. Silakan coba lagi.');
    }

    public function paymentError(Request $request)
    {
        $orderId = $request->get('order_ref');
        $order = ctype_digit((string) $orderId) ? Order::find((int) $orderId) : null;

        if ($order && Auth::check() && $order->user_id === Auth::id()) {
            return redirect()->route('orders.index')
                ->with('error', '❌ Pembayaran untuk pesanan ' . $order->order_number . ' gagal atau dibatalkan. Silakan coba lagi.');
        }

        return redirect()->route('orders.index')
            ->with('error', '❌ Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
    }

    public function paymentStatus(Request $request)
    {
        $order = $this->resolveOrderFromCallback($request);

        if (!$order) {
            return redirect()->route('orders.index')->with('error', 'Pesanan tidak ditemukan!');
        }

        if ($redirect = $this->authorizeOrderAccess($order)) {
            return $redirect;
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.index')
                ->with('success', '✅ Pembayaran berhasil! Enroll Key akan segera dikirim oleh Admin.');
        }

        $midtransOrderId = $order->transaction_id ?? ($order->order_number . '-' . (int) $order->total_price);
        $status = $this->midtrans->checkStatus($midtransOrderId);

        if (!$status) {
            return redirect()->route('orders.index')
                ->with('warning', '⏳ Status pembayaran belum dapat diverifikasi. Silakan cek kembali beberapa saat lagi.');
        }

        $mapped = MidtransPaymentService::mapStatus($status);

        if ($mapped === 'paid') {
            $updateData = ['payment_time' => now()];

            if (!empty($status->payment_type)) {
                $updateData['payment_type'] = $status->payment_type;
            }

            $order->markPaid($updateData);

            $this->logUserActivity($order->user, [
                'action' => 'Payment Success via Status Check',
                'order' => $order->order_number,
                'package' => $order->package->title ?? 'Package',
                'payment_type' => $status->payment_type ?? null,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return redirect()->route('orders.index')
                ->with('success', '✅ Pembayaran berhasil! Enroll Key akan segera dikirim oleh Admin.');
        }

        if ($mapped === 'pending') {
            return redirect()->route('orders.index')
                ->with('warning', '⏳ Pembayaran sedang diproses. Silakan tunggu.');
        }

        return redirect()->route('orders.index')
            ->with('error', '❌ Pembayaran gagal. Silakan coba lagi.');
    }

    /**
     * Endpoint notification/webhook dari Midtrans (server-to-server).
     * TIDAK menggunakan session/CSRF — keaslian payload diverifikasi via
     * signature_key SHA512 di MidtransPaymentService::readNotification().
     */
    public function notification(Request $request)
    {
        try {
            $notif = $this->midtrans->readNotification($request);

            // Payload tidak valid / signature salah — tolak.
            if (!$notif) {
                Log::warning('Notifikasi Midtrans (paket) tidak valid atau signature salah');
                return response('FORBIDDEN', 403);
            }

            $orderId     = (string) ($notif->order_id ?? '');
            $paymentType = (string) ($notif->payment_type ?? '');

            // Exclude mirror orders video (type='video') — tiap webhook hanya
            // mengelola tabel miliknya masing-masing.
            $packageOnly = fn () => Order::query()->where(function ($q) {
                $q->whereNull('video_order_id')->where('type', '!=', 'video');
            });

            // Lookup by transaction_id (order_id Midtrans yang dikirim saat Snap)
            $order = $packageOnly()->where('transaction_id', $orderId)->first();

            // Fallback via order_number (segmen awal order_id)
            if (!$order) {
                $order = $packageOnly()
                    ->where('order_number', MidtransPaymentService::extractOrderNumber($orderId))
                    ->first();
            }

            if (!$order) {
                return response('NOT FOUND', 404);
            }

            $status = MidtransPaymentService::mapStatus($notif);

            if ($status === 'paid') {
                $updateData = ['payment_time' => now()];
                if ($paymentType) {
                    $updateData['payment_type'] = $paymentType;
                }
                $order->markPaid($updateData);

                $this->logUserActivity($order->user, [
                    'action' => 'Payment Success via Webhook',
                    'order' => $order->order_number,
                    'package' => $order->package->title ?? 'Package',
                    'payment_type' => $paymentType,
                    'transaction_id' => $notif->transaction_id ?? null,
                    'timestamp' => now()->toDateTimeString(),
                ]);
            } elseif ($status === 'failed') {
                $order->update(['payment_status' => 'failed']);
            } elseif ($status === 'expired') {
                $order->update(['payment_status' => 'expired']);
            } else {
                if ($order->payment_status === 'pending') {
                    $order->touch();
                }
            }

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('Error memproses notifikasi Midtrans (paket)', [
                'error' => $e->getMessage(),
                'order_id' => $request->input('order_id'),
            ]);
            return response('Internal error', 500);
        }
    }

    private function logUserActivity(?User $user, array $entry): void
    {
        if (!$user) {
            return;
        }

        $logs = $user->activity_logs ?? [];
        $logs[] = $entry;

        $user->activity_logs = $logs;
        $user->save();
    }

    public function activate(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        if ($order->isVideoOrder()) {
            return redirect()->route('orders.index')->with('error', 'Pesanan video tidak menggunakan Enroll Key.');
        }

        $enrollment = $order->enrollment;
        $ready = ($enrollment['sent_by_admin'] ?? false) && ($enrollment['activated'] ?? false);

        if (!$ready) {
            return redirect()->route('orders.index')->with('error', 'Enroll Key belum diaktifkan & dikirim oleh Admin. Silakan tunggu konfirmasi Admin.');
        }

        if ($request->has('enroll_key')) {
            if (hash_equals((string) $enrollment['key'], (string) $request->input('enroll_key'))) {
                $enrollment['unlocked'] = true;
                $enrollment['unlocked_at'] = now()->toDateTimeString();
                $order->update(['enrollment' => $enrollment]);
                return redirect()->route('packages.show', $order->package_id)->with('success', 'Paket berhasil diaktifkan! Selamat belajar.');
            }
            return redirect()->back()->with('error', 'Enroll Key tidak valid!');
        }

        return view('orders.activate', compact('order'));
    }

    public function verifyEnrollKey(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak!'], 403);
        }

        if ($order->isVideoOrder()) {
            return response()->json(['success' => false, 'message' => 'Pesanan video tidak menggunakan Enroll Key.'], 400);
        }

        $enrollment = $order->enrollment;
        $ready = ($enrollment['sent_by_admin'] ?? false) && ($enrollment['activated'] ?? false);

        if (!$ready) {
            return response()->json(
                ['success' => false, 'message' => 'Enroll Key belum diaktifkan & dikirim oleh Admin. Silakan tunggu.'],
                400,
            );
        }

        if (hash_equals((string) $enrollment['key'], (string) $request->input('enroll_key'))) {
            $enrollment['unlocked'] = true;
            $enrollment['unlocked_at'] = now()->toDateTimeString();
            $order->update(['enrollment' => $enrollment]);

            return response()->json([
                'success' => true,
                'message' => 'Enroll Key berhasil diverifikasi! Soal latihan sudah bisa diakses.',
                'redirect' => route('packages.show', $order->package_id),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Enroll Key tidak valid!'], 400);
    }

    public function verify(Request $request, Order $order)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $isVideo = $order->isVideoOrder();
        $order->markPaid(['payment_time' => now(), 'payment_type' => 'manual']);

        // Notify user about verified payment
        NotificationService::create(
            $order->user_id,
            $isVideo ? 'video' : 'order',
            'Pembayaran Terverifikasi',
            'Pembayaran pesanan #' . $order->order_number . ' telah diverifikasi oleh admin. ' . ($isVideo ? 'Akses video aktif.' : 'Membership aktif.'),
            ['order_id' => $order->id, 'order_number' => $order->order_number, 'action_url' => route('orders.index')],
            true
        );

        return redirect()->route('admin.orders.index')->with('success', $isVideo
            ? 'Pembayaran berhasil diverifikasi! Akses video telah diaktifkan.'
            : 'Pembayaran berhasil diverifikasi! Membership paket telah diaktifkan.');
    }

    public function activateEnrollByAdmin(Request $request, Order $order)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $enrollment = $order->enrollment;
        $enrollment['activated'] = true;
        $enrollment['activated_at'] = now()->toDateTimeString();
        $enrollment['activated_by'] = Auth::user()->name;
        $order->update(['enrollment' => $enrollment]);

        $this->logUserActivity($order->user, [
            'action' => 'Enroll Key Activated by Admin',
            'order' => $order->order_number,
            'package' => $order->package->title ?? 'Package',
            'admin' => Auth::user()->name,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Notify user about enroll key activation
        NotificationService::create(
            $order->user_id,
            'enroll',
            'Enroll Key Diaktifkan',
            'Admin telah mengaktifkan Enroll Key untuk paket "' . ($order->package->title ?? '-') . '". Silakan masukkan kunci untuk membuka materi latihan.',
            ['order_id' => $order->id, 'action_url' => route('orders.index')],
            true
        );

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Enroll Key berhasil diaktifkan untuk ' . ($order->user->name ?? 'User'));
    }

    public function sendEnrollKey(Request $request, Order $order)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $enrollment = $order->enrollment;

        if (!$enrollment || !isset($enrollment['key'])) {
            $enrollment = [
                'key' => 'ENR-' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(4)),
                'activated' => false,
                'activated_at' => null,
                'activated_by' => null,
                'sent_by_admin' => true,
                'sent_at' => now()->toDateTimeString(),
                'unlocked' => false,
                'unlocked_at' => null,
            ];
        } else {
            $enrollment['sent_by_admin'] = true;
            $enrollment['sent_at'] = now()->toDateTimeString();
            $enrollment['activated'] = false;
            $enrollment['unlocked'] = false;
            $enrollment['unlocked_at'] = null;
        }

        $order->update(['enrollment' => $enrollment]);

        $this->logUserActivity($order->user, [
            'action' => 'Enroll Key Sent by Admin',
            'order' => $order->order_number,
            'package' => $order->package->title ?? 'Package',
            'enroll_key' => Str::limit($enrollment['key'], 7, '…'),
            'admin' => Auth::user()->name,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Notify user about enroll key sent
        NotificationService::create(
            $order->user_id,
            'enroll',
            'Enroll Key Dikirim',
            'Admin telah mengirimkan Enroll Key untuk paket "' . ($order->package->title ?? '-') . '". Periksa email Anda untuk kunci aktivasi.',
            ['order_id' => $order->id, 'action_url' => route('orders.index')],
            true
        );

        return redirect()->route('admin.orders.index')->with('success', 'Enroll Key berhasil dikirim ke pengguna!');
    }
}

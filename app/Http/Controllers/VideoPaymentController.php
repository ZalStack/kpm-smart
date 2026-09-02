<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoOrder;
use App\Services\MidtransPaymentService;
use App\Services\NotificationService;
use App\Support\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoPaymentController extends Controller
{
    public function __construct(private readonly MidtransPaymentService $midtrans)
    {
    }

    public function index(Request $request)
    {
        $query = Video::with('package')->where('is_active', true);

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->where('title', 'like', "%{$escapedSearch}%");
        }

        $videos = $query->latest()->paginate(12)->withQueryString();
        $packages = \App\Models\Package::orderBy('title')->get();

        $videoAccessMap = Auth::check()
            ? VideoOrder::where('user_id', Auth::id())
                ->whereIn('payment_status', ['paid', 'pending'])
                ->latest()
                ->get()
                ->unique('video_id')
                ->mapWithKeys(fn ($order) => [$order->video_id => $order->accessStatus()])
                ->toArray()
            : [];

        return view('videos.index', compact('videos', 'packages', 'videoAccessMap'));
    }

    public function show(Video $video)
    {
        $video->load('package');
        $videoOrder = null;
        $pendingOrder = null;

        if (Auth::check()) {
            $videoOrder = VideoOrder::where('user_id', Auth::id())
                ->where('video_id', $video->id)
                ->where('payment_status', 'paid')
                ->latest()
                ->first();

            if (!$videoOrder) {
                $pendingOrder = VideoOrder::where('user_id', Auth::id())
                    ->where('video_id', $video->id)
                    ->where('payment_status', 'pending')
                    ->latest()
                    ->first();
            }
        }

        return view('videos.show', compact('video', 'videoOrder', 'pendingOrder'));
    }

    public function createOrder(Video $video, Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        $existingPaid = VideoOrder::where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->where('payment_status', 'paid')
            ->where('access_end', '>=', now())
            ->first();

        if ($existingPaid) {
            return redirect()->route('videos.show', $video)->with('error', 'Anda sudah memiliki akses aktif ke video ini.');
        }

        $pendingOrder = VideoOrder::where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->where('payment_status', 'pending')
            ->latest()
            ->first();

        if ($pendingOrder) {
            // Update price if video supports PWYW and amount changed
            if ($video->is_pay_what_you_want && $request->filled('amount')) {
                $amount = max(1, round((float) $request->amount));
                if ((int) $pendingOrder->total_price !== $amount) {
                    $pendingOrder->update(['total_price' => $amount]);
                }
            }
            return redirect()->route('videos.pay', ['video' => $video->id, 'videoOrder' => $pendingOrder->id]);
        }

        // Determine total price
        if ($video->is_pay_what_you_want) {
            $minAmount = $video->minimumPayAmount();
            $amount = $request->input('amount');
            if ($amount === null || $amount === '') {
                return redirect()->route('videos.show', $video)->with('error', 'Silakan masukkan jumlah pembayaran.');
            }
            $totalPrice = max(1, round((float) $amount));
            if ($totalPrice < $minAmount) {
                return redirect()->route('videos.show', $video)->with('error', 'Jumlah pembayaran minimal Rp ' . number_format($minAmount, 0, ',', '.') . '.');
            }
        } else {
            $totalPrice = max(1, round((float) $video->final_price));
        }

        $orderNumber = 'VID-' . date('YmdHis') . '-' . strtoupper(Str::random(8));

        $videoOrder = VideoOrder::create([
            'user_id' => $user->id,
            'video_id' => $video->id,
            'order_number' => $orderNumber,
            'total_price' => $totalPrice,
            'payment_status' => 'pending',
        ]);

        // Notify all admins about new video order
        $admins = \App\Models\User::where('role', 'admin')->pluck('id');
        foreach ($admins as $adminId) {
            NotificationService::create(
                $adminId,
                'video',
                'Pesanan Video Baru',
                $user->name . ' memesan video "' . ($video->title ?? '-') . '" seharga Rp ' . number_format($totalPrice, 0, ',', '.') . '. Menunggu pembayaran.',
                ['video_order_id' => $videoOrder->id, 'order_number' => $orderNumber, 'action_url' => route('admin.video-orders.index')],
                true
            );
        }

        return redirect()->route('videos.pay', ['video' => $video->id, 'videoOrder' => $videoOrder->id]);
    }

    /**
     * Halaman checkout video: buat Snap Token dan render halaman pembayaran.
     */
    public function processPayment(Request $request, Video $video, VideoOrder $videoOrder)
    {
        if ($videoOrder->user_id !== Auth::id()) {
            return redirect()->route('videos.show', $video)->with('error', 'Akses ditolak!');
        }

        if ($videoOrder->video_id !== $video->id) {
            return redirect()->route('videos.show', $videoOrder->video_id)->with('error', 'Pesanan tidak cocok dengan video ini.');
        }

        if ($videoOrder->payment_status === 'paid') {
            return redirect()->route('videos.show', $video)->with('success', 'Anda sudah memiliki akses ke video ini.');
        }

        $user   = Auth::user();
        $amount = (int) $videoOrder->total_price;

        // order_id Midtrans: nomor pesanan + nominal
        $midtransOrderId = $videoOrder->order_number . '-' . $amount;

        // Pakai Snap Token yang sudah ada bila masih valid
        $snapToken = null;
        if ($videoOrder->payment_status === 'pending' && filled($videoOrder->payment_url)) {
            $snapToken = $videoOrder->payment_url;
        }

        if (!$snapToken) {
            if (!$this->midtrans->isConfigured()) {
                return view('videos.payment', [
                    'video' => $video,
                    'videoOrder' => $videoOrder,
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
                itemName: Str::limit($video->title, 50),
                itemId: (string) $video->id,
                finishUrl: url('/video-payment/finish?video_order_id=' . $videoOrder->id),
                unfinishUrl: url('/video-payment/finish?video_order_id=' . $videoOrder->id),
                errorUrl: url('/video-payment/finish?video_order_id=' . $videoOrder->id),
            );

            try {
                $snapToken = $this->midtrans->createSnapToken($params);
            } catch (\Throwable $e) {
                Log::error('Gagal membuat Snap Token Midtrans (video)', [
                    'video_order_id' => $videoOrder->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->back()->with('error', '❌ Gagal menghubungi gateway pembayaran. Silakan coba lagi.');
            }

            $videoOrder->update([
                'transaction_id' => $midtransOrderId,
                'payment_url' => $snapToken,
            ]);
        }

        return view('videos.payment', [
            'video' => $video,
            'videoOrder' => $videoOrder,
            'snapToken' => $snapToken,
            'clientKey' => config('midtrans.client_key'),
            'snapJsUrl' => $this->midtrans->snapJsUrl(),
            'gatewayConfigured' => $this->midtrans->isConfigured(),
            'sandboxMode' => !$this->midtrans->isProduction(),
        ]);
    }

    /**
     * POST pay — diarahkan ke processPayment untuk regenerate token jika perlu.
     */
    public function pay(Request $request, Video $video, VideoOrder $videoOrder)
    {
        if ($videoOrder->user_id !== Auth::id()) {
            return back()->with('error', 'Akses ditolak!');
        }

        return redirect()->route('videos.pay', ['video' => $video->id, 'videoOrder' => $videoOrder->id]);
    }

    /**
     * Halaman return dari Midtrans setelah pembayaran video.
     * Teruskan ke alur /payment/finish melalui mirror order.
     */
    public function paymentFinish(Request $request)
    {
        $videoOrderId = $request->get('video_order_id');
        $videoOrder = ctype_digit((string) $videoOrderId) ? VideoOrder::find((int) $videoOrderId) : null;

        if (!$videoOrder || !Auth::check() || $videoOrder->user_id !== Auth::id()) {
            return redirect()->route('login')->with('error', 'Silakan login untuk melihat status pesanan Anda.');
        }

        $mirrorOrder = $videoOrder->mirrorOrder();

        $query = collect($request->query())
            ->except(['video_order_id'])
            ->all();
        $query['order_ref'] = $mirrorOrder->id;

        return redirect()->route('payment.finish', $query);
    }

    /**
     * Endpoint notification/webhook dari Midtrans untuk pembayaran video.
     */
    public function notification(Request $request)
    {
        try {
            $notif = $this->midtrans->readNotification($request);

            if (!$notif) {
                Log::warning('Notifikasi Midtrans (video) tidak valid atau signature salah');
                return response('FORBIDDEN', 403);
            }

            $orderId     = (string) ($notif->order_id ?? '');
            $paymentType = (string) ($notif->payment_type ?? '');

            $videoOrder = VideoOrder::where('transaction_id', $orderId)->first();

            if (!$videoOrder) {
                $videoOrder = VideoOrder::where(
                    'order_number',
                    MidtransPaymentService::extractOrderNumber($orderId)
                )->first();
            }

            if (!$videoOrder) {
                return response('NOT FOUND', 404);
            }

            $status = MidtransPaymentService::mapStatus($notif);

            if ($status === 'paid') {
                $updateData = ['payment_time' => now()];
                if ($paymentType) {
                    $updateData['payment_type'] = $paymentType;
                }
                $videoOrder->markPaid($updateData);
            } elseif ($status === 'failed') {
                $videoOrder->update(['payment_status' => 'failed']);
            } elseif ($status === 'expired') {
                $videoOrder->update(['payment_status' => 'expired']);
            } else {
                if ($videoOrder->payment_status === 'pending') {
                    $videoOrder->touch();
                }
            }

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('Error memproses notifikasi Midtrans (video)', [
                'error' => $e->getMessage(),
                'order_id' => $request->input('order_id'),
            ]);
            return response('Internal error', 500);
        }
    }
}

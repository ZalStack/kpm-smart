<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransPaymentService
{
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Konfigurasi Midtrans SDK dari config/midtrans.php.
     * Dipanggil sekali saat instansi dibuat — cukup satu kali per request.
     */
    private function configure(): void
    {
        Config::$serverKey    = (string) config('midtrans.server_key', '');
        Config::$clientKey    = (string) config('midtrans.client_key', '');
        Config::$isProduction = (bool)   config('midtrans.is_production', false);
        Config::$isSanitized  = true;   // sanitasi input otomatis
        Config::$is3ds        = true;   // wajib 3DS untuk kartu kredit
    }

    /**
     * Apakah Server Key sudah dikonfigurasi?
     */
    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key'))
            && filled(config('midtrans.client_key'));
    }

    public function isProduction(): bool
    {
        return (bool) config('midtrans.is_production', false);
    }

    /**
     * URL Snap JS CDN yang sesuai dengan mode (sandbox/produksi).
     */
    public function snapJsUrl(): string
    {
        return (string) config('midtrans.snap_url',
            $this->isProduction()
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js'
        );
    }

    /**
     * Buat Snap Token yang dipakai oleh Snap.js di sisi klien untuk
     * membuka popup pembayaran Midtrans.
     *
     * Mengembalikan Snap Token (string).
     * Melempar \Throwable bila gagal — caller menentukan tampilan error.
     */
    public function createSnapToken(array $params): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Kredensial Midtrans (server key / client key) belum dikonfigurasi.');
        }

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('Gagal membuat Snap Token Midtrans', [
                'order_id' => $params['transaction_details']['order_id'] ?? null,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }

        return $snapToken;
    }

    /**
     * Susun parameter standar Snap untuk paket maupun video.
     * Expiry unit: minute — diambil dari config midtrans.expiry_minutes.
     */
    public function buildSnapParams(
        string $orderId,
        int    $grossAmount,
        string $firstName,
        string $email,
        string $phone,
        string $itemName,
        string $itemId,
        string $finishUrl,
        string $unfinishUrl,
        string $errorUrl,
    ): array {
        $expiryMinutes = max(10, (int) config('midtrans.expiry_minutes', 60));

        return [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => Str::limit($firstName, 50),
                'email'      => $email,
                'phone'      => $phone ?: '08123456789',
            ],
            'item_details' => [
                [
                    'id'       => (string) $itemId,
                    'price'    => $grossAmount,
                    'quantity' => 1,
                    'name'     => Str::limit($itemName, 50),
                ],
            ],
            'callbacks' => [
                'finish'   => $finishUrl,
                'unfinish' => $unfinishUrl,
                'error'    => $errorUrl,
            ],
            'expiry' => [
                'unit'     => 'minute',
                'duration' => $expiryMinutes,
            ],
            'credit_card' => [
                'secure' => true,   // wajibkan 3DS
            ],
        ];
    }

    /**
     * Cek status transaksi LANGSUNG ke API Midtrans berdasarkan order_id.
     * Data redirect browser tidak pernah dipercaya — selalu diverifikasi
     * dengan endpoint ini sebelum order ditandai lunas.
     *
     * Mengembalikan objek status atau null jika tidak dapat diverifikasi.
     */
    public function checkStatus(string $orderId): ?object
    {
        if (!$this->isConfigured() || $orderId === '') {
            return null;
        }

        try {
            $status = Transaction::status($orderId);
            return is_object($status) ? $status : null;
        } catch (\Throwable $e) {
            Log::warning('Midtrans Transaction::status gagal', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Baca & verifikasi payload notification Midtrans (server-to-server).
     *
     * Keamanan: signature_key wajib cocok dengan
     * SHA512(order_id + status_code + gross_amount + server_key).
     * Notification tanpa signature valid dikembalikan null.
     *
     * Mendukung JSON body (dari postJson test) maupun php://input normal.
     */
    public function readNotification(Request $request): ?object
    {
        try {
            // Baca dari request Laravel agar kompatibel dengan test (postJson)
            // maupun notifikasi Midtrans asli (JSON via php://input).
            $data = $request->all();

            // Fallback: coba baca dari php://input jika request->all() kosong
            if (empty($data)) {
                $raw = file_get_contents('php://input');
                if ($raw) {
                    $data = json_decode($raw, true) ?? [];
                }
            }

            $orderId     = trim((string) ($data['order_id'] ?? ''));
            $statusCode  = trim((string) ($data['status_code'] ?? ''));
            $grossAmount = trim((string) ($data['gross_amount'] ?? ''));
            $received    = trim((string) ($data['signature_key'] ?? ''));

            if ($orderId === '') {
                return null;
            }

            // Verifikasi signature: SHA512(order_id + status_code + gross_amount + server_key)
            $serverKey = (string) config('midtrans.server_key', '');
            $expected  = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if (!hash_equals($expected, $received)) {
                Log::warning('Notifikasi Midtrans dengan signature_key TIDAK VALID ditolak', [
                    'order_id' => $orderId,
                ]);
                return null;
            }

            // Buat stdClass dari data yang sudah divalidasi
            $notif = new \stdClass();
            foreach ($data as $key => $value) {
                $notif->$key = $value;
            }

            return $notif;
        } catch (\Throwable $e) {
            Log::warning('Gagal memproses notifikasi Midtrans', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Pemetaan transaction_status + fraud_status Midtrans -> status internal.
     *
     * Referensi: https://docs.midtrans.com/reference/get-transaction-status
     */
    public static function mapStatus(?object $notifOrStatus): string
    {
        if (!$notifOrStatus) {
            return 'pending';
        }

        $txStatus    = strtolower((string) ($notifOrStatus->transaction_status ?? ''));
        $fraudStatus = strtolower((string) ($notifOrStatus->fraud_status ?? ''));

        return match (true) {
            // Pembayaran berhasil & tidak dicurigai fraud
            $txStatus === 'capture' && $fraudStatus === 'accept'        => 'paid',
            $txStatus === 'settlement'                                   => 'paid',

            // Fraud — harus diverifikasi manual
            $txStatus === 'capture' && $fraudStatus === 'challenge'     => 'pending',

            // Dibatalkan atau kadaluarsa
            $txStatus === 'cancel'                                       => 'failed',
            $txStatus === 'deny'                                         => 'failed',
            $txStatus === 'expire'                                       => 'expired',

            // Refund / partial refund
            $txStatus === 'refund'                                       => 'refunded',
            $txStatus === 'partial_refund'                               => 'refunded',

            // Pending / authorize
            default                                                      => 'pending',
        };
    }

    /**
     * Rekonstruksi order_number lokal dari order_id Midtrans.
     *
     * Format yang kita kirim: "{order_number}-{amount}" agar setiap
     * percobaan dengan nominal yang sama memiliki ID unik di Midtrans.
     * Segmen terakhir yang berupa digit murni dianggap sebagai amount.
     */
    public static function extractOrderNumber(string $orderId): string
    {
        $parts = explode('-', trim($orderId));

        if (count($parts) < 2) {
            return $orderId;
        }

        // Lepas amount hanya bila berupa digit murni.
        if (ctype_digit((string) end($parts))) {
            array_pop($parts);
        }

        return implode('-', $parts);
    }

    /**
     * Label metode pembayaran Midtrans untuk tampilan admin/user.
     * Midtrans mengembalikan payment_type seperti "bank_transfer", "gopay", dst.
     */
    public static function methodLabel(?string $paymentType): string
    {
        if (!$paymentType) {
            return '-';
        }

        return match (strtolower($paymentType)) {
            'credit_card'   => 'Kartu Kredit/Debit',
            'bank_transfer' => 'Transfer Bank',
            'gopay'         => 'GoPay',
            'shopeepay'     => 'ShopeePay',
            'qris'          => 'QRIS',
            'cimb_clicks'   => 'CIMB Clicks',
            'bca_klikbca'   => 'KlikBCA',
            'bca_klikpay'   => 'BCA KlikPay',
            'cstore'        => 'Gerai Retail',
            'echannel'      => 'Mandiri Bill',
            'permata'       => 'Permata Virtual Account',
            'bca'           => 'BCA Virtual Account',
            'bni'           => 'BNI Virtual Account',
            'bri'           => 'BRI Virtual Account',
            'danamon_online'=> 'Danamon Online',
            'akulaku'       => 'Akulaku',
            'kredivo'       => 'Kredivo',
            default         => ucwords(str_replace('_', ' ', $paymentType)),
        };
    }
}

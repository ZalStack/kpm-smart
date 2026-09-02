<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Kredensial Midtrans deterministik untuk uji signature notification.
        config([
            'midtrans.server_key'    => 'testing-server-key',
            'midtrans.client_key'    => 'testing-client-key',
            'midtrans.is_production' => false,
        ]);
    }

    private function createPendingOrder(): Order
    {
        $user = User::factory()->create(['role' => 'user']);
        $package = Package::create([
            'title'       => 'Paket Webhook',
            'description' => 'desc',
            'price'       => 100000,
        ]);

        return Order::create([
            'user_id'        => $user->id,
            'package_id'     => $package->id,
            'order_number'   => 'ORD-20260822000100-AB12CD34',
            'total_price'    => 100000,
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Buat payload notifikasi Midtrans dengan signature_key SHA512 yang VALID:
     * SHA512(order_id + status_code + gross_amount + server_key)
     */
    private function validNotificationPayload(
        string $orderId,
        int|string $grossAmount,
        string $transactionStatus = 'settlement',
        string $fraudStatus = 'accept',
        string $statusCode = '200',
    ): array {
        $serverKey  = config('midtrans.server_key');
        $grossStr   = (string) $grossAmount; // gunakan nilai yang sama untuk signature dan payload

        $signatureKey = hash('sha512', $orderId . $statusCode . $grossStr . $serverKey);

        return [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossStr,
            'transaction_status' => $transactionStatus,
            'fraud_status'       => $fraudStatus,
            'payment_type'       => 'bank_transfer',
            'transaction_id'     => 'MIDTRANS-' . uniqid(),
            'signature_key'      => $signatureKey,
        ];
    }

    /**
     * Webhook menandai order paket sebagai lunas lewat transaction_id yang tepat.
     */
    public function test_webhook_marks_package_order_paid_by_transaction_id(): void
    {
        $order = $this->createPendingOrder();
        $midtransOrderId = $order->order_number . '-' . (int) $order->total_price;
        $order->update(['transaction_id' => $midtransOrderId]);

        $this->postJson('/payment/notification', $this->validNotificationPayload($midtransOrderId, 100000))
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('bank_transfer', $order->payment_type);
        $this->assertNotNull($order->membership_end, 'Membership harus teraktifasi via webhook');
    }

    /**
     * Fallback order_number: invoice yang dicari lewat segmen awal order_id
     * Midtrans jika transaction_id di DB masih kosong.
     */
    public function test_webhook_finds_order_via_order_number_fallback(): void
    {
        $order = $this->createPendingOrder();
        $this->assertNull($order->transaction_id);

        // order_id Midtrans = order_number + amount (format deterministik)
        $midtransOrderId = $order->order_number . '-100000';

        $this->postJson('/payment/notification', $this->validNotificationPayload($midtransOrderId, 100000))
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
    }

    /**
     * Mirror pesanan video (type='video') TIDAK boleh diubah webhook paket.
     */
    public function test_package_webhook_ignores_video_mirror_orders(): void
    {
        $mirror = Order::create([
            'user_id'        => User::factory()->create()->id,
            'package_id'     => null,
            'video_order_id' => null,
            'type'           => 'video',
            'order_number'   => 'VID-20260822000200-XY98ZZ76',
            'total_price'    => 50000,
            'payment_status' => 'pending',
            'transaction_id' => 'VID-20260822000200-XY98ZZ76-50000',
        ]);

        $this->postJson('/payment/notification', $this->validNotificationPayload($mirror->transaction_id, 50000))
            ->assertStatus(404);

        $mirror->refresh();
        $this->assertEquals('pending', $mirror->payment_status);
    }

    /**
     * Regresi TypeError: activity_logs di-cast ke array oleh Eloquent;
     * penulisan log setelah pembayaran tidak boleh merusak format array.
     */
    public function test_activity_log_written_as_array_after_webhook_payment(): void
    {
        $order = $this->createPendingOrder();

        // Simulasikan user yang sudah punya log sebelumnya (array).
        $user = $order->user;
        $user->activity_logs = [['action' => 'Old Entry', 'timestamp' => now()->toDateTimeString()]];
        $user->save();

        $midtransOrderId = $order->order_number . '-' . (int) $order->total_price;
        $order->update(['transaction_id' => $midtransOrderId]);

        $this->postJson('/payment/notification', $this->validNotificationPayload($midtransOrderId, 100000))
            ->assertStatus(200);

        $user->refresh();
        $logs = $user->activity_logs;

        $this->assertIsArray($logs);
        $this->assertCount(2, $logs);
        $this->assertEquals('Payment Success via Webhook', $logs[1]['action']);

        // Dashboard admin harus render aman meski ada user ber-log.
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->get(route('admin.dashboard'))->assertStatus(200);
    }

    /**
     * Notifikasi dengan signature_key TIDAK valid harus ditolak (403)
     * dan tidak mengubah status pesanan.
     */
    public function test_webhook_rejects_invalid_signature(): void
    {
        $order = $this->createPendingOrder();

        $payload = $this->validNotificationPayload($order->order_number . '-100000', 100000);
        $payload['signature_key'] = strrev($payload['signature_key']); // signature rusak

        $this->postJson('/payment/notification', $payload)->assertStatus(403);

        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
    }

    /**
     * transaction_status "cancel" (gagal) harus menandai pesanan failed.
     */
    public function test_webhook_marks_cancelled_orders_as_failed(): void
    {
        $order = $this->createPendingOrder();
        $midtransOrderId = $order->order_number . '-100000';
        $order->update(['transaction_id' => $midtransOrderId]);

        $this->postJson('/payment/notification', $this->validNotificationPayload(
            orderId: $midtransOrderId,
            grossAmount: 100000,
            transactionStatus: 'cancel',
            fraudStatus: '',
            statusCode: '200',
        ))->assertStatus(200);

        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
    }

    /**
     * transaction_status "expire" harus menandai pesanan expired.
     */
    public function test_webhook_marks_expired_orders(): void
    {
        $order = $this->createPendingOrder();
        $midtransOrderId = $order->order_number . '-100000';
        $order->update(['transaction_id' => $midtransOrderId]);

        $this->postJson('/payment/notification', $this->validNotificationPayload(
            orderId: $midtransOrderId,
            grossAmount: 100000,
            transactionStatus: 'expire',
            fraudStatus: '',
            statusCode: '407',
        ))->assertStatus(200);

        $order->refresh();
        $this->assertEquals('expired', $order->payment_status);
    }

    /**
     * Payload kosong (tanpa order_id) harus ditolak 403, bukan 500.
     * Exception internal tidak boleh membocorkan detail error ke caller publik.
     */
    public function test_webhook_returns_403_for_empty_payload(): void
    {
        $response = $this->postJson('/payment/notification', []);

        $response->assertStatus(403);
        $this->assertStringNotContainsString('Exception', $response->getContent());
    }

    /**
     * capture + fraud_status=accept harus ditandai paid (kartu kredit).
     */
    public function test_webhook_marks_capture_accept_as_paid(): void
    {
        $order = $this->createPendingOrder();
        $midtransOrderId = $order->order_number . '-100000';
        $order->update(['transaction_id' => $midtransOrderId]);

        $this->postJson('/payment/notification', $this->validNotificationPayload(
            orderId: $midtransOrderId,
            grossAmount: 100000,
            transactionStatus: 'capture',
            fraudStatus: 'accept',
            statusCode: '200',
        ))->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
    }

    /**
     * capture + fraud_status=challenge (potensi fraud) harus tetap pending.
     */
    public function test_webhook_keeps_pending_for_fraud_challenge(): void
    {
        $order = $this->createPendingOrder();
        $midtransOrderId = $order->order_number . '-100000';
        $order->update(['transaction_id' => $midtransOrderId]);

        $this->postJson('/payment/notification', $this->validNotificationPayload(
            orderId: $midtransOrderId,
            grossAmount: 100000,
            transactionStatus: 'capture',
            fraudStatus: 'challenge',
            statusCode: '201',
        ))->assertStatus(200);

        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
    }
}

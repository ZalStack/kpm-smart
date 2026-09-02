<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoOrder;
use App\Services\MidtransPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoFeatureSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_verified' => true, 'is_active' => true]);
    }

    private function normalUser(): User
    {
        return User::factory()->create(['role' => 'user', 'is_verified' => true, 'is_active' => true]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'midtrans.server_key'    => 'testing-server-key',
            'midtrans.client_key'    => 'testing-client-key',
            'midtrans.is_production' => false,
        ]);
    }

    public function test_admin_video_pages_render(): void
    {
        $this->actingAs($this->adminUser());

        $this->get(route('admin.videos.index'))->assertStatus(200);
        $this->get(route('admin.videos.create'))->assertStatus(200);
        $this->get(route('admin.video-orders.index'))->assertStatus(200);
        $this->get(route('admin.login-logs.index'))->assertStatus(200);
    }

    public function test_user_video_pages_render(): void
    {
        $this->actingAs($this->normalUser());

        $this->get(route('videos.index'))->assertStatus(200);
        $this->get(route('user.dashboard'))->assertStatus(200);
    }

    public function test_video_show_and_edit_render_with_data(): void
    {
        $admin = $this->adminUser();
        $user  = $this->normalUser();

        $video = Video::create([
            'title'                => 'Smoke Test Video',
            'description'          => 'Test description',
            'video_url'            => 'https://youtube.com/watch?v=abc123',
            'price'                => 50000,
            'discount_type'        => 'percent',
            'discount_value'       => 10,
            'access_duration_days' => 30,
            'is_active'            => true,
        ]);

        // Admin edit page
        $this->actingAs($admin);
        $this->get(route('admin.videos.edit', $video))->assertStatus(200);

        // User show page (paywall state)
        $this->actingAs($user);
        $this->get(route('videos.show', $video))->assertStatus(200);

        // Create order + redirect to payment page
        $response = $this->post(route('videos.order', $video));
        $order = VideoOrder::where('user_id', $user->id)->where('video_id', $video->id)->latest()->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('videos.pay', ['video' => $video->id, 'videoOrder' => $order->id]));

        // Payment page renders (Midtrans: snap token disimpan di payment_url)
        // Simulasikan snap_token sudah ada agar tidak memanggil Midtrans API
        $order->update([
            'transaction_id' => $order->order_number . '-' . (int) $order->total_price,
            'payment_url'    => 'snap-token-smoke-test',
        ]);
        $this->get(route('videos.pay', ['video' => $video->id, 'videoOrder' => $order->id]))->assertStatus(200);

        // Show page again with pending order
        $this->get(route('videos.show', $video))->assertStatus(200);

        // Mark paid & grant access -> watch state
        $order->update(['payment_status' => 'paid', 'access_granted' => true]);
        $order->activateAccess();
        $this->get(route('videos.show', $video))->assertStatus(200);

        $order->delete();
        $video->delete();
    }

    public function test_video_order_creates_mirror_order_row(): void
    {
        $user = $this->normalUser();
        $video = Video::create([
            'title'                => 'Mirror Test Video',
            'description'          => 'x',
            'video_url'            => 'https://youtube.com/watch?v=xyz',
            'price'                => 25000,
            'access_duration_days' => 14,
            'is_active'            => true,
        ]);

        $this->actingAs($user);
        $this->post(route('videos.order', $video));

        $videoOrder = VideoOrder::where('user_id', $user->id)->where('video_id', $video->id)->first();
        $mirror     = Order::where('video_order_id', $videoOrder->id)->first();

        $this->assertNotNull($mirror);
        $this->assertEquals('video', $mirror->type);
        $this->assertEquals($videoOrder->order_number, $mirror->order_number);
        $this->assertEquals('pending', $mirror->payment_status);
        $this->assertTrue($mirror->isVideoOrder());
        $this->assertEquals('Mirror Test Video', $mirror->item_title);
    }

    /**
     * Alur finish setelah pembayaran: status diverifikasi ke Midtrans.
     * Mock MidtransPaymentService::checkStatus agar tidak menghubungi API.
     */
    public function test_package_style_finish_flow_activates_video_and_renders_finish_page(): void
    {
        $user = $this->normalUser();
        $video = Video::create([
            'title'                => 'Finish Flow Video',
            'description'          => 'x',
            'video_url'            => 'https://youtube.com/watch?v=fin',
            'price'                => 30000,
            'access_duration_days' => 30,
            'is_active'            => true,
        ]);

        $this->actingAs($user);
        $this->post(route('videos.order', $video));

        $videoOrder = VideoOrder::where('user_id', $user->id)->where('video_id', $video->id)->first();
        $mirror     = $videoOrder->mirrorOrder();

        $midtransOrderId = $videoOrder->order_number . '-' . (int) $videoOrder->total_price;
        $mirror->update(['transaction_id' => $midtransOrderId]);

        // Mock MidtransPaymentService — simulasikan status "settlement" (lunas)
        $this->instance(MidtransPaymentService::class, new class extends MidtransPaymentService {
            public function __construct() {}
            public function checkStatus(string $orderId): ?object
            {
                return (object) [
                    'transaction_status' => 'settlement',
                    'fraud_status'       => 'accept',
                    'payment_type'       => 'gopay',
                    'transaction_id'     => 'MOCK-MIDTRANS-TXN',
                ];
            }
        });

        $response = $this->get(route('payment.finish', ['order_ref' => $mirror->id]));

        $response->assertStatus(200);
        $response->assertSee('Finish Flow Video');
        $response->assertSee('Video Pembahasan');

        $mirror->refresh();
        $videoOrder->refresh();

        $this->assertEquals('paid', $mirror->payment_status);
        $this->assertEquals('paid', $videoOrder->payment_status);
        $this->assertEquals('gopay', $videoOrder->payment_type);
        $this->assertNotNull($videoOrder->access_start);
        $this->assertNotNull($videoOrder->access_end);
    }

    /**
     * Keamanan: URL return Midtrans tidak dipercaya langsung.
     * Jika checkStatus gagal/null, order TIDAK boleh berubah jadi paid.
     */
    public function test_forged_finish_request_cannot_mark_order_paid(): void
    {
        $user = $this->normalUser();
        $video = Video::create([
            'title'                => 'Forged Request Video',
            'description'          => 'x',
            'video_url'            => 'https://youtube.com/watch?v=forged',
            'price'                => 10000,
            'access_duration_days' => 30,
            'is_active'            => true,
        ]);

        $this->actingAs($user);
        $this->post(route('videos.order', $video));

        $videoOrder = VideoOrder::where('user_id', $user->id)->where('video_id', $video->id)->first();
        $mirror     = $videoOrder->mirrorOrder();

        // Mock: API Midtrans tidak bisa dihubungi / transaksi tidak ditemukan
        $this->instance(MidtransPaymentService::class, new class extends MidtransPaymentService {
            public function __construct() {}
            public function checkStatus(string $orderId): ?object { return null; }
        });

        $this->get(route('payment.finish', ['order_ref' => $mirror->id]))
            ->assertRedirect(route('orders.index'));

        $mirror->refresh();
        $this->assertEquals('pending', $mirror->payment_status);

        $videoOrder->refresh();
        $this->assertEquals('pending', $videoOrder->payment_status);
    }

    public function test_non_owner_cannot_access_payment_finish_page(): void
    {
        $owner   = $this->normalUser();
        $attacker = $this->normalUser();

        $video = Video::create([
            'title'                => 'Ownership Test Video',
            'description'          => 'x',
            'video_url'            => 'https://youtube.com/watch?v=own',
            'price'                => 10000,
            'access_duration_days' => 30,
            'is_active'            => true,
        ]);

        $this->actingAs($owner);
        $this->post(route('videos.order', $video));

        $videoOrder = VideoOrder::where('user_id', $owner->id)->where('video_id', $video->id)->first();
        $mirror     = $videoOrder->mirrorOrder();

        $this->actingAs($attacker);
        $this->get(route('payment.finish', ['order_ref' => $mirror->id]))->assertForbidden();
    }

    public function test_video_orders_appear_in_user_orders_admin_transactions_and_reports(): void
    {
        $admin = $this->adminUser();
        $user  = $this->normalUser();
        $video = Video::create([
            'title'                => 'Report Visibility Video',
            'description'          => 'x',
            'video_url'            => 'https://youtube.com/watch?v=rep',
            'price'                => 40000,
            'access_duration_days' => 30,
            'is_active'            => true,
        ]);

        $this->actingAs($user);
        $this->post(route('videos.order', $video));

        $videoOrder = VideoOrder::where('user_id', $user->id)->where('video_id', $video->id)->first();
        $mirror     = $videoOrder->mirrorOrder();
        $mirror->update(['payment_status' => 'paid']);
        $videoOrder->refresh();

        // Sebelum admin grant akses -> tampil "Menunggu Aktivasi Admin"
        $this->get(route('orders.index'))->assertSee('Menunggu Aktivasi Admin');

        // Setelah admin grant akses -> tombol Tonton muncul
        $videoOrder->update(['access_granted' => true]);
        $videoOrder->activateAccess();

        $ordersResponse = $this->get(route('orders.index'));
        $ordersResponse->assertStatus(200);
        $ordersResponse->assertSee('Report Visibility Video');
        $ordersResponse->assertSee('Tonton');

        // Admin: transaksi & laporan memuat data video
        $this->actingAs($admin);
        $txResponse = $this->get(route('admin.transactions.index'));
        $txResponse->assertStatus(200);
        $txResponse->assertSee('Report Visibility Video');

        $reportResponse = $this->get(route('admin.reports.index'));
        $reportResponse->assertStatus(200);
        $reportResponse->assertSee('Report Visibility Video');

        // Detail transaksi & order admin bisa dibuka untuk mirror video
        $this->get(route('admin.transactions.show', $mirror->id))->assertStatus(200);
        $this->get(route('admin.orders.show', $mirror->id))->assertStatus(200);
    }
}

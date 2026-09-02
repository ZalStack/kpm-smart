<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\VideoOrder;

class VideoOrderObserver
{
    /**
     * Setiap pembelian video otomatis tercatat di tabel orders sehingga
     * muncul di halaman Pesanan user, Transaksi admin, dan Laporan.
     */
    public function created(VideoOrder $videoOrder): void
    {
        $videoOrder->mirrorOrder();
    }

    /**
     * Sinkronkan status pembayaran video ke baris mirror di tabel orders.
     */
    public function updated(VideoOrder $videoOrder): void
    {
        $payload = [];

        foreach (['payment_status', 'payment_type', 'payment_time', 'transaction_id'] as $field) {
            if ($videoOrder->isDirty($field)) {
                $payload[$field] = $videoOrder->{$field};
            }
        }

        if (empty($payload)) {
            return;
        }

        $mirror = Order::where('video_order_id', $videoOrder->id)->first();

        if (!$mirror) {
            return;
        }

        $changed = false;
        foreach ($payload as $field => $value) {
            if ($mirror->{$field} != $value) {
                $changed = true;
                break;
            }
        }

        if ($changed) {
            $mirror->update($payload);
        }
    }
}

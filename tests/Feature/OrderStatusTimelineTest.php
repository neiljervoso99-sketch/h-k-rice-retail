<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_order_states_display_the_requested_customer_stages(): void
    {
        $processing = new Order(['status' => 'accepted']);
        $shipped = new Order(['status' => 'accepted', 'shipped_at' => now()]);
        $delivered = new Order(['status' => 'completed']);
        $cancelled = new Order(['status' => 'cancelled']);

        $this->assertSame('Processing', $processing->display_status);
        $this->assertSame('Shipped', $shipped->display_status);
        $this->assertSame('Delivered', $delivered->display_status);
        $this->assertSame('Cancelled', $cancelled->display_status);
    }
}
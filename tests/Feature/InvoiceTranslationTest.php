<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Clocking;
use Modules\Invoice\Models\InvoiceCard;

class InvoiceTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_card_page_shows_translated_strings_in_english(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $store = Store::factory()->create();
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);

        $card = InvoiceCard::factory()->create(['clocking_id' => $clocking->id, 'store_id' => $store->id, 'user_id' => $user->id]);

        $response = $this->get(route('invoice.cards.show', $card));

        $response->assertStatus(200);
        $response->assertSee(__('invoice.select_task'));
        $response->assertSee(__('invoice.add_selected'));
    }

    public function test_invoice_card_page_shows_translated_strings_in_arabic(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        app()->setLocale('ar');

        $store = Store::factory()->create();
        $clocking = Clocking::factory()->create(['user_id' => $user->id]);

        $card = InvoiceCard::factory()->create(['clocking_id' => $clocking->id, 'store_id' => $store->id, 'user_id' => $user->id]);

        $response = $this->get(route('invoice.cards.show', $card));

        $response->assertStatus(200);
        $response->assertSee(trans('invoice.select_task'));
        $response->assertSee(trans('invoice.add_selected'));
    }
}

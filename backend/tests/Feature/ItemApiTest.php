<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_endpoint_requires_token(): void
    {
        $this->getJson('/api/items')->assertStatus(401);
    }

    public function test_login_returns_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'salah',
        ])->assertStatus(401);
    }

    public function test_can_create_item_returns_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/items', [
            'name' => 'Laptop',
            'sku' => 'SKU-001',
            'quantity' => 5,
            'price' => 7500000,
        ])->assertStatus(201)->assertJsonPath('data.sku', 'SKU-001');

        $this->assertDatabaseHas('items', ['sku' => 'SKU-001']);
    }

    public function test_create_item_validation_fails_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/items', [])->assertStatus(422);
    }

    public function test_can_update_and_delete_item(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $item = Item::factory()->create();

        $this->putJson("/api/items/{$item->id}", ['name' => 'Baru'])
            ->assertOk()->assertJsonPath('data.name', 'Baru');

        $this->deleteJson("/api/items/{$item->id}")->assertOk();
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }
}
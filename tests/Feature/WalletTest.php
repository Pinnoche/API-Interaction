<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WalletTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_wallet_balance()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100]);
    
        $response = $this->actingAs($user, 'api')->getJson('/api/wallet/balance');
        $response->assertStatus(200)
                 ->assertJson(['balance' => 100]);
    }
    
    public function test_wallet_transfer()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
    
        $wallet = Wallet::factory()->create(['user_id' => $sender->id, 'balance' => 100]);
    
        $response = $this->actingAs($sender, 'api')->postJson('/api/wallet/transfer', [
            'recipient_id' => $recipient->id,
            'amount' => 50,
        ]);
    
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Transfer successful']);
    }
    
}

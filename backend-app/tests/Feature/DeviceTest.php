<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    use RefreshDatabase;

    public function authenticate(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_user_can_create_device()
    {

        $user = $this->authenticate();


        $response = $this->postJson('api/devices', [
            'name' => 'Xiaomi Redmi Note 8',
            'location' => 'Escritório Central',
            'purchase_date' => '2025-12-12'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure(['id']);

        $this->assertDatabaseHas('devices', [
            'name' => 'Xiaomi Redmi Note 8',
            'user_id' => $user->id,
            'deleted_at' => null,
        ]);

    }

    public function test_device_creation_fails_with_invalid_data()
    {
        $this->authenticate();

        $response = $this->postJson('/api/devices', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'location',
                    'purchase_date',
                ]);
    }

    public function test_user_sees_only_own_devices()
    {
        $user = $this->authenticate();
        $otherUser = User::factory()->create();
        $pdo = DB::connection()->getPdo();

        // Device do usuário autenticado
        $this->postJson('/api/devices', [
            'name' => 'Meu Device',
            'location' => 'Casa',
            'purchase_date' => '2024-01-01',
        ]);

        // Device de outro usuário (inserido direto no banco)
        $stmt = $pdo->prepare("
            INSERT INTO devices (name, location, purchase_date, in_use, user_id, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            'Outro Device',
            'Escritorio',
            '2025-06-04',
            0,
            $otherUser->id,
        ]);

        $response = $this->getJson('/api/devices');

        $response->assertOk()
                ->assertJsonCount(1)
                ->assertJsonFragment([
                    'name' => 'Meu Device',
                ]);
    }


    public function test_user_can_toggle_device_usage()
    {
        $user = $this->authenticate();

        $responsePost = $this->postJson('api/devices', [
            'name' => 'Xiaomi Redmi Note 8',
            'location' => 'Escritório Central',
            'purchase_date' => '2025-12-12'
        ]);

        $deviceId = $responsePost->json('id');

        $this->patchJson("/api/devices/{$deviceId}/use")
            ->assertNoContent();

        $this->assertDatabaseHas('devices', [
            'id' => $deviceId,
            'user_id' => $user->id,
            'in_use' => 1,
        ]);
    }







}

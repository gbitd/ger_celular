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
            'name' => 'Meu Dispositivo',
            'location' => 'Casa',
            'purchase_date' => '2024-01-01',
        ]);

        // Device de outro usuário (inserido direto no banco)
        $stmt = $pdo->prepare("
            INSERT INTO devices (name, location, purchase_date, in_use, user_id, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            'Outro Dispositivo',
            'Escritorio',
            '2025-06-04',
            0,
            $otherUser->id,
        ]);

        $response = $this->getJson('/api/devices?page=1');

        $response->assertOk();

        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('Meu Dispositivo', $data[0]['name']);
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
            ->assertOk();

        $this->assertDatabaseHas('devices', [
            'id' => $deviceId,
            'user_id' => $user->id,
            'in_use' => 1,
        ]);
    }

    public function test_user_can_update_device()
    {
        $user = $this->authenticate();

        $responsePost = $this->postJson('api/devices', [
            'name' => 'Xiaomi Redmi Note 9',
            'location' => 'Loja 2',
            'purchase_date' => '2025-10-12'
        ]);

        $deviceId = $responsePost->json('id');

        $this->putJson("/api/devices/{$deviceId}", [
            'name' => 'Iphone 14 Pro Max',
            'location' => 'Loja 3',
            'purchase_date' => '2023-12-12'
        ])->assertNoContent();

        $this->assertDatabaseHas('devices', [
            'id' => $deviceId,
            'user_id' => $user->id,
            'name' => 'Iphone 14 Pro Max',
            'location' => 'Loja 3',
            'purchase_date' => '2023-12-12',
            'in_use' => 0,
        ]);

    }

    public function test_user_can_soft_delete_device()
    {
        $user = $this->authenticate();

        $responsePost = $this->postJson('api/devices', [
            'name' => 'Xiaomi Redmi Note 9',
            'location' => 'Loja 2',
            'purchase_date' => '2025-10-12'
        ]);

        $deviceId = $responsePost->json('id');

        $this->deleteJson("/api/devices/{$deviceId}")
            ->assertNoContent();

        $this->assertDatabaseHas('devices', [
            'id' => $deviceId,
        ]);

        $this->assertDatabaseMissing('devices', [
            'deleted_at' => null
        ]);

    }

    public function test_device_update_fails_with_invalid_data()
    {
        $user = $this->authenticate();

        $responsePost = $this->postJson('api/devices', [
            'name' => 'Xiaomi Redmi Note 9',
            'location' => 'Loja 2',
            'purchase_date' => '2025-10-12'
        ]);

        $deviceId = $responsePost->json('id');

        $responsePut = $this->putJson("/api/devices/{$deviceId}", [
            'name' => 'Iphone 14 Pro Max',
            'location' => 'Loja 3',
            'purchase_date' => date('Y-m-d', strtotime('+12 days')) // Data futura
        ]);

        $responsePut
            ->assertStatus(422)
            ->assertJsonValidationErrors(['purchase_date']);

    }

    public function test_device_update_fails_with_future_date()
    {
        $user = $this->authenticate();

        $responsePost = $this->postJson('api/devices', [
            'name' => 'Xiaomi Redmi Note 9',
            'location' => 'Loja 2',
            'purchase_date' => '2025-10-12'
        ]);

        $deviceId = $responsePost->json('id');

        $responsePut = $this->putJson("/api/devices/{$deviceId}", []);

        $responsePut->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'location',
                    'purchase_date',
                ]);
    }

    public function test_device_delete_fails_when_device_not_found()
    {
        $user = $this->authenticate();

        $responseDelete = $this->deleteJson("/api/devices/40");

        $responseDelete->assertStatus(404)
                    ->assertJson([
                        "message" => "Dispositivo não encontrado"
                    ]);
    }

    public function test_device_update_fails_when_device_not_found()
    {
        $this->authenticate();

        $response = $this->putJson('/api/devices/999', [
            'name' => 'Teste',
            'location' => 'Local',
            'purchase_date' => '2026-01-01',
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Dispositivo não encontrado',
            ]);
    }

    public function test_device_toggle_use_fails_when_device_not_found()
    {
        $user = $this->authenticate();

        $responsePatch = $this->patchJson("/api/devices/40/use");

        $responsePatch->assertStatus(404)
                    ->assertJson([
                        "message" => "Dispositivo não encontrado"
                    ]);
    }



}

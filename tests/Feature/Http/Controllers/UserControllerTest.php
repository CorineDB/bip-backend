<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson(route('users.index'));

        $response->assertOk()
                 ->assertJsonStructure([ '*' => ['id'] ]);
    }

    public function test_can_show_single_users(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('users.show', $user->id));

        $response->assertOk()
                 ->assertJsonFragment(['id' => $user->id]);
    }

    public function test_can_create_users(): void
    {
        $payload = [
            // 'field' => $this->faker->word,
        ];

        $response = $this->postJson(route('users.store'), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('users', $payload);
    }

    public function test_can_update_users(): void
    {
        $user = User::factory()->create();

        $payload = [
            // 'field' => 'UpdatedValue',
        ];

        $response = $this->putJson(route('users.update', $user->id), $payload);

        $response->assertOk();
        $this->assertDatabaseHas('users', $payload);
    }

    public function test_can_delete_users(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson(route('users.destroy', $user->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
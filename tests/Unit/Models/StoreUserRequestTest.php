<?php

namespace Tests\Unit\Models;

use App\Models\StoreUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreUserRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_created(): void
    {
        $model = StoreUserRequest::factory()->make();

        $this->assertInstanceOf(StoreUserRequest::class, $model);
    }
}
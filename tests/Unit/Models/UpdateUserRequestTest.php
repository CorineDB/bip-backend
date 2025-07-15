<?php

namespace Tests\Unit\Models;

use App\Models\UpdateUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_created(): void
    {
        $model = UpdateUserRequest::factory()->make();

        $this->assertInstanceOf(UpdateUserRequest::class, $model);
    }
}
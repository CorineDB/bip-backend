<?php

namespace Tests\Unit\Models;

use App\Models\IdeeProjet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeeProjetTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_created(): void
    {
        $model = IdeeProjet::factory()->make();

        $this->assertInstanceOf(IdeeProjet::class, $model);
    }
}
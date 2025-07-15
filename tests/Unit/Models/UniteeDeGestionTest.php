<?php

namespace Tests\Unit\Models;

use App\Models\UniteeDeGestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniteeDeGestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_created(): void
    {
        $model = UniteeDeGestion::factory()->make();

        $this->assertInstanceOf(UniteeDeGestion::class, $model);
    }
}
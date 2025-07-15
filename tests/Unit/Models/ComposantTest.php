<?php

namespace Tests\Unit\Models;

use App\Models\Composant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComposantTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_can_be_created(): void
    {
        $model = Composant::factory()->make();

        $this->assertInstanceOf(Composant::class, $model);
    }
}
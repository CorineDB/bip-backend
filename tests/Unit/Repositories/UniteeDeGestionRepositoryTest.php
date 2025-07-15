<?php

namespace Tests\Unit\Repositories;

use App\Models\UniteeDeGestion;
use App\Repositories\UniteeDeGestionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniteeDeGestionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UniteeDeGestionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UniteeDeGestionRepository(new UniteeDeGestion);
    }

    public function test_it_can_create_a_{{ model_variable }}(): void
    {
        $data = [
            // Fill with attributes
        ];

        $created = $this->repository->create($data);

        $this->assertDatabaseHas('{{ table }}', $data);
        $this->assertNotNull($created);
    }

    public function test_it_can_create_a_model(): void
    {
        $repository = new UniteeDeGestionRepository(new UniteeDeGestion);
        $data = UniteeDeGestion::factory()->make()->toArray();
        $model = $repository->create($data);

        $this->assertDatabaseHas($model->getTable(), [
            'id' => $model->id,
        ]);
    }
}
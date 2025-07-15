<?php

namespace Tests\Unit\Repositories;

use App\Models\StoreUserRequest;
use App\Repositories\StoreUserRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreUserRequestRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected StoreUserRequestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StoreUserRequestRepository(new StoreUserRequest);
    }

    public function test_it_can_create_a_storeuserrequest(): void
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
        $repository = new StoreUserRequestRepository(new StoreUserRequest);
        $data = StoreUserRequest::factory()->make()->toArray();
        $model = $repository->create($data);

        $this->assertDatabaseHas($model->getTable(), [
            'id' => $model->id,
        ]);
    }
}
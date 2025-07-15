<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Mockery;
use App\Services\UserServiceService;
use App\Repositories\Contracts\App\Repositories\Contracts\UserServiceRepositoryInterface;

class UserServiceServiceTest extends TestCase
{
    protected $repositoryMock;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = Mockery::mock(App\Repositories\Contracts\UserServiceRepositoryInterface::class);
        $this->service = new UserServiceService($this->repositoryMock);
    }

    public function test_all_returns_successful_json_response()
    {
        $this->repositoryMock->shouldReceive('all')->once()->andReturn(collect(['item1', 'item2']));

        $response = $this->service->all();

        $this->assertTrue($response->getData()->success);
        $this->assertCount(2, $response->getData()->data);
    }

    // Tu peux ajouter d'autres tests ici pour find, create, update, delete
}
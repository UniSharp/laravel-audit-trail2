<?php

namespace Tests;

use Mockery as m;
use CreateAuditsTable;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Config\Repository as ConfigRepository;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $migrations = [
        CreateAuditsTable::class
    ];

    public function setUp()
    {
        $this->setMocks();

        $this->migrate();
    }

    public function tearDown()
    {
        $this->migrateRollback();

        m::close();
    }

    protected function setMocks()
    {
        $app = m::mock(Container::class);
        $app->shouldReceive('instance');
        $app->shouldReceive('offsetGet')->with('db')->andReturn(
            m::mock('db')->shouldReceive('connection')->andReturn(
                m::mock('connection')->shouldReceive('getSchemaBuilder')->andReturn('schema')->getMock()
            )->getMock()
        );
        $app->shouldReceive('offsetGet');

        Schema::setFacadeApplication($app);
        Schema::swap(Manager::schema());
    }

    protected function migrate()
    {
        foreach ($this->migrations as $migration) {
            (new $migration)->up();
        }
    }

    protected function migrateRollback()
    {
        foreach (array_reverse($this->migrations) as $migration) {
            (new $migration)->down();
        }
    }
}

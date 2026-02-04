<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase as BaseRefreshDatabase;

trait RefreshDatabase
{
    use BaseRefreshDatabase;

    protected function migrateDatabases()
    {
        $migrator = $this->app->make('migrator');
        $connection = $this->app['config']->get('database.default');
        $migrator->setConnection($connection);

        $repository = $this->app->make('migration.repository');
        $repository->setSource($connection);

        if (! $migrator->repositoryExists()) {
            $repository->createRepository();
        }

        $migrator->run(database_path('migrations'));
    }
}

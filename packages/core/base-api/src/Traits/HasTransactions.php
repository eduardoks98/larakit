<?php

namespace Eduardoks98\BaseApi\Traits;

use Illuminate\Support\Facades\DB;

trait HasTransactions
{
    /**
     * Begin a database transaction.
     *
     * @param string $connection
     * @return void
     */
    protected function beginTransaction(string $connection = 'mysql'): void
    {
        DB::connection($connection)->beginTransaction();
    }

    /**
     * Commit the active database transaction.
     *
     * @param string $connection
     * @return void
     */
    protected function commit(string $connection = 'mysql'): void
    {
        DB::connection($connection)->commit();
    }

    /**
     * Rollback the active database transaction.
     *
     * @param string $connection
     * @return void
     */
    protected function rollback(string $connection = 'mysql'): void
    {
        DB::connection($connection)->rollBack();
    }
}

<?php

namespace Eduardoks98\Permissions\Commands;

use Illuminate\Console\Command;
use Eduardoks98\Permissions\Services\PermissionService;

class SyncPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync
                            {--seed : Also seed default profiles}
                            {--fresh : Delete all permissions before syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from the configured enum to the database';

    /**
     * Execute the console command.
     */
    public function handle(PermissionService $service): int
    {
        $this->info('Starting permissions sync...');

        // Check if enum is configured
        if (!$service->hasPermissionEnum()) {
            $this->error('No permission enum configured.');
            $this->line('Set PERMISSIONS_ENUM in your .env or configure it in config/permissions.php');
            $this->line('Example: PERMISSIONS_ENUM=App\\Enums\\PermissionType');

            return self::FAILURE;
        }

        $enumClass = $service->getPermissionEnumClass();
        $this->line("Using enum: {$enumClass}");

        // Fresh sync - delete all first
        if ($this->option('fresh')) {
            if ($this->confirm('This will delete all existing permissions. Are you sure?')) {
                \Eduardoks98\Permissions\Models\Permission::query()->delete();
                $this->warn('All permissions deleted.');
            } else {
                $this->info('Fresh sync cancelled.');
            }
        }

        // Sync permissions
        $result = $service->syncPermissionsFromEnum();

        $this->newLine();
        $this->info('Permissions sync completed:');
        $this->line("  Created: {$result['created']}");
        $this->line("  Updated: {$result['updated']}");

        if (!empty($result['errors'])) {
            $this->newLine();
            $this->warn('Errors encountered:');
            foreach ($result['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        // Seed default profiles if requested
        if ($this->option('seed')) {
            $this->newLine();
            $this->info('Seeding default profiles...');

            $created = $service->seedDefaultProfiles();

            if (!empty($created)) {
                $this->line('  Created profiles: ' . implode(', ', $created));
            } else {
                $this->line('  No new profiles created (already exist).');
            }
        }

        $this->newLine();
        $this->info('Done!');

        return self::SUCCESS;
    }
}

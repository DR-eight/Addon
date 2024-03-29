<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Console\Processes\Composer;
use Statamic\Console\RunsInPlease;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class SwitchToDatabase extends Command
{
    use RunsInPlease;

    protected $name = 'sc:switch-to-database';

    protected $description = 'Switch your site to using a database for orders & customers.';

    protected $stubsPath;

    public function __construct()
    {
        parent::__construct();

        $this->stubsPath = __DIR__.'/stubs';
    }

    public function handle()
    {
        $this->line('Switching to a database...');

        if (app()->environment('production')) {
            return $this->error('You should not run this command in production. Please switch locally first, then deploy the changes.');
        }

        if (! Composer::create()->isInstalled('statamic-rad-pack/runway')) {
            return $this->error('You need to install Runway before running this command. Run `composer require statamic-rad-pack/runway` first.');
        }

        if (! Composer::create()->isInstalled('doctrine/dbal')) {
            return $this->error('You need to install Doctrine DBAL before running this command. Run `composer require doctrine/dbal` first.');
        }

        $this
            ->copyMigrationStubs()
            ->copyBlueprintStubs()
            ->publishRunwayConfig()
            ->switchRepositories();

        $this->line('');
        $this->info('Next steps:');
        $this->line('- Run `php artisan migrate`');
        $this->line('- Test your site');
        $this->line('- Run the migrator command to migrate any existing customers & orders');
    }

    protected function copyMigrationStubs(): self
    {
        $this->info('Copying migration stubs...');

        if (count(File::glob(database_path('migrations').'/*_create_customers_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_customers_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_customers_table.php'));
        }

        if (count(File::glob(database_path('migrations').'/*_create_orders_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_orders_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_orders_table.php'));
        }

        if (count(File::glob(database_path('migrations').'/*_create_status_log_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_status_log_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_status_log_table.php'));
        }

        return $this;
    }

    protected function copyBlueprintStubs(): self
    {
        $this->info('Copying blueprint stubs...');

        File::ensureDirectoryExists(resource_path('blueprints/vendor/runway'));

        if (! File::exists(resource_path('blueprints/vendor/runway/customer.yaml'))) {
            File::copy($this->stubsPath.'/runway_customer_blueprint.yaml', resource_path('blueprints/vendor/runway/customer.yaml'));
        }

        if (! File::exists(resource_path('blueprints/vendor/runway/order.yaml'))) {
            File::copy($this->stubsPath.'/runway_order_blueprint.yaml', resource_path('blueprints/vendor/runway/order.yaml'));
        }

        return $this;
    }

    protected function publishRunwayConfig(): self
    {
        $this->info('Publishing Runway config file...');

        if (! File::exists(config_path('runway.php'))) {
            File::copy($this->stubsPath.'/runway_config.php', config_path('runway.php'));
        }

        return $this;
    }

    protected function switchRepositories(): self
    {
        $this->info('Switching content repositories...');

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            return $this;
        }

        ConfigWriter::edit('simple-commerce')
            ->replace('content.orders', [
                'repository' => \DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository::class,
                'model' => \DoubleThreeDigital\SimpleCommerce\Orders\OrderModel::class,
            ])
            ->replace('content.customers', [
                'repository' => \DoubleThreeDigital\SimpleCommerce\Customers\EloquentCustomerRepository::class,
                'model' => \DoubleThreeDigital\SimpleCommerce\Customers\CustomerModel::class,
            ])
            ->save();

        return $this;
    }
}

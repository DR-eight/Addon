<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v6_0;

use Illuminate\Support\Facades\File;
use Statamic\UpdateScripts\UpdateScript;

class PublishMigrations extends UpdateScript
{
    protected $stubsPath;

    public function __construct()
    {
        $this->stubsPath = __DIR__.'/../../Console/Commands/stubs';
    }

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            if (count(File::glob(database_path('migrations').'/*_create_status_log_table.php')) < 1) {
                File::copy($this->stubsPath.'/create_status_log_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_status_log_table.php'));
            }
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}

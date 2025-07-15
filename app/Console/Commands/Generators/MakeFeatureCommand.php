<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;

class MakeFeatureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:feature {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a full Laravel clean architecture feature';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        $this->callSilent('make:model', ['name' => $name]);
        $this->callSilent('make:migration', ['name' => $name]);
        $this->callSilent('make:request', ['name' => $name]);
        $this->callSilent('make:dto', ['name' => $name]);
        $this->callSilent('make:action', ['name' => $name]);
        $this->callSilent('make:controller', ['name' => $name]);
        $this->callSilent('make:test', ['name' => $name]);
        $this->callSilent('make:route', ['name' => $name]);

        $this->info("✅ Feature '{$name}' generated successfully.");
    }
}

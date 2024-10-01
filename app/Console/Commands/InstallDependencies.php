<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallDependencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     parent::__construct();
    // }
    protected $signature = 'app:install-dependencies';

    // Command description
    protected $description = 'Install composer dependencies';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
     // Execute composer install command
     exec('composer install --no-dev --optimize-autoloader', $output, $returnVar);

     if ($returnVar === 0) {
         $this->info("Dependencies installed successfully.");
     } else {
         $this->error("There was an error installing dependencies.");
     }

     return $returnVar;
    }
}

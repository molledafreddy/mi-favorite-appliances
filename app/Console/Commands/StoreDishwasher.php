<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StoreDishwasher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dishwasher:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store all dishwasher in our data base';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = $this-> StoreDishwasher($this->ScrapingDishwasher()); 
        Log::debug("impresion del scraping StoreDishwasher");
        Log::debug($data);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\Store;
use Goutte\Client;
use Log;

class StoreAppliances extends Command
{
    use  Store;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appliances:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store all aplliances in our data base';

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
        $data = $this-> StoreAppliances($this->ScrapingAppliances()); 
        Log::debug("impresion del scraping");
        Log::debug($data);
    }
}

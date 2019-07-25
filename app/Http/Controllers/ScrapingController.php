<?php

namespace App\Http\Controllers;

use Illuminate \Http\Request;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use claviska\SimpleImage;
use Exception;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Image;
use App\Traits\Store;

class ScrapingController extends Controller
{
    use  Store;

    public function appliances(Client $client) {
        
        $data = $this-> StoreAppliances($this->ScrapingAppliances());
       dd($data);
        
    }

    
}

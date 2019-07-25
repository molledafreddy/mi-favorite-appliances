<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Goutte\Client;
use DB;
use Symfony\Component\DomCrawler\Crawler;
use App\Appliance;
use App\Dishwasher;


trait Store
{
    /**
     * [StoreAppliances description: metodo que permite realizar un scraping de los electrodomesticos para luego ser insertados en la base de datos]
     */
    public function ScrapingAppliances() {
        $client = new Client();
        $data = [];
        $url = 'https://www.appliancesdelivered.ie/search/small-appliances?sort=price_desc&page=1';
        $class = 'div.products-count';
        $count = $this->productCounts($client, $url, $class);
        
        for ($i=0; $i < $count ; $i++) { 
            $crawler = $client->request('GET', 'https://www.appliancesdelivered.ie/search/small-appliances?sort=price_desc&page='. $i);
            $data [] = $this->extractAppliances($crawler);
        }
        return $data;
    }

    private function productCounts(Client $client, $url, $class) {
        $crawler = $client->request('GET', $url);
        
        $strig = $crawler->filter($class)->text();
        $stringClean = preg_replace("/[^0-9]/", "", $strig);

        return $result = ceil($stringClean/20);
    }

    private function extractAppliances(Crawler $crawler) {
        
        $inlineAppliancesClass ='.search-results-product';
        
        $appliances = $crawler->filter($inlineAppliancesClass)->each(function (Crawler $appliancesNode) {
            $dataInsert = [];
            $url = $appliancesNode->children()->filter('div a')->attr('href');
            $portions = explode("/", $url);
            $id = end($portions);
            
            $divs = $appliancesNode->children()->filter('div');

            $urlImage = $divs->filter('div a img')->attr('data-src');
            
            $sectionDescription = $divs->eq(2);
            //se optiene la url del logo del producto
            $urlLogo = $sectionDescription->filter('div div a picture img')->attr('data-src');
            
            $urlWarranty = $sectionDescription->filter('div div.sales-container picture img');
            $statusWarranty = 0;
            if ($urlWarranty->count() > 0) {$statusWarranty = 1;}
            
            $title = $sectionDescription->filter('h4')->first()->text();
            $price = $divs->filter('.section-title')->text();
            
            $pricePrevius = $divs->filter('h5.price-previous');
            if ($pricePrevius->count() > 0) {
                $pricePrevius = $pricePrevius->text();
            } else { $pricePrevius = "";}
            
            $interest = 0;
            if ($divs->filter('a.flexifi-link')->count() > 0) {
                $interest = 1;
            }
            $warranty_fiance = $divs->filter('a.item-info-more')->text();
            $iten_info = $divs->filter('div .item-info-more');

            $dataLinks = [];
            $dataCount_1 = $iten_info->eq(0);
            if ($dataCount_1->count()) {
                $dataLinks[]  = $dataCount_1->text();
            }
            $dataCount_2 = $iten_info->eq(1);
            if ($dataCount_2->count()) {
                $dataLinks[]  = $dataCount_2->text();
            }
            
            $sectionDescription = $divs->eq(2);
            $title = $sectionDescription->filter('h4')->text();
            $price = $divs->filter('.section-title')->text();
           
            $dataD = $sectionDescription->filter('li');
            $dataDescripcion = [];
            $dataDescripcion[]  = $dataD->eq(0)->text();
            $dataDescripcion[]  = $dataD->eq(1)->text();
            $cont_2 = $dataD->eq(2);
            if ($cont_2->count()) {
                $dataDescripcion[] = $cont_2->text();
            }
            $cont_3 = $dataD->eq(3);
            if ($cont_3->count()) {
                $dataDescripcion[] = $cont_3->text();
            }

            $dataInsert['id'] = $id;
            $dataInsert['title'] = $title;
            $dataInsert['price'] = $price;
            $dataInsert['price_previus'] = $pricePrevius;
            $dataInsert['link'] = json_encode($dataLinks);
            $dataInsert['interest'] = $interest;
            $dataInsert['description'] = json_encode($dataDescripcion);
            $dataInsert['image'] = $urlImage;
            $dataInsert['logo'] = $urlLogo;
            $dataInsert['status_warranty'] = $statusWarranty;
            
            return $dataInsert;
        });
        
        return $appliances;
    }

    public function StoreAppliances($data) {
        foreach ($data as $key => $appliances) {
            foreach ($appliances as $key => $value) {
                $image = "";
                $logo = "";
                $result = DB::table( 'appliances' )->where( 'id', $value['id'] )->exists();
                if (!$result) {
                    $image = $this->imagesSave(1, 'appliances', $value['image'], 'jpeg');
                    $logo = $this->imagesSave(1, 'appliances', $value['logo'], 'jpg');
                    
                    $appliance = new Appliance();
                    $appliance->id = $value['id'];
                    $appliance->title = $value['title'];
                    $appliance->price = $value['price'];
                    $appliance->price_previus = $value['price_previus'];
                    $appliance->interest = $value['interest'];
                    $appliance->links = $value['link'];
                    $appliance->description = $value['description'];
                    $appliance->image = $image;
                    $appliance->logo = $logo;
                    $appliance->status_warranty = $value['status_warranty'];
                    $appliance->save();
                }
            }
        }
        return ["message" => "Proceso realizado co exito"];
    }

    /**
     * [StoreAppliances description: metodo que permite realizar un scraping de los electrodomesticos para luego ser insertados en la base de datos]
     */
    public function ScrapingDishwasher() {
        $data = [];
        $url = 'https://www.appliancesdelivered.ie/dishwashers?sort=price_asc&page=1';
        $class = 'div.products-count';
        
        $client = new Client();
        $count = $this->productCounts($client, $url, $class);
        
        for ($i=0; $i < $count ; $i++) { 
            $crawler = $client->request('GET', 'https://www.appliancesdelivered.ie/dishwashers?sort=price_asc&page='. $i);
            $data [] = $this->extractDishwasher($crawler);
        }
        return $data;
    }

    private function extractDishwasher (Crawler $crawler) {
        $inlineDishwasherClass ='.search-results-product';
        $dishwasher = $crawler->filter($inlineDishwasherClass)->each(function (Crawler $dishwasherNode) {
            $dataInsert = [];
            $url = $dishwasherNode->children()->filter('div a')->attr('href');

            $portions = explode("/", $url);
            $id = end($portions);
                        
            $divs = $dishwasherNode->children()->filter('div');
            $urlImage = $divs->filter('div a img')->attr('data-src');
            
            $sectionDescription = $divs->eq(2);
            //se optiene la url del logo del producto
            $urlLogo = $sectionDescription->filter('div div a picture img')->attr('data-src');
            
            $urlWarranty = $sectionDescription->filter('div div.sales-container picture img');
            $statusWarranty = 0;
            if ($urlWarranty->count() > 0) {$statusWarranty = 1;}
            
            $title = $sectionDescription->filter('h4')->first()->text();
            $price = $divs->filter('.section-title')->text();
            
            $pricePrevius = $divs->filter('h5.price-previous');
            if ($pricePrevius->count() > 0) {
                $pricePrevius = $pricePrevius->text();
            } else { $pricePrevius = "";}
            
            $interest = 0;
            if ($divs->filter('a.flexifi-link')->count() > 0) {
                $interest = 1;
            }

            $recycling = 0;
            if ($divs->filter('p.recycling-cost-text')->count() > 0) {
                $recycling = 1;
            }
           
            $iten_span = $divs->filter('div .item-info-more');           
            $dataLinks = [];
            $dataLinks[]  = $iten_span->eq(0)->text();
            $dataLinks[]  = $iten_span->eq(1)->text();
            $dataLinks[]  = $iten_span->eq(2)->text();
            $dataLinks[]  = $iten_span->eq(3)->text();
            $dataCount = $iten_span->eq(4);
            if ($dataCount->count()) {
                $dataLinks[]  = $dataCount->text();
            }

            $sectionDescription = $divs->eq(2);
            $title = $sectionDescription->filter('h4')->text();
            $price = $divs->filter('.section-title')->text();
            
            $dataD = $sectionDescription->filter('li');
            $dataDescripcion = [];
            $dataDescripcion[]  = $dataD->eq(0)->text();
            $dataDescripcion[]  = $dataD->eq(1)->text();
            $cont_2 = $dataD->eq(2);
            if ($cont_2->count()) {
                $dataDescripcion[] = $cont_2->text();
            }
            $cont_3 = $dataD->eq(3);
            if ($cont_3->count()) {
                $dataDescripcion[] = $cont_3->text();
            }

            $dataInsert['id'] = $id;
            $dataInsert['title'] = $title;
            $dataInsert['price'] = $price;
            $dataInsert['price_previus'] = $pricePrevius;
            $dataInsert['recycling'] = $recycling;
            $dataInsert['interest'] = $interest;
            $dataInsert['description'] = json_encode($dataDescripcion);
            $dataInsert['links'] = json_encode($dataLinks);
            $dataInsert['image'] = $urlImage;
            $dataInsert['logo'] = $urlLogo;
            $dataInsert['status_warranty'] = $statusWarranty;
            
            return $dataInsert;
        });
        return $dishwasher;
    }

    public function StoreDishwasher($data) {
        foreach ($data as $key => $dishwasher) {
            foreach ($dishwasher as $key2 => $value) {
                $image = "";
                $logo = "";
                $result = DB::table( 'dishwashers' )->where( 'id', $value['id'] )->exists();
                if (!$result) {
                    $image = $this->imagesSave(1, 'dishwasher', $value['image'], 'jpeg');
                    $logo = $this->imagesSave(1, 'dishwasher', $value['logo'], 'jpg');
                    $appliance = new Dishwasher();
                    $appliance->id = $value['id'];
                    $appliance->title = $value['title'];
                    $appliance->price = $value['price'];
                    $appliance->price_previus = $value['price_previus'];
                    $appliance->recycling = $value['recycling'];
                    $appliance->interest = $value['interest'];
                    $appliance->description = $value['description'];
                    $appliance->links = $value['links'];
                    $appliance->image = $image;
                    $appliance->logo = $logo;
                    $appliance->status_warranty = $value['status_warranty'];
                    $appliance->save();
                }
            }
        }
        return ["message" => "Proceso realizado con exito"];
    }

    /**
     * @param $userID
     * @param $module
     * @param $objectID
     * @param string $ext
     */
    public function imagesSave($userID, $module, $objectID, $ext = 'png')
    { 
        $image = file_get_contents($objectID);
        $name = public_path() .'/images/' . $module . '/' . md5($objectID) . '.' . $ext;
        file_put_contents(public_path() .'/images/' . $module . '/' . md5($objectID) . '.' . $ext, $image); // guardamos la imagen 
        return $name;
    }
}

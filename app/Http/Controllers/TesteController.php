<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
date_default_timezone_set('America/Sao_Paulo');

class TesteController extends Controller
{
    public function testePegarDados(){
        // URL do arquivo .gz
        $gzippedFileUrl = 'https://challenges.coode.sh/food/data/json/products_01.json.gz';

        
        // Nome do arquivo temporário para o .gz
        $tempGzippedFile = tempnam(sys_get_temp_dir(), 'gzipped_file');

        // Baixar o arquivo .gz
        $response = Http::get($gzippedFileUrl);
        //$inicioMemoria = memory_get_usage();
        if ($response->successful()) {
        
            $gzippedData = $response->body();
            file_put_contents($tempGzippedFile, $gzippedData);

            $zp = gzopen($tempGzippedFile, 'rb');

            $buffer = '';
            $counter = 0;
            while ($counter < 3) {
                $buffer .= gzread($zp, 5); // Ler em partes de 5 Bytes
                
                // Verificar se o buffer contém um objeto JSON completo
                if (strpos($buffer, '{') !== false && strpos($buffer, '}') !== false) {
                    //dd(json_decode($buffer));
                   
                    //if($acum == 2)
                        //dd($buffer);
                        
                    $json = json_decode($buffer);
                    //dd((int)preg_replace('/[^0-9\s]/', '', $json->code) );
     
                    if ($json !== null) {
                        try {
                            $product = Product::updateOrCreate([
                                'code' => (int) preg_replace('/[^0-9\s]/', '', $json->code),
                                'status'=> 'published',
                                'imported_t'=> date('Y-m-d\TH:i:s\Z'),
                                'url'=> $json->url,
                                'creator'=> $json->creator,
                                'created_t'=> $json->created_t,
                                'last_modified_t'=> $json->last_modified_t,
                                'product_name'=> $json->product_name,
                                'quantity'=> $json->quantity,
                                'brands'=> $json->brands,
                                'categories'=> $json->categories,
                                'labels'=>  $json->labels,
                                'cities'=>  $json->cities,
                                'purchase_places'=> $json->purchase_places,
                                'stores'=> $json->stores,
                                'ingredients_text'=>$json->ingredients_text,
                                'traces'=> $json->traces,
                                'serving_size'=> $json->serving_size,
                                'serving_quantity'=>  empty($json->serving_quantity) ? null : $json->serving_quantity,
                                'nutriscore_score'=> empty($json->nutriscore_score) ? null  : $json->nutriscore_score,
                                'nutriscore_grade'=> $json->nutriscore_grade,
                                'main_category'=>  $json->main_category,
                                'image_url'=> $json->image_url
                            ]);
                            
                        } catch (\Throwable $th) {
                            dd($th);
                        }
                       
                        $counter++;

                        $buffer = '';
                       
                    }
                }
            }
            gzclose($zp);
            //dd(json_encode($date));
        } else {
            // Falha ao obter o arquivo .gz
        }
        // Excluir o arquivo temporário .gz
        unlink($tempGzippedFile);

        $picodememoria = memory_get_peak_usage();

        dd('pico de memória: '.(round(($picodememoria/1048576),2)).' MB', 'Tempo totl em segundos: '.  microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], date('Y-m-d H:i:s'));
       
    }
}

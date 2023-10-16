<?php

namespace App\Jobs;

use App\Models\ErrorImport;
use App\Models\FailedJob;
use App\Models\LogImport;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
date_default_timezone_set('America/Sao_Paulo');

class TakeProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
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
            while ($counter < 4) {
                $buffer .= gzread($zp, 5); // Ler em partes de 5 Bytes
                
                // Verificar se o buffer contém um objeto JSON completo
                if (strpos($buffer, '{') !== false && strpos($buffer, '}') !== false) {
                        
                    $json = json_decode($buffer);

                    if ($json !== null) {
                        try {
                            Product::updateOrInsert(
                                ['code' => (int) preg_replace('/[^0-9\s]/', '', $json->code)],
                                [
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
                            
                            ErrorImport::create([
                                'date_error' => date('Y-m-d H:i:s')
                            ]);

                            logs()->error($th);
                            break;
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

        $memorySpike = memory_get_peak_usage();

        try {
            LogImport::create([
                'last_import' => date('Y-m-d H:i:s'),
                'memory_usage' => round(($memorySpike/1048576),2)
            ]);    
        } catch (\Throwable $th) {
            logs()->error($th);
        }
        
    }
}

<?php

namespace App\Jobs;

use App\Models\ErrorImport;
use App\Models\FailedJob;
use App\Models\LogImport;
use App\Models\Product;
use Exception;
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
        $start_time = microtime(true);

        $response = Http::get('https://challenges.coode.sh/food/data/json/index.txt');
        $result = explode("\n",$response->body());

        foreach($result as $key_index => $fileName){

            if(!empty($fileName)){
                // URL do arquivo .gz
                $gzippedFileUrl = 'https://challenges.coode.sh/food/data/json/'.$fileName;
                
                // Nome do arquivo temporário para o .gz
                $tempGzippedFile = tempnam(sys_get_temp_dir(), 'gzipped_file');

                // Baixar o arquivo .gz
                $response = Http::get($gzippedFileUrl);

                if ($response->successful()) {
                
                    file_put_contents($tempGzippedFile, $response->body());

                    $zp = gzopen($tempGzippedFile, 'rb');

                    $buffer = '';
                    $counter = 0;
                    while ($counter < 100) {
                        $buffer .= gzread($zp, 1); // Ler em partes de 1 Byte
                        
                        // Verificar se o buffer contém um objeto JSON completo
                        if (strpos($buffer, '{') !== false && strpos($buffer, '}') !== false) {
                                
                            $json = json_decode($buffer);

                            if ($json !== null) {
                                try {
                                    Product::updateOrCreate(
                                        ['code' => (int) preg_replace('/[^0-9\s]/', '', $json->code)],
                                        [
                                            'status'=> 'published',
                                            'imported_t'=> date('Y-m-d\TH:i:s\Z'),
                                            'url'=> $json->url,
                                            'creator'=> $json->creator,
                                            'created_t'=> date("Y-m-d H:i:s", $json->created_t),
                                            'last_modified_t'=> date("Y-m-d H:i:s", $json->last_modified_t),
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
                                            'nutriscore_score'=> empty($json->nutriscore_score) ? null : $json->nutriscore_score,
                                            'nutriscore_grade'=> $json->nutriscore_grade,
                                            'main_category'=>  $json->main_category,
                                            'image_url'=> $json->image_url
                                        ]);
                                    
                                } catch (Exception $e) {
                                    $end_time = microtime(true);
                                    $memorySpike = memory_get_peak_usage();
                                    $log_import = LogImport::create([
                                        'last_import' => date('Y-m-d H:i:s'),
                                        'memory_usage_in_mb' => round(($memorySpike/1048576),2),
                                        'online_time_in_seconds' => ($end_time - $start_time),
                                        'status' => false
                                    ]);

                                    ErrorImport::create([
                                        'date_error' => date('Y-m-d H:i:s'),
                                        'log_import_id' => $log_import->id

                                    ]);

                                    logs()->error($e->getMessage());
                                    gzclose($zp);
                                    unlink($tempGzippedFile);
                                    break 2;
                                }
                            
                                $counter++;

                                $buffer = '';
                            
                            }
                        }
                    }
                    gzclose($zp);

                } else {
                    $end_time = microtime(true);
                    $memorySpike = memory_get_peak_usage();
                    $log_import = LogImport::create([
                        'last_import' => date('Y-m-d H:i:s'),
                        'memory_usage_in_mb' => round(($memorySpike/1048576),2),
                        'online_time_in_seconds' => ($end_time - $start_time),
                        'status' => false
                    ]);

                    ErrorImport::create([
                        'date_error' => date('Y-m-d H:i:s'),
                        'log_import_id' => $log_import->id

                    ]);

                    logs()->error('Unable to generate file');
                    unlink($tempGzippedFile);
                    break;
                }
                // Excluir o arquivo temporário .gz
                unlink($tempGzippedFile);
                
                
            }
        }
      
        $memorySpike = memory_get_peak_usage();

        $end_time = microtime(true);
        $time = $end_time - $start_time;
        $memory = round(($memorySpike/1048576),2);
        try {
            LogImport::create([
                'last_import' => date('Y-m-d H:i:s'),
                'memory_usage_in_mb' => $memory,
                'online_time_in_seconds' => $time,
                'status' => true
            ]);    
        } catch (Exception $e) {
            logs()->error($e->getMessage());
        }
                
    }
}

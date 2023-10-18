<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
date_default_timezone_set('America/Sao_Paulo');

class TesteController extends Controller
{
    public function testePegarDados(){

        $start_time = microtime(true);

       
        $response = Http::get('https://challenges.coode.sh/food/data/json/index.txt');
        $result = explode("\n",$response->body());
        $end_time = microtime(true);
        dd( $end_time - $start_time );
        foreach($result as $key_index => $fileName){

            if(!empty($fileName)){
                // URL do arquivo .gz
                $gzippedFileUrl = 'https://challenges.coode.sh/food/data/json/'.$fileName;

                // Nome do arquivo temporário para o .gz
                $tempGzippedFile = tempnam(sys_get_temp_dir(), 'gzipped_file');

                // Baixar o arquivo .gz
                $response = Http::get($gzippedFileUrl);
                //$inicioMemoria = memory_get_usage();
                if ($response->successful()) {
                    var_dump( $key_index);
                    //$gzippedData = ;
                    
                    file_put_contents($tempGzippedFile, $response->body());

                    $zp = gzopen($tempGzippedFile, 'rb');

                    $buffer = '';
                    $counter = 0;
                    while ($counter < 4) {
                        $buffer .= gzread($zp, 1); // Ler em partes de 1 Byte
                        
                        // Verificar se o buffer contém um objeto JSON completo
                        if (strpos($buffer, '{') !== false && strpos($buffer, '}') !== false) {
                            //if($key_index == 1)
                                //dd($buffer);
                        
                            //if($acum == 2)
                                //dd($buffer);
                            try {
                                $json = json_decode($buffer);
                            } catch (\Throwable $th) {
                                dd($buffer);
                            } 
                            // Defina um limite de tempo de execução para 30 segundos
                            set_time_limit(30);

                            try {
                                // Execute a operação que pode levar muito tempo
                                $json = json_decode($buffer);

                                if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
                                    dd($buffer, $fileName ,$key_index);
                                    throw new Exception("Erro na decodificação JSON: " . json_last_error_msg());
                                }

                                // Se a operação for bem-sucedida, redefina o limite de tempo de execução
                                set_time_limit(0);
                            } catch (Exception $e) {
                                // Lidar com a exceção
                                unlink($tempGzippedFile);
                                dd($buffer, $fileName ,$key_index);
                                echo "Exceção: " . $e->getMessage();
                                // Ou, você pode lançar outra exceção se necessário
                                throw new Exception("Operação levou mais de 30 segundos para ser concluída");
                            }

                            // Continue o restante do seu código
                            
                            //dd((int)preg_replace('/[^0-9\s]/', '', $json->code) );
                    
                            if ($json !== null) {
                                try {
                                    Product::updateOrCreate(
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
                                    dd($th);
                                    break 2;
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
                $end_time = microtime(true);
                var_dump('pico de memória: '.(round(($picodememoria/1048576),2)).' MB', 'Tempo total em segundos: '.  microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], date('Y-m-d H:i:s'));
            }
        }
    }
}

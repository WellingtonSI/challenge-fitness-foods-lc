<?php

namespace App\Http\Controllers;

use App\Models\LogImport;
use Illuminate\Http\Request;

class ApiInfoController extends Controller
{
    public function __invoke()
    {   
        try {
            $data = LogImport::latest()->first();
            $result = [
                'last_import' => $data->last_import,
                'memory_usage_in_mb' => $data->memory_usage_in_mb,
                'online_time_in_seconds' => number_format($data->online_time_in_seconds,2)
            ];
            return response()->json($result,200);
        } catch (\Exception $e) {
            response()->json('The query could not be performed, please try again later',500);
        }
       
    }


}

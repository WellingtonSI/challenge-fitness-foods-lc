<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    /**
     * Display the specified resource.
     */
    public function show(int $code)
    {
        $product = Product::findOrFail($code);

        return response()->json($product,200);
    }

    public function list(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'per_page' => 'numeric'
        ]);

        if($validator->fails())
            return response()->json($validator->errors(), 422); 

        $products = Product::paginate($request->per_page ?? 15);

        return response()->json($products,200);
    }

   
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $code)
    {
       
        $validator = Validator::make($request->all(), [
            "status" => "string",
            "imported_t"=> "date",
            "url"=> "string",
            "creator"=> "string",
            "product_name"=> "string",
            "quantity"=> "string",
            "brands"=> "string",
            "categories"=> "string",
            "labels"=> "string",
            "cities"=> "string",
            "purchase_places"=> "string",
            "stores"=> "string",
            "ingredients_text"=> "string",
            "traces"=> "string",
            "serving_size"=> "string",
            "serving_quantity"=> "numeric",
            "nutriscore_score"=> "numeric",
            "nutriscore_grade"=> "string",
            "main_category"=> "string",
            "image_url"=> "string"
        ]);

        if($validator->fails())
            return response()->json($validator->errors(), 422);

        Product::where('code',$code)
                    ->firstOrFail()
                    ->update($request->all());
                    
        return response()->json('Sucess', 200);        
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $code)
    {
  
        Product::where('code',$code)
                ->firstOrFail()
                ->update(['status' => 'trash']);

        return response()->json('Deleted',200);

    }
}

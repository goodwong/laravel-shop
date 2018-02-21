<?php

namespace Goodwong\LaravelShop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $file = $request->file('image');
        if (!$file) {
            return 'no image';
        }
        // save path...
        $path = 'products';
        $shop_id = $request->input('shop');
        if ($shop_id) {
            $path = "{$path}/shop-{$shop_id}";
        }
        $product_id = $request->input('product');
        if ($product_id) {
            $path = "{$path}/product-{$product_id}";
        }
        $date = date('Ymd');
        $path = "{$path}/{$date}";
    
        $path = $file->store($path, 'public');
        $path = "storage/{$path}";

        // corp image size...
        $image = app('image')->make(public_path($path));
        $width = $request->input('w');
        $height = $request->input('h');
        if ($width && $height) {
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            })->save();
        }
        $width = $image->width();
        $height = $image->height();

        // response...
        $size = filesize($path);
        return ['url' => url($path), 'size' => $size, 'width' => $width, 'height' => $height];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $path = $request->input('path');
        $path = preg_replace('|^https?://[^\/]+/storage/|', '', $path);
        Storage::disk('public')->delete($path);
        return response('ok', 204);
    }
}

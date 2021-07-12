<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChainFactory;


if (!function_exists('uploadImage')) {
    function uploadImage($key = "avatar", $folder = 'users')
    {

        $request = \request();
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            $format = $file->getClientOriginalExtension();

            $name = time() . ".$format";
            Storage::put($name, $file->getContent());
            if (Storage::move($name, "public/$folder/" . $name)) {
                $optimizerChain = OptimizerChainFactory::create();
                $optimizerChain->optimize(storage_path('app/public/' . $folder . '/' . $name));
                return "/$folder/" . $name;
            }
        }
        return false;
    }
}

if (!function_exists('showImage')) {
    function showImage($folder, $image)
    {
        $path = storage_path('app/public/images/' . $folder . '/' . $image);
        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type)->send();

        return $response;
    }
}

if (!function_exists('showImage2')) {
    function showImage2($folder, $image)
    {
        $path = storage_path('app/public/' . $folder . '/' . $image);
        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type)->send();

        return $response;
    }
}

if (!function_exists('uploadFile')) {
    function uploadFile($key = "file", $folder = 'projects', $oldFile = false)
    {
        $request = request();
        if ($request->hasFile($key)) {
            if ($oldFile)
                Storage::disk('public')->delete($oldFile);

            $uploadedFile = $request->file($key);
            $moved = Storage::disk('public')->put($folder, $uploadedFile);

            if ($moved)
                return $moved; // url to file
        }
        return $oldFile;
    }
}


if (!function_exists('uploadMultiImages')) {
    function uploadMultiImages($key = "photos", $folder = 'projects', $oldFiles = false)
    {
        if(request()->hasFile($key)){
            if ($oldFiles) {
                foreach ($oldFiles as $file)
                    Storage::disk('public')->delete($file);
            }

            $request = \request();
            $imagesNames = array();
            foreach ($request->file($key) as $image) {
                Storage::disk('public')->exists($folder) or Storage::disk('public')->makeDirectory($folder);
                $imageName = Storage::disk('public')->put($folder, $image);
                $optimizerChain = OptimizerChainFactory::create();
                $optimizerChain->optimize(storage_path("app/public/$imageName"));
                array_push($imagesNames, $imageName);
            }
            return $imagesNames;
        }

        return $oldFiles;
    }
}

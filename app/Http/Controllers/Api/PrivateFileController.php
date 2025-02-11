<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrivateFileController extends Controller
{
    public function show($filename)
    {

        //Autorisation d'accès à ajouter ici

        $filePath = storage_path('app/private/' . $filename);

        if (!Storage::exists('private/' . $filename)) {
            return response()->json(['error' => 'Fichier non trouvé.'], 404);
        }

        return response()->file($filePath);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Article;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Maximum 2MB
            ]);
            
            $articleId = $request->articleId; 
            $article = Article::find($articleId);
    
            // Vérifier s'il y a déjà une image pour cet article
            if ($article->art_image) {
                // Supprimer l'image existante
                $imagePath = storage_path('app/' . $article->art_image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $image = $request->file('image');
            $fileName = $request->fileName; 
            $filePath = $request->filePath; 
            
            $imageName = $fileName . '.' . $image->getClientOriginalExtension();
            $image->storeAs($filePath, $imageName);
            $imagePath = $filePath . '/' . $imageName;
    
            $article->art_image = $imagePath;
            $article->save();
    
            return response()->json(['message' => 'Image mise en ligne avec succès.', 'path' => $imagePath, 'article' => $article], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'upload de l\'image.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}

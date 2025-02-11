<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Article;

class ImageController extends Controller
{
    public function uploadArticle(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:4096', // Maximum 2MB
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

    public function delete(Request $request){
        try {
            $fileName = $request->fileName;
            $filePath = $request->filePath;
            
            // Rechercher et supprimer le fichier existant quelle que soit son extension
            $directoryPath = storage_path('app/' . $filePath);
            if (is_dir($directoryPath)) {
                $existingFiles = glob($directoryPath . '/' . $fileName . '.*');
                foreach ($existingFiles as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            
            return response()->json([
                'status' => 200, 
                'message' => 'L\'image a été supprimée avec succès.'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'upload de l\'image.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function upload(Request $request)
    {
        try {
            // Debug initial de la requête
            \Log::info('Début upload - POST parameters', ['params' => $request->all()]);
            \Log::info('Files in request', ['files' => $request->allFiles()]);
            \Log::info('PHP settings', [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ]);
    
            // Vérification initiale du fichier
            if (!$request->hasFile('image')) {
                \Log::warning('Aucun fichier dans la requête', [
                    'FILES' => $_FILES,
                    'request_size' => $request->header('Content-Length')
                ]);
                return response()->json([
                    'error' => 'Aucun fichier reçu',
                    'message' => 'Veuillez sélectionner une image à uploader',
                    'debug' => [
                        'FILES' => $_FILES,
                        'request_size' => $request->header('Content-Length'),
                        'max_upload' => ini_get('upload_max_filesize'),
                        'max_post' => ini_get('post_max_size')
                    ]
                ], 400);
            }
    
            // Validation avec catch spécifique pour les erreurs de validation 
            try {
                $validatedData = $request->validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::warning('Erreur de validation', [
                    'errors' => $e->errors()
                ]);
                return response()->json([
                    'error' => 'Erreur de validation du fichier',
                    'message' => 'Le fichier doit être une image (JPG, PNG, GIF ou WEBP)',
                    'debug' => $e->errors()
                ], 422);
            }
    
            $image = $request->file('image');
            $fileName = $request->fileName;
            $filePath = $request->filePath;
    
            \Log::info('Fichier validé', [
                'original_name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'mime' => $image->getMimeType()
            ]);
    
            // Vérification des paramètres requis
            if (!$fileName || !$filePath) {
                return response()->json([
                    'error' => 'Paramètres manquants',
                    'message' => 'Le nom du fichier et le chemin sont requis'
                ], 400);
            }
    
            $imageName = $fileName . '.' . $image->getClientOriginalExtension();
            $fullPath = $filePath . '/' . $imageName;
            $directoryPath = storage_path('app/' . $filePath);
    
            // Vérification du répertoire
            if (!is_dir($directoryPath)) {
                \Log::error('Répertoire invalide', [
                    'path' => $directoryPath
                ]);
                return response()->json([
                    'error' => 'Répertoire de destination invalide',
                    'message' => 'Le dossier de destination n\'existe pas'
                ], 500);
            }
    
            // Suppression des fichiers existants
            if (is_dir($directoryPath)) {
                $existingFiles = glob($directoryPath . '/' . $fileName . '.*');
                foreach ($existingFiles as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
    
            // Tentative de stockage
            try {
                $stored = $image->storeAs($filePath, $imageName);
                
                if (!$stored) {
                    \Log::error('Échec du stockage', [
                        'path' => $fullPath
                    ]);
                    return response()->json([
                        'error' => 'Échec du stockage du fichier',
                        'message' => 'Impossible de sauvegarder l\'image'
                    ], 500);
                }
    
                \Log::info('Upload réussi', [
                    'path' => $fullPath
                ]);
                return response()->json([
                    'status' => 200,
                    'message' => 'Image mise en ligne avec succès',
                    'path' => $fullPath
                ], 200);
    
            } catch (\Exception $e) {
                \Log::error('Erreur lors du stockage', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Erreur lors du stockage du fichier',
                    'message' => 'Une erreur est survenue lors de la sauvegarde de l\'image'
                ], 500);
            }
    
        } catch (\Exception $e) {
            \Log::error('Exception générale dans upload', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'upload de l\'image',
                'message' => 'Une erreur inattendue est survenue pendant l\'upload',
                'debug' => [
                    'error' => $e->getMessage(),
                    'php_settings' => [
                        'upload_max_filesize' => ini_get('upload_max_filesize'),
                        'post_max_size' => ini_get('post_max_size'),
                        'max_execution_time' => ini_get('max_execution_time')
                    ]
                ]
            ], 500);
        }
    }
    
    protected function getFileErrorMessage($code)
    {
        $messages = [
            UPLOAD_ERR_OK => 'Le fichier a été uploadé avec succès.',
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP.INI.',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire HTML.',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé.',
            UPLOAD_ERR_NO_TMP_DIR => 'Le dossier temporaire est manquant.',
            UPLOAD_ERR_CANT_WRITE => 'Échec d\'écriture du fichier sur le disque.',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier.'
        ];
    
        return $messages[$code] ?? 'Erreur d\'upload inconnue.';
    }

    public function getImage(Request $request)
    {
        try {
            $path = $request->query('path');
            $name = $request->query('name');
            
            if (!$path || !$name) {
                return response()->json([
                    'status' => '400',
                    'error' => 'Paramètres manquants',
                    'message' => 'Le chemin et le nom de l\'image sont requis'
                ]);
            }
            
            // Liste des extensions à vérifier
            $extensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
            $imagePath = null;
            
            // Cherche le fichier avec les différentes extensions possibles
            foreach ($extensions as $ext) {
                $testPath = $path . '/' . $name . '.' . $ext;
                if (Storage::disk('local')->exists($testPath)) {
                    return response()->file(storage_path('app/' . $testPath));
                }
            }
            
            // Si aucune image n'est trouvée
            return response()->json([
                'status' => '404',
                'error' => 'Image non trouvée.',
                'message' => 'L\'image demandée n\'existe pas.'
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la récupération de l\'image.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

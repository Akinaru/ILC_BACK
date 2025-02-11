<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Models\Account;

class DocumentsController extends Controller
{
    public function upload(Request $request)
    {
        try {
            // Valider les champs de la requête
            $validatedData = $request->validate([
                'file' => 'required|file|mimes:pdf,xls,xlsx|max:20480', // Maximum 20MB
                'title' => 'required|string|max:255',
                'folder' => 'required|string|max:255',
            ]);
    
            // Vérifier si le fichier est présent dans la requête
            if (!$request->hasFile('file')) {
                return response()->json([
                    'error' => 'Aucun fichier trouvé dans la requête.'
                ], 400);
            }
    
            $file = $request->file('file');
            $title = $request->input('title');
            $folder = $request->input('folder');
    
            // Construire le chemin complet du fichier avec un nom unique
            $fileName = $title . '.' . $file->getClientOriginalExtension();
            $filePath = $folder . '/' . $fileName;
    
            // Stocker le nouveau fichier dans le dossier private
            $file->storeAs($folder, $fileName, 'private');
    
            if (str_contains($title, 'choix_cours')) {
                $login = str_replace('choix_cours_', '', $title);
                $account = Account::where('acc_id', $login)->first();
                if ($account) {
                    $account->acc_validechoixcours = false;
                    $account->save();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Fichier mis en ligne avec succès.',
                'path' => $filePath,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'upload du fichier.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function checkFileExists($folder, $filename)
    {
        // Construire le chemin complet du répertoire
        $directoryPath = 'documents/' . $folder;
    
        // Récupérer tous les fichiers dans le répertoire
        $files = Storage::disk('private')->allFiles($directoryPath);
    
        // Parcourir les fichiers pour trouver celui qui correspond au nom donné
        foreach ($files as $file) {
            // Extraire le nom du fichier sans l'extension
            $fileWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
    
            // Vérifier si le nom du fichier correspond au nom donné
            if ($fileWithoutExtension === $filename) {
                // Le fichier a été trouvé, retourner son chemin complet avec l'extension
                return response()->json([
                    'status' => 200,
                    'exists' => true,
                    'message' => 'Le fichier existe.',
                    'path' => $file, // Inclut l'extension
                ]);
            }
        }
    
        // Aucun fichier correspondant n'a été trouvé
        return response()->json([
            'status' => 404,
            'exists' => false,
            'message' => 'Le fichier n\'existe pas.',
        ]);
    }

    public function checkFileExistsPerso($folder, $filename, $login)
    {
        // Construire le chemin complet du répertoire
        $directoryPath = 'documents/' . $folder;

        // Récupérer tous les fichiers dans le répertoire
        $files = Storage::disk('private')->allFiles($directoryPath);

        // Construire le nom de fichier à vérifier
        $fullFilename = "{$filename}_{$login}";

        // Parcourir les fichiers pour trouver celui qui correspond au nom donné
        foreach ($files as $file) {
            // Extraire le nom du fichier sans l'extension
            $fileWithoutExtension = pathinfo($file, PATHINFO_FILENAME);

            // Vérifier si le nom du fichier correspond au nom complet
            if ($fileWithoutExtension === $fullFilename) {
                // Le fichier a été trouvé, retourner son chemin complet avec l'extension
                return response()->json([
                    'status' => 200,
                    'exists' => true,
                    'message' => 'Le fichier existe.',
                    'path' => $file, // Inclut l'extension
                ]);
            }
        }

        // Aucun fichier correspondant n'a été trouvé
        return response()->json([
            'status' => 404,
            'exists' => false,
            'message' => 'Le fichier n\'existe pas.',
        ]);
    }
    
    
    public function getDocument($folder, $filename)
    {
        // Assurez-vous que l'utilisateur a le droit d'accéder à ce fichier, selon vos règles de sécurité.
    
        // Construire le chemin complet du fichier
        $filePath = storage_path('app/private/documents/' . $folder . '/' . $filename);
    
        // Vérifier si le fichier existe
        if (file_exists($filePath)) {
            // Créer une réponse binaire pour le fichier
            $response = new BinaryFileResponse($filePath);
    
            // Vérifier si le fichier est un PDF
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'pdf') {
                // Désactiver la mise en cache pour le PDF
                $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            } else {
                // Forcer le téléchargement pour les autres types de fichiers
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
            }
    
            // Retourner la réponse
            return $response;
        } else {
            // Retourner une réponse d'erreur si le fichier n'existe pas
            abort(404);
        }
    }

    public function getMyDocument($folder, $filename)
    {
        // Construire le chemin complet du fichier
        $filePath = storage_path('app/private/documents/etu/' . $folder . '/' . $filename);
    
        // Vérifier si le fichier existe
        if (file_exists($filePath)) {
            // Créer une réponse binaire pour le fichier
            $response = new BinaryFileResponse($filePath);
    
            // Vérifier si le fichier est un PDF
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'pdf') {
                // Désactiver la mise en cache pour le PDF
                $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            } else {
                // Forcer le téléchargement pour les autres types de fichiers
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
            }
    
            // Retourner la réponse
            return $response;
        } else {
            // Retourner une réponse d'erreur si le fichier n'existe pas
            abort(404);
        }
    }

        // Méthode pour supprimer un fichier
        public function delete($folder, $filename)
    {
        try {
            // Construire le chemin complet du répertoire
            $directoryPath = 'documents/' . $folder;

            // Récupérer tous les fichiers dans le répertoire
            $files = Storage::disk('private')->allFiles($directoryPath);

            // Parcourir les fichiers pour trouver celui qui correspond au nom donné sans extension
            foreach ($files as $file) {
                // Extraire le nom du fichier sans l'extension
                $fileWithoutExtension = pathinfo($file, PATHINFO_FILENAME);

                // Vérifier si le nom du fichier correspond au nom donné
                if ($fileWithoutExtension === $filename) {
                    // Le fichier a été trouvé, on peut maintenant le supprimer
                    Storage::disk('private')->delete($file);

                    if (str_contains($filename, 'choix_cours')) {
                        $login = str_replace('choix_cours_', '', $filename);
                        $account = Account::where('acc_id', $login)->first();
                        if ($account) {
                            $account->acc_validechoixcours = false;
                            $account->save();
                        }
                    }

                    return response()->json([
                        'status' => 200,
                        'message' => 'Fichier supprimé avec succès.',
                    ]);
                }
            }

            // Aucun fichier correspondant n'a été trouvé
            return response()->json([
                'status' => 404,
                'error' => 'Fichier non trouvé.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la suppression du fichier.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
        // Méthode pour supprimer un fichier
        public function deletePerso($folder, $filename)
    {
        try {
            // Construire le chemin complet du répertoire
            $directoryPath = 'documents/etu/' . $folder;

            // Récupérer tous les fichiers dans le répertoire
            $files = Storage::disk('private')->allFiles($directoryPath);

            // Parcourir les fichiers pour trouver celui qui correspond au nom donné sans extension
            foreach ($files as $file) {
                // Extraire le nom du fichier sans l'extension
                $fileWithoutExtension = pathinfo($file, PATHINFO_FILENAME);

                // Vérifier si le nom du fichier correspond au nom donné
                if ($fileWithoutExtension === $filename) {
                    // Le fichier a été trouvé, on peut maintenant le supprimer
                    Storage::disk('private')->delete($file);

                    if (str_contains($filename, 'choix_cours')) {
                        $login = str_replace('choix_cours_', '', $filename);
                        $account = Account::where('acc_id', $login)->first();
                        if ($account) {
                            $account->acc_validechoixcours = false;
                            $account->save();
                        }
                    }

                    return response()->json([
                        'status' => 200,
                        'message' => 'Fichier supprimé avec succès.',
                    ]);
                }
            }

            // Aucun fichier correspondant n'a été trouvé
            return response()->json([
                'status' => 404,
                'error' => 'Fichier non trouvé.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la suppression du fichier.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}

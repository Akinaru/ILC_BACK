<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Models\Account;
use App\Models\Document;
use App\Models\Article;
use App\Models\DocumentArticle;
use App\Http\Resources\DocumentResource;

class DocumentsController extends Controller
{
    public function upload(Request $request)
    {
        try {
            // Valider les champs de la requête
            $validatedData = $request->validate([
                'file'   => 'required|file|mimes:pdf,xls,xlsx|max:20480', // Maximum 20MB
                'title'  => 'required|string|max:255',
                'folder' => 'required|string|max:255',
            ]);

            // Vérifier si le fichier est présent dans la requête
            if (!$request->hasFile('file')) {
                return response()->json([
                    'error' => 'Aucun fichier trouvé dans la requête.'
                ], 400);
            }

            $file   = $request->file('file');
            $title  = $request->input('title');
            $folder = $request->input('folder');

            // Construire le chemin complet du fichier avec un nom unique
            $fileName = $title . '.' . $file->getClientOriginalExtension();
            $filePath = $folder . '/' . $fileName;

            // 🔁 Supprimer toutes les anciennes versions avec le même "title" (pdf/xls/xlsx, etc.)
            $disk  = Storage::disk('private');
            $files = $disk->files($folder);

            foreach ($files as $existingFile) {
                // $existingFile est du type "folder/nom.ext"
                if (pathinfo($existingFile, PATHINFO_FILENAME) === $title && $existingFile !== $filePath) {
                    $disk->delete($existingFile);
                }
            }

            // Stocker le nouveau fichier dans le dossier private
            $file->storeAs($folder, $fileName, 'private');

            if (str_contains($title, 'choix_cours')) {
                $login   = str_replace('choix_cours_', '', $title);
                $account = Account::where('acc_id', $login)->first();
                if ($account) {
                    $account->acc_validechoixcours = false;
                    $account->save();
                }
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Fichier mis en ligne avec succès.',
                'path'    => $filePath,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Une erreur s\'est produite lors de l\'upload du fichier.',
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

    public function checkFileExistsPerso($folder, $filename)
    {
        $user = auth()->user();

        if (!$user || !$user->acc_id) {
            return response()->json([
                'status' => 403,
                'message' => 'Utilisateur non authentifié ou identifiant manquant.',
            ]);
        }

        $login = $user->acc_id;

        // Construire le chemin complet du répertoire
        $directoryPath = 'documents/' . $folder;

        // Récupérer tous les fichiers dans le répertoire
        $files = Storage::disk('private')->allFiles($directoryPath);

        // Construire le nom de fichier à vérifier
        $fullFilename = "{$filename}_{$login}";

        // Parcourir les fichiers pour trouver celui qui correspond au nom donné
        foreach ($files as $file) {
            $fileWithoutExtension = pathinfo($file, PATHINFO_FILENAME);

            if ($fileWithoutExtension === $fullFilename) {
                return response()->json([
                    'status' => 200,
                    'exists' => true,
                    'message' => 'Le fichier existe.',
                    'path' => $file,
                ]);
            }
        }

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

    //Fonctions pour gérer les documents des articles

    //Récupère tout les documents enregistrés sur la BD pour permettre leur attribution
    public function getAllDocumentsForArticle(){
        $documents = Document::orderBy('doc_name', 'asc')->get();

        $documentCollection = DocumentResource::collection($documents);

        return response()->json([
            'documents' => $documentCollection,
            'count' => $documentCollection->count(),
        ]);
    }

    // Récupère les documents attibués à un article en paramètre
    public function getDocumentArticle($idarticle)
    {
        try {
            if (!$idarticle) {
                return response()->json([
                    'status' => '400',
                    'error' => 'Paramètres manquants',
                    'message' => 'Le numéro de l\'article est requis'
                ]);
            }

            $article =  Article::findOrFail($idarticle);
            $documents = $article->documents;

            // Cherche les fichiers
            $documentCollection = DocumentResource::collection($documents);

            return response()->json([
                'documents' => $documentCollection,
                'count' => $documentCollection->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la récupération des documents.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadDocumentArticle($filename)
    {
        // Assurez-vous que l'utilisateur a le droit d'accéder à ce fichier, selon vos règles de sécurité.

        // Construire le chemin complet du fichier
        $filePath = storage_path('app/private/documents/admin/article/' . $filename);

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

    //upload un nouveau document sur le serveur et l'enregistre dans la BD pour permettre sa réutilisation
    public function uploadDocumentArticle(Request $request)
    {
        if($request->input('isNewOrOverride') == "true"){ //Dans le cas d'un nouveau document
            try {
                // Valider les champs de la requête
                $validatedData = $request->validate([
                    'file' => 'required|file|mimes:pdf,xls,xlsx,docx,pptx,odt|max:20480', // Maximum 20MB
                    'title' => 'required|string|max:255',
                    'folder' => 'required|string|max:255',
                    'articleId' => 'required|string',
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
                $art_id = $request->input('articleId');

                // Construire le chemin complet du fichier avec un nom unique
                $filePath = '/documents' . $folder . '/' . $title;

                // Stocker le nouveau fichier dans le dossier private
                $file->storeAs('/documents' . $folder, $title, 'private');

                //Vérification que le document ajouté n'existe pas déjà (dans le cas d'un écrasement)
                if(Document::where('doc_name', $validatedData['title'])->first() == null){
                    //Enregistrement des informations du document dans la BD
                    $newDocument = new Document();
                    $newDocument->doc_name = $validatedData['title'];
                    $newDocument->doc_path = $validatedData['folder'];
                    $newDocument->save();
                }

                $selectedDoc = Document::where('doc_name', $validatedData['title'])->first();

                //Attibution du document à l'article
                $NewdocumentArticle = new DocumentArticle();
                $NewdocumentArticle->art_id = $art_id;
                $NewdocumentArticle->doc_id = $selectedDoc->doc_id;
                $NewdocumentArticle->save();

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
        else{
            try {
                // Valider les champs de la requête
                $validatedData = $request->validate([
                    'articleId' => 'required|string',
                    'fileId' => 'required|string',
                ]);

                $art_id = $request->input('articleId');
                $doc_id = $request->input('fileId');

                //attribution du document
                if(DocumentArticle::where('art_id', $validatedData['articleId'])->where('doc_id', $validatedData['fileId'])->first() == null){ //Vérification si l'user met par mégarde deux fois le même doc
                    $NewdocumentArticle = new DocumentArticle();
                    $NewdocumentArticle->art_id = $art_id;
                    $NewdocumentArticle->doc_id = $doc_id;
                    $NewdocumentArticle->save();
                }

                return response()->json([
                    'status' => 200,
                    'message' => 'Fichier attribué avec succès.',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Une erreur s\'est produite lors de l\'attribution du fichier.',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
    }
}

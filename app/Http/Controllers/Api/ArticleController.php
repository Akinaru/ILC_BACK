<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ArticleResource;
use Illuminate\Support\Facades\DB;
use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentArticle;

class ArticleController extends Controller
{
    public function index(){
        $articles = Article::orderBy('art_pin', 'desc')
                            ->orderBy('art_creationdate', 'desc')
                            ->get();

        $articleCollection = ArticleResource::collection($articles);

        return response()->json([
            'articles' => $articleCollection,
            'count' => $articleCollection->count(),
        ]);
    }

    public function getById($id)
    {
        $succes = Article::findOrFail($id);
        return new ArticleResource($succes);
    }

    public function store(Request $request)
    {
        try {


            $validatedData = $request->validate([
                'art_title' => 'required|string',
                'art_description' => 'required|string',
                'art_datesortie' => 'nullable|date',
                'art_pin' => 'boolean',
            ]);
    

            $article = new Article();
            $article->art_title = $validatedData['art_title'];
            $article->art_description = $validatedData['art_description'];
            $article->art_pin = $validatedData['art_pin'];
            if (isset($validatedData['art_datesortie'])) {
                $article->art_datesortie = $validatedData['art_datesortie'];
            }else{
                $article->art_datesortie = DB::raw('NOW()');
            }
            
            $article->save();

    
            return response()->json(['status' => 201, 'message' => 'Article ajouté avec succès', 'article' => $article]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'article.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function deleteById($id)
    {
        $article = Article::find($id);
    
        if (!$article) {
            return response()->json(['message' => 'Article non trouvé.'], 404);
        }

        // Supprime les liens entre l'article et ses documents s'il en possède
        if($article->documents){
            $documentsArticle = DocumentArticle::where('art_id', $id)
            ->get();
        
            foreach($documentsArticle as $doc){
                $doc->delete();
            }
        }
    
        // Supprimer l'image du stockage si elle existe
        if ($article->art_image) {
            $imagePath = storage_path('app/' . $article->art_image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    
        $article->delete();
    
        return response()->json(['status' => 202, 'message' => 'Article supprimé avec succès.']);
    }

    public function unlinkDocuments($id)
    {
        $article = Article::find($id);
    
        if (!$article) {
            return response()->json(['message' => 'Article non trouvé.'], 404);
        }

        // Supprime les liens entre l'article et ses documents s'il en possède
        if($article->documents){
            $documentsArticle = DocumentArticle::where('art_id', $id)
            ->get();
        
            foreach($documentsArticle as $doc){
                $doc->delete();
            }
        }
    
        return response()->json(['status' => 202, 'message' => 'DocumentArticles supprimés.']);
    }

    public function put(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'art_id' => 'required|integer',
                'art_title' => 'required|string',
                'art_description' => 'required|string',
                'art_datesortie' => 'nullable|date',
                'art_pin' => 'boolean'
            ]);
            $id = $validatedData['art_id'];

            $article = Article::find($id);
            $article->art_title = $validatedData['art_title'];
            $article->art_description = $validatedData['art_description'];
            $article->art_pin = $validatedData['art_pin'];
            $article->art_lastmodif = DB::raw('NOW()');

            if (isset($validatedData['art_datesortie'])) {
                $article->art_datesortie = $validatedData['art_datesortie'];
            }else{
                 $article->art_datesortie = DB::raw('NOW()');

             }
            $article->save();
    
    
            return response()->json(['status' => 200, 'message' => 'Article modifié avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification de l\'article.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getImageById($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'status' => '404',
                'error' => 'Article non trouvé.',
                'message' => 'L\'article demandé n\'existe pas.'
            ]);
        }

        $imagePath = $article->art_image;

        if (!Storage::disk('local')->exists($imagePath)) {
            return response()->json([
                'status' => '404',
                'error' => 'Image non trouvée.',
                'message' => 'L\'image demandée n\'existe pas.'
            ]);
        }

        return response()->file(storage_path('app/'.$imagePath));
    }

}

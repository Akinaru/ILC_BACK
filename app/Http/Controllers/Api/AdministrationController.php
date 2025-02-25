<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AdministrationResource;
use App\Models\Administration;

class AdministrationController extends Controller
{
    public function index()
    {
        $admin = Administration::find(1);

        if (!$admin) {
            return response()->json(['message' => 'Administration introuvable.'], 404);
        }
        return response()->json($admin);
    }

    public function changeDateLimite(Request $request)
    {
        $validatedData = $request->validate([
            'adm_datelimite' => 'required|date',
        ]);

        $admin = Administration::find(1);
        if (!$admin) {
            $admin = new Administration();
            $admin->adm_id = 1;
            
        }

        $admin->adm_datelimite = $validatedData['adm_datelimite'];
        $admin->save();
        return response()->json(['status'=> 200 ,'message' => 'Date limite modifiée à '.$admin->adm_datelimite.'.']);
    }
    

    public function changeArbitrageStatus(Request $request)
    {
        $validatedData = $request->validate([
            'adm_arbitragetemporaire' => 'required|boolean',
        ]);
    
        $admin = Administration::find(1);
        if (!$admin) {
            $admin = new Administration();
            $admin->adm_id = 1;
        }
    
        $admin->adm_arbitragetemporaire = $validatedData['adm_arbitragetemporaire'];
        $admin->save();
    
        $statusText = $admin->adm_arbitragetemporaire ? 'Temporaire' : 'Définitif';
        return response()->json([
            'status' => 200,
            'message' => 'Status de l\'arbitrage changé à : ' . $statusText
        ]);
    }

    public function backup()
    {
        try {
            // Chemin où stocker la sauvegarde
            $backupPath = '/var/www/html/ilc/BACK/bdd_backup/';
            $filename = 'ilc_backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
            $fullPath = $backupPath . $filename;
            
            // Configuration de la base de données depuis .env
            $dbUsername = config('database.connections.mysql.username');
            $dbPassword = config('database.connections.mysql.password');
            $dbName = config('database.connections.mysql.database');
            
            // Créer la commande mysqldump
            $command = "mysqldump --user={$dbUsername} --password={$dbPassword} {$dbName} > {$fullPath}";
            
            // Exécuter la commande shell
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(60 * 5); // 5 minutes timeout
            $process->run();
            
            // Vérifier si la commande a réussi
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            // Créer également un fichier de sauvegarde le plus récent
            $latestPath = $backupPath . 'ilc_backup_latest.sql';
            copy($fullPath, $latestPath);
            
            // Journaliser l'événement
            Log::info('Database backup created successfully at: ' . $fullPath);
            
            return response()->json([
                'success' => true,
                'message' => 'Sauvegarde de la base de données créée avec succès',
                'file' => $filename
            ]);
            
        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Échec de la sauvegarde de la base de données: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Méthode optionnelle pour télécharger la dernière sauvegarde
    public function downloadLatest()
    {
        $path = '/var/www/html/ilc/BACK/ilc_backup_latest.sql';
        
        if (file_exists($path)) {
            return response()->download($path);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Aucune sauvegarde disponible'
        ], 404);
    }
}

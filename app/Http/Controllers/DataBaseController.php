<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DataBaseController extends Controller
{
    public function resetAll(Request $request)
    {
        // Vérifier si le mot de passe est correct
        $password = $request->input('password');

        $checkPasswordResult = $this->checkPassword($password);
        if ($checkPasswordResult !== null) {
            return $checkPasswordResult;
        }

        try {
            Schema::disableForeignKeyConstraints();

            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = reset($table);

                Schema::dropIfExists($tableName);
            }

            Artisan::call('migrate:fresh');
            Schema::enableForeignKeyConstraints();

            return response()->json([
                'message' => 'La base de données a été réinitialisée.',
                'tables' => $tables
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression des tables.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetTable(Request $request, $tableName)
    {
        // Vérifier si le mot de passe est correct
        $password = $request->input('password');

        $checkPasswordResult = $this->checkPassword($password);
        if ($checkPasswordResult !== null) {
            return $checkPasswordResult;
        }

        try {
            Schema::disableForeignKeyConstraints();

            DB::table($tableName)->truncate();

            Schema::enableForeignKeyConstraints();

            return response()->json([
                'message' => 'Vous avez réinitialisé la table.',
                'table' => $tableName
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression de la table.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function checkPassword($password)
    {
        if ($password !== 'd') {
            return response()->json([
                'error' => 'Mot de passe incorrect.',
            ], 403);
        }

        return null;
    }
}

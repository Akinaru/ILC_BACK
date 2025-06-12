<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class AccountCleanup extends Command
{
    protected $signature = 'accounts:cleanup';
    protected $description = 'Anonymise après 3 ans et supprime après 10 ans les comptes sans accès';

    public function handle()
    {
        date_default_timezone_set('Europe/Paris');

        $now = Carbon::now('Europe/Paris');
        $anonymizeThreshold = $now->copy()->subYears(3);
        $deleteThreshold = $now->copy()->subYears(10);

        // Anonymisation
        $accountsToAnonymize = DB::table('t_e_account_acc as acc')
            ->leftJoin('t_e_access_acs as acs', 'acc.acc_id', '=', 'acs.acc_id')
            ->whereNull('acs.acc_id')
            ->where('acc.acc_dateinscription', '<=', $anonymizeThreshold)
            ->where('acc.acc_fullname', '!=', 'Compte anonymisé')
            ->select('acc.acc_id', 'acc.acc_fullname', 'acc.acc_dateinscription')
            ->get();

        $anonymizedCount = 0;
        if ($accountsToAnonymize->isNotEmpty()) {
            foreach ($accountsToAnonymize as $account) {
                DB::table('t_e_account_acc')
                    ->where('acc_id', $account->acc_id)
                    ->update([
                        'acc_fullname' => 'Compte anonymisé',
                        'acc_mail' => 'anonyme_' . $account->acc_id . '@ilc.local',
                        'acc_studentnum' => null,
                    ]);

                DB::table('account_cleanup_logs')->insert([
                    'acc_id' => $account->acc_id,
                    'action_type' => 'anonymize',
                    'action_status' => 'executed',
                    'log_message' => 'Compte anonymisé',
                    'executed_at' => now('Europe/Paris'),
                ]);

                $anonymizedCount++;
            }
        }

        // Suppression
        $accountsToDelete = DB::table('t_e_account_acc as acc')
            ->leftJoin('t_e_access_acs as acs', 'acc.acc_id', '=', 'acs.acc_id')
            ->whereNull('acs.acc_id')
            ->where('acc.acc_fullname', '=', 'Compte anonymisé')
            ->where('acc.acc_dateinscription', '<=', $deleteThreshold)
            ->select('acc.acc_id')
            ->get();

        $deletedCount = 0;
        foreach ($accountsToDelete as $account) {
            DB::table('account_cleanup_logs')->insert([
                'acc_id' => $account->acc_id,
                'action_type' => 'delete',
                'action_status' => 'executed',
                'log_message' => 'Compte supprimé',
                'executed_at' => now('Europe/Paris'),
            ]);

            DB::table('t_e_account_acc')
                ->where('acc_id', $account->acc_id)
                ->delete();

            $deletedCount++;
        }

        $this->line("────────────── Résumé global ──────────────");
        $this->line("📅 " . now('Europe/Paris')->format('Y-m-d H:i:s'));
        $this->line("✔ Anonymisés : {$anonymizedCount}");
        $this->line("🗑️ Supprimés : {$deletedCount}");
        $this->line("────────────── Fin du résumé ──────────────");

        // Log fichier
        $logLine = now('Europe/Paris')->format('Y-m-d H:i:s') . " | Cleanup run: {$anonymizedCount} anonymised, {$deletedCount} deleted" . PHP_EOL;
        File::append(storage_path('logs/accounts_cleanup.log'), $logLine);
    }
}

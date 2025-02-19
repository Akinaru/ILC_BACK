<?php

namespace App\Exports;

use App\Models\Account;
use App\Models\Agreement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AccountExport implements FromCollection, WithHeadings, WithMapping
{
    protected $accounts;

    public function __construct(array $accounts)
    {
        $this->accounts = $accounts;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Vérifier si la requête contient un token valide
        $token = request()->query('token');
        if (!$token || !auth('sanctum')->user()) {
            throw new \Exception("Token invalide ou manquant");
        }
    
        $ids = $this->accounts;
    
        if (!is_array($ids)) {
            throw new \Exception("Expected an array of IDs, received something else.");
        }
    
        return Account::whereDoesntHave('access')
            ->whereIn('acc_id', $ids)
            ->with(['department', 'wishes', 'arbitrage.agreement'])
            ->get();
    }

    /**
     * @param mixed $account
     * @return array
     */
    public function map($account): array
    {
        // Vérifier si l'objet 'wishes' existe avant d'accéder aux champs, sinon utiliser null
        $wishesFormatted = [];
        $wishIds = [
            $account->wishes?->wsha_one ?? null,
            $account->wishes?->wsha_two ?? null,
            $account->wishes?->wsha_three ?? null,
            $account->wishes?->wsha_four ?? null,
            $account->wishes?->wsha_five ?? null,
            $account->wishes?->wsha_six ?? null
        ];
    
        // Récupérer tous les Agreements associés aux voeux (en une seule requête)
        $agreements = Agreement::whereIn('agree_id', array_filter($wishIds))->get()->keyBy('agree_id');
    
        // Formater les voeux
        foreach ($wishIds as $wishId) {
            if ($wishId !== null && isset($agreements[$wishId])) {
                $agreement = $agreements[$wishId];
                $formattedWish = sprintf(
                    "(%s) %s - %s [%s]",
                    $agreement->partnercountry?->parco_name ?? 'Aucun',
                    $agreement->university?->univ_name ?? 'Aucune',
                    $agreement->university?->univ_city ?? 'Aucune',
                    $agreement->isced?->isc_code ?? 'Inconnu'
                );
            } else {
                $formattedWish = 'Aucun';
            }
    
            $wishesFormatted[] = $formattedWish;
        }

        // Formater la destination finale dans le même format que les vœux
        $finalDestination = 'Aucune';
        if ($account->arbitrage && $account->arbitrage->agreement) {
            $finalDestination = sprintf(
                "(%s) %s - %s [%s]",
                $account->arbitrage->agreement->partnercountry?->parco_name ?? 'Aucun',
                $account->arbitrage->agreement->university?->univ_name ?? 'Aucune',
                $account->arbitrage->agreement->university?->univ_city ?? 'Aucune',
                $account->arbitrage->agreement->isced?->isc_code ?? 'Inconnu'
            );
        }
    
        return [
            'ID' => $account->acc_id,
            'Nom Complet' => $account->acc_fullname ?? '',
            'Numéro Étudiant' => $account->acc_studentnum ?? 'Inconnu',
            'TOEIC' => $account->acc_toeic ?? 'Inconnu',
            'Email' => $account->acc_mail ?? 'Inconnu',
            'Département' => $account->department->dept_shortname ?? 'Aucun',
            'Déstination finale' => $finalDestination,
            'Voeu 1' => $wishesFormatted[0],
            'Voeu 2' => $wishesFormatted[1],
            'Voeu 3' => $wishesFormatted[2],
            'Voeu 4' => $wishesFormatted[3],
            'Voeu 5' => $wishesFormatted[4],
            'Voeu 6' => $wishesFormatted[5],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Login',
            'Nom complet',
            'Numéro étudiant',
            'TOEIC',
            'Email',
            'Nom du département',
            'Déstination finale',
            'Voeu 1',
            'Voeu 2',
            'Voeu 3',
            'Voeu 4',
            'Voeu 5',
            'Voeu 6',
        ];
    }
}
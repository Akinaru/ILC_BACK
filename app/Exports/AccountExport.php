<?php

namespace App\Exports;

use App\Models\Account;
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
        // Extraire les IDs des objets (cette partie est correcte)
        $ids = $this->accounts;
    
        // S'assurer que $ids est un tableau de chaînes
        if (!is_array($ids)) {
            throw new \Exception("Expected an array of IDs, received something else.");
        }
    
        // Retourner les comptes où 'access' est null et où l'ID est dans la liste donnée
        return Account::whereDoesntHave('access')
            ->whereIn('acc_id', $ids)
            ->with('department')
            ->get();
    }

    /**
     * @param mixed $account
     * @return array
     */
    public function map($account): array
    {
        return [
            'ID' => $account->acc_id,
            'Nom Complet' => $account->acc_fullname ? $account->acc_fullname : '',
            'Numéro Étudiant' => $account->acc_studentnum ? $account->acc_studentnum : 'Inconnu',
            'TOEIC' => $account->acc_toeic ? $account->acc_toeic : 'Inconnu',
            'Email' => $account->acc_mail ?  $account->acc_mail : 'Inconnu',
            'Département' => $account->department ? $account->department->dept_shortname : 'Aucun',
            'Déstination finale' => $account->arbitrage && $account->arbitrage->agreement && $account->arbitrage->agreement->university
            ? $account->arbitrage->agreement->university->univ_name
            : 'Aucune',
            
            'Pays déstination' => $account->arbitrage && $account->arbitrage->agreement && $account->arbitrage->agreement->partnercountry
            ? $account->arbitrage->agreement->partnercountry->parco_name
            : 'Aucun',
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
            'Pays déstination',
        ];
    }
}
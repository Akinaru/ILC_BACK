<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MobileAppController extends Controller
{
    public function exportForMobileApp(){
        $query = DB::statement('CREATE OR REPLACE VIEW view_accounts_with_info AS
            WITH
                wish_univ1 AS (SELECT DISTINCT acc_id, univ_name AS wish1 FROM t_e_wishagreement_wsha wsha
                    LEFT JOIN t_e_agreement_agree agree on wsha.wsha_one = agree.agree_id
                    LEFT JOIN t_e_university_univ univ on agree.univ_id = univ.univ_id
                    ),
                wish_univ2 AS (SELECT DISTINCT acc_id, univ_name AS wish2 FROM t_e_wishagreement_wsha wsha
                    LEFT JOIN t_e_agreement_agree agree on wsha.wsha_two = agree.agree_id
                    LEFT JOIN t_e_university_univ univ on agree.univ_id = univ.univ_id
                    ),
                wish_univ3 AS (SELECT DISTINCT acc_id, univ_name AS wish3 FROM t_e_wishagreement_wsha wsha
                    LEFT JOIN t_e_agreement_agree agree on wsha.wsha_three = agree.agree_id
                    LEFT JOIN t_e_university_univ univ on agree.univ_id = univ.univ_id
                    ),
                wish_univ4 AS (SELECT DISTINCT acc_id, univ_name AS wish4 FROM t_e_wishagreement_wsha wsha
                    LEFT JOIN t_e_agreement_agree agree on wsha.wsha_four = agree.agree_id
                    LEFT JOIN t_e_university_univ univ on agree.univ_id = univ.univ_id
                    ),
                wish_univ5 AS (SELECT DISTINCT acc_id, univ_name AS wish5 FROM t_e_wishagreement_wsha wsha
                    LEFT JOIN t_e_agreement_agree agree on wsha.wsha_five = agree.agree_id
                    LEFT JOIN t_e_university_univ univ on agree.univ_id = univ.univ_id
                    ),
                wish_univ6 AS (SELECT DISTINCT acc_id, univ_name AS wish6 FROM t_e_wishagreement_wsha wsha
                    LEFT JOIN t_e_agreement_agree agree on wsha.wsha_six = agree.agree_id
                    LEFT JOIN t_e_university_univ univ on agree.univ_id = univ.univ_id
                    )
            SELECT acc.acc_id, acc_fullname, acc_mail, acc_tokenapplimsg, 
                dept_shortname, acs_accounttype, 
                acc_anneemobilite, acc_periodemobilite, 
                univ_name, parco_name,
                wish1, wish2, wish3, wish4, wish5, wish6
            FROM t_e_account_acc acc
            LEFT JOIN t_e_department_dept dept on acc.dept_id = dept.dept_id
            LEFT JOIN t_e_access_acs acs on acc.acc_id = acs.acc_id
            LEFT JOIN t_e_arbitrage_arb arb on acc.acc_id = arb.acc_id
            LEFT JOIN t_e_agreement_agree agree on arb.agree_id = agree.agree_id
            LEFT JOIN t_e_university_univ univ on agree.univ_id = univ.univ_id
            LEFT JOIN t_e_partnercountry_parco parco on univ.parco_id = parco.parco_id
            LEFT JOIN wish_univ1 w1 on acc.acc_id = w1.acc_id
            LEFT JOIN wish_univ2 w2 on acc.acc_id = w2.acc_id
            LEFT JOIN wish_univ3 w3 on acc.acc_id = w3.acc_id
            LEFT JOIN wish_univ4 w4 on acc.acc_id = w4.acc_id
            LEFT JOIN wish_univ5 w5 on acc.acc_id = w5.acc_id
            LEFT JOIN wish_univ6 w6 on acc.acc_id = w6.acc_id;'
        );

        $view = DB::table('view_accounts_with_info')->orderBy('acc_id')->get();
        $response = Http::post('http://51.83.36.122:7473/api/account/store', [
            'data' => $view,
        ]);
        return response()->json([
            'message' => $response["message"],
            'savedaccounts' => $response["data"]
        ]);

        /* Pour vérifier l'intégrité des données envoyées */
        //return $view;
    }

    public function bulkAssignToken(){
        try {
            $accounts = Account::all();

            foreach($accounts as $account){
                if($account->acc_tokenapplimsg == null){
                    $account->acc_tokenapplimsg = Str::upper(Str::random(8));
                    $account->save();
                }
            }

            return response()->json([
                'message' => 'Mots de passe assignés'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors l\'attibution des mots de passe.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
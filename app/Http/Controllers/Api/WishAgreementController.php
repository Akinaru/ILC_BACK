<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\WishAgreementResource;
use App\Models\WishAgreement;

class WishAgreementController extends Controller
{

    public function getByLogin($login)
    {
        $succes = WishAgreement::find($login);
        if(!$succes){
            return response()->json([
                'count' => 0,
            ]);
        }
        $wishAgreement = new WishAgreementResource($succes);

        $count = 0;
        $count += $wishAgreement->wsha_one ? 1 : 0;
        $count += $wishAgreement->wsha_two ? 1 : 0;
        $count += $wishAgreement->wsha_three ? 1 : 0;
        $count += $wishAgreement->wsha_four ? 1 : 0;
        $count += $wishAgreement->wsha_five ? 1 : 0;
        $count += $wishAgreement->wsha_six ? 1 : 0;

        return response()->json([
            'wishes' => $wishAgreement,
            'count' => $count,
        ]);
    } 

    public function save(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'acc_id' => 'required|string',
                'wsha_one' => 'integer|nullable',
                'wsha_two' => 'integer|nullable',
                'wsha_three' => 'integer|nullable',
                'wsha_four' => 'integer|nullable',
                'wsha_five' => 'integer|nullable',
                'wsha_six' => 'integer|nullable',
            ]);
    
            $acc_id = $validatedData['acc_id'];
            $wish = WishAgreement::find($acc_id);
    
            if (!$wish) {
                $newWish = new WishAgreement();
                $newWish->acc_id = $acc_id;
                $newWish->wsha_one = $validatedData['wsha_one'];
                $newWish->wsha_two = $validatedData['wsha_two'];
                $newWish->wsha_three = $validatedData['wsha_three'];
                $newWish->wsha_four = $validatedData['wsha_four'];
                $newWish->wsha_five = $validatedData['wsha_five'];
                $newWish->wsha_six = $validatedData['wsha_six'];
                $newWish->save();
            } else {
                $wish->wsha_one = $validatedData['wsha_one'];
                $wish->wsha_two = $validatedData['wsha_two'];
                $wish->wsha_three = $validatedData['wsha_three'];
                $wish->wsha_four = $validatedData['wsha_four'];
                $wish->wsha_five = $validatedData['wsha_five'];
                $wish->wsha_six = $validatedData['wsha_six'];
                $wish->save();
            }
    
            if ($wish && !$wish->wsha_one && !$wish->wsha_two && !$wish->wsha_three && !$wish->wsha_four && !$wish->wsha_five && !$wish->wsha_six) {
                $wish->delete();
            }
    
            return response()->json(['status' => 201, 'save' => 'Sauvegarde automatique', 'message' => 'Vos voeux ont été sauvegardés avec succès.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la sauvegarde des voeux.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

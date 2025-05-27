<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AgreementResource;
use App\Models\Account;
use App\Models\Agreement;
use App\Models\Article;
use App\Models\Event;
use App\Models\Admininistration;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $wishes = $this->resource->wishes;

        // Initialiser le tableau des souhaits et le nombre de souhaits non nuls à 0
        $wishesArray = [];
        $wishCount = 0;

        if ($wishes) {
            $wishCount = collect([
                $wishes->wsha_one,
                $wishes->wsha_two,
                $wishes->wsha_three,
                $wishes->wsha_four,
                $wishes->wsha_five,
                $wishes->wsha_six,
            ])->filter(function ($wish) {
                return !is_null($wish);
            })->count();

            $wishesArray = $wishes->toArray();
        }

        $wishesArray['count'] = $wishCount;

        $roleInfo = $this->getRoleInfo();
        $docCount = $this->getFileCount();

        $accessResponse = null;
        if ($this->resource->access) {
            $accessResponse = [
                'access' => $this->resource->access,
                'count' => 1,
            ];
        } else {
            $accessResponse = [
                'count' => 0,
            ];
        }

        // Favoris
        $favoris = $this->resource->favoris;
        $favorisData = [
            'count' => $favoris->count(),
            'items' => \App\Http\Resources\FavorisResource::collection($favoris),
        ];

        // Métriques si accès de niveau 1
        $adminMetrics = null;
        if ($this->resource->hasRole('admin')) {
            $studentsCount = Account::whereDoesntHave('access')
                ->where('acc_arbitragefait', false)
                ->where('acc_validateacc', true)
                ->count();

            $agreementsCount = Agreement::count();
            $articlesCount = Article::count();
            $evenementsCount = Event::count();

            $adminMetrics = [
                'students' => $studentsCount,
                'agreements' => $agreementsCount,
                'articles' => $articlesCount,
                'evenements' => $evenementsCount,
            ];
        }

        $periode = $this->resource->acc_periodemobilite;
        $admin = \App\Models\Administration::find(1);
        $deadline = $admin
            ? match ($periode) {
                1 => $admin->adm_datelimite_automne,
                2 => $admin->adm_datelimite_printemps,
                default => $admin->adm_datelimite_automne,
            }
            : null;

        return [
            'acc_id' => $this->resource->acc_id,
            'acc_fullname' => $this->resource->acc_fullname,
            'acc_lastlogin' => $this->resource->acc_lastlogin,
            'acc_studentnum' => $this->resource->acc_studentnum,
            'acc_amenagement' => $this->resource->acc_amenagement,
            'acc_amenagemendesc' => $this->resource->acc_amenagementdesc,
            'acc_anneemobilite' => $this->resource->acc_anneemobilite,
            'acc_periodemobilite' => $this->resource->acc_periodemobilite,
            'acc_temoignage' => $this->resource->acc_temoignage,
            'acc_validechoixcours' => (bool) $this->resource->acc_validechoixcours,
            'acc_toeic' => $this->resource->acc_toeic,
            'acc_mail' => $this->resource->acc_mail,
            'acc_parcours' => $this->resource->acc_parcours,
            'acc_validateacc' => (bool) $this->resource->acc_validateacc,
            'acc_arbitragefait' => (bool) $this->resource->acc_arbitragefait,
            'acc_ancienetu' => (bool) $this->resource->acc_ancienetu,
            'department' => $this->resource->department,
            'acc_json_department' => $this->resource->acc_json_department,
            'acc_json_agreement' => $this->resource->acc_json_agreement,
            'access' => $accessResponse,
            'role' => $roleInfo,
            'datelimite' => $deadline,
            'documents' => $docCount,
            'destination' => new AgreementResource($this->resource->destination),
            'arbitrage' => $this->resource->arbitrage 
                ? [
                    ...(new AgreementResource($this->resource->arbitrage->agreement))->toArray($request),
                    'status' => \App\Models\Administration::find(1)->adm_arbitragetemporaire ?? false
                ] 
                : null,
            'wishes' => $wishesArray,
            'favoris' => $favorisData,
            'metrics' => $adminMetrics,
        ];
    }
}

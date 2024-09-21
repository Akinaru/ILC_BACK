<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PartnerCountryResource;
use App\Models\PartnerCountry;


class PartnerCountryController extends Controller
{
    public function index(){
        $partnerCountries = PartnerCountry::orderBy('parco_name')->get();
        return PartnerCountryResource::collection($partnerCountries);
    }

    public function getById($id)
    {
        $succes = PartnerCountry::findOrFail($id);
        return new PartnerCountryResource($succes);
    }
}
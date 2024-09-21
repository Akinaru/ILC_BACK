<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerCountry extends Model
{
    use HasFactory;
    protected $table = 't_e_partnercountry_parco';

    protected $primaryKey = 'parco_id';
    public $timestamps = false;

    protected $fillable = [
        'parco_id',
        'parco_name',
        'parco_code'
    ];
}

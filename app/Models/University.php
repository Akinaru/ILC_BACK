<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;
    protected $table = 't_e_university_univ';

    protected $primaryKey = 'univ_id';
    public $timestamps = false;

    protected $fillable = [
        'univ_id',
        'univ_name',
        'univ_city',
        'univ_mail',
        'univ_adress',
        'parco_id'
    ];

    public function partnercountry()
    {
        return $this->hasOne(PartnerCountry::class, 'parco_id', 'parco_id');
    }  
}

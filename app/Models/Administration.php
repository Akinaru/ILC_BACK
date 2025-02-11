<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administration extends Model
{
    use HasFactory;
   protected $table = 't_e_administration_adm';
   protected $primaryKey = 'adm_id';
   protected $fillable = ['adm_id', 'adm_datelimite', 'adm_arbitragetemporaire'];
   public $timestamps = false;

}

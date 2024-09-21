<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Isced extends Model
{
    use HasFactory;
    protected $table = 't_e_isced_isc';

    protected $primaryKey = 'isc_id';
    public $timestamps = false;

    protected $fillable = [
        'isc_id',
        'isc_code',
        'isc_name',
    ];
}

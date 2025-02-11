<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Favoris extends Model
{
    use HasFactory;

    protected $table = 't_e_favoris_fav';

    protected $primaryKey = 'fav_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'deptagree_id',
        'dept_id',
        'agree_id',
        'deptagree_valide',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agree_id', 'agree_id');
    }
}

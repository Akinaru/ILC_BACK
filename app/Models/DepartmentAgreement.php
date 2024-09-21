<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentAgreement extends Model
{
    use HasFactory;

    protected $table = 't_j_deptagreement_deptagree';

    protected $primaryKey = 'deptagree_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'deptagree_id',
        'agree_id',
        'acc_id',
        'arb_pos'
    ];
}

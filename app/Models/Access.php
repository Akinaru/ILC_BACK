<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    use HasFactory;
    protected $table = 't_e_access_acs';

    protected $primaryKey = 'acc_id';
    public $timestamps = false;
    public $incrementing = false; 

    protected $fillable = [
        'acc_id',
        'acs_accounttype',
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'acc_id', 'acc_id');
    } 


}

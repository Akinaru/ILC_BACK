<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arbitrage extends Model
{
    use HasFactory;
    protected $table = 't_e_arbitrage_arb';

    protected $primaryKey = 'acc_id';
    public $timestamps = false;
    public $incrementing = false; 

    protected $fillable = [
        'acc_id',
        'agree_id',
        'arb_pos',
    ];


    public function account()
    {
        return $this->hasOne(Account::class, 'acc_id', 'acc_id');
    } 

    public function agreement()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'agree_id');
    } 
}

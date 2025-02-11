<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptedAccount extends Model
{
    use HasFactory;

    protected $table = 't_e_acceptedaccount_acptacc';

    protected $primaryKey = 'acc_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'acc_id',
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'acc_id', 'acc_id');
    }
    
    public function department()
    {
        if ($this->account) {
            return $this->account->belongsTo(Department::class, 'dept_id', 'dept_id');
        } else {
            // Si l'account n'existe pas, retournez une relation vide
            return $this->belongsTo(Department::class, 'dept_id', 'dept_id')->where('1', '=', '0');
        }
    }
}

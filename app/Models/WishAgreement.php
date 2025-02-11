<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishAgreement extends Model
{
    use HasFactory;

    protected $table = 't_e_wishagreement_wsha';

    protected $primaryKey = 'acc_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'acc_id',
        'wsha_one',
        'wsha_two',
        'wsha_three',
        'wsha_four',
        'wsha_five',
        'wsha_six',
    ];

    
    public function agree_one()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'wsha_one');
    }  

    public function agree_two()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'wsha_two');
    }  

    public function agree_three()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'wsha_three');
    }  

    public function agree_four()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'wsha_four');
    }  

    public function agree_five()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'wsha_five');
    }  

    public function agree_six()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'wsha_six');
    }  
}

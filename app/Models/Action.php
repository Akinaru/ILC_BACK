<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;
    protected $table = 't_e_action_act';

    protected $primaryKey = 'act_id';
    public $timestamps = false;

    protected $fillable = [
        'act_id',
        'act_description',
        'act_date',
        'act_type',
        
        'acc_id',
        'dept_id',
        'agree_id',
        'art_id',
        'evt_id',
        'access',
        'admin'
    ];

    public function account()
    {
        return $this->hasOne(Account::class, 'acc_id', 'acc_id');
    } 

    public function department()
    {
        return $this->hasOne(Department::class, 'dept_id', 'dept_id');
    } 

    public function agreement()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'agree_id');
    } 

    public function article()
    {
        return $this->hasOne(Article::class, 'art_id', 'art_id');
    } 

    public function event()
    {
        return $this->hasOne(Event::class, 'evt_id', 'evt_id');
    } 

    public function admin()
    {
        return $this->hasOne(Event::class, 'admin', 'adm_id');
    } 
}

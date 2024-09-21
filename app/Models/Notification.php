<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 't_e_notification_not';

    protected $primaryKey = 'not_id';
    public $timestamps = false;

    protected $fillable = [
        'not_id',
        'not_envoyeur',
        'not_receveur',
        'not_message',
        'not_date',
        'not_vue',
    ];
    public function envoyeur()
    {
        return $this->belongsTo(Account::class, 'not_envoyeur', 'acc_id');
    } 
    public function receveur()
    {
        return $this->belongsTo(Account::class, 'not_receveur', 'acc_id');
    } 
}

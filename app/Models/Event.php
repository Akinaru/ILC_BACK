<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table = 't_e_event_evt';

    protected $primaryKey = 'evt_id';
    public $timestamps = false;

    protected $fillable = [
        'evt_id',
        'evt_name',
        'evt_description',
        'evt_datetime',
        'evthm_id',
    ];

    public function theme()
    {
        return $this->hasOne(EventTheme::class, 'evthm_id', 'evthm_id');
    } 
}

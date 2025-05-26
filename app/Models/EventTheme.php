<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTheme extends Model
{
    use HasFactory;
    protected $table = 't_e_eventtheme_evthm';

    protected $primaryKey = 'evthm_id';
    public $timestamps = false;

    protected $fillable = [
        'evthm_id',
        'evthm_name',
        'evthm_color',
    ];
}

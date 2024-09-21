<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;
    protected $table = 't_e_component_comp';

    protected $primaryKey = 'comp_id';
    public $timestamps = false;

    protected $fillable = [
        'comp_id',
        'comp_name',
        'comp_shortname',
        'comp_logo',
        'comp_color',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class, 'comp_id', 'comp_id');
    }    
}

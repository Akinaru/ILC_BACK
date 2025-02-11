<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 't_e_department_dept';

    protected $primaryKey = 'dept_id';
    public $timestamps = false;

    protected $fillable = [
        'dept_id',
        'dept_name',
        'dept_shortname',
        'dept_color',
        'comp_id',
    
    ];

    public function agreements()
    {
        return $this->belongsToMany(Agreement::class, 't_j_deptagreement_deptagree', 'dept_id', 'agree_id');
    }


    public function component()
    {
        return $this->hasOne(Component::class, 'comp_id', 'comp_id');
    } 


}

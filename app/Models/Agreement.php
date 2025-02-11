<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\DepartmentResource;

class Agreement extends Model
{
    use HasFactory;
    protected $table = 't_e_agreement_agree';

    protected $primaryKey = 'agree_id';
    public $timestamps = false;

    protected $fillable = [
        'agree_id',
        'isc_id',
        'univ_id',
        'comp_id',
        'agree_lien',
        'agree_description',
        'agree_nbplace',
        'agree_typeaccord',
        'agree_note',
    ];
    public function departments()
    {
        return $this->belongsToMany(Department::class, 't_j_deptagreement_deptagree', 'agree_id', 'dept_id')
                    ->withPivot('deptagree_valide')
                    ->orderBy('dept_name');
    }

    public function departmentsShortName()
    {
        return $this->belongsToMany(Department::class, 't_j_deptagreement_deptagree', 'agree_id', 'dept_id')
                    ->withPivot('deptagree_valide')
                    ->orderBy('dept_shortname');
    }

    public function isced()
    {
        return $this->hasOne(Isced::class, 'isc_id', 'isc_id');
    } 

    public function university()
    {
        return $this->hasOne(University::class, 'univ_id', 'univ_id');
    } 

    public function component()
    {
        return $this->hasOne(Component::class, 'comp_id', 'comp_id');
    } 

    public function partnercountry()
    {
        return $this->university ? $this->university->partnercountry() : null;
    }

}

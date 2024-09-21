<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $table = 't_e_account_acc';

    protected $primaryKey = 'acc_id';
    public $timestamps = false;
    public $incrementing = false; 

    protected $fillable = [
        'acc_id',
        'acc_fullname',
        'acc_lastlogin',
        'acc_studentnum',
        'acc_validateacc',
        'acc_toeic',
        'acc_mail',
        'dept_id',
        'acc_amenagement',
        'acc_amenagementdesc',
    ];

    public function department()
    {
        return $this->hasOne(Department::class, 'dept_id', 'dept_id');
    } 

    public function access()
    {
        return $this->hasOne(Access::class, 'acc_id', 'acc_id');
    } 

    public function arbitrage()
    {
        return $this->hasOne(Arbitrage::class, 'acc_id', 'acc_id');
    } 

    public function getRoleInfo()
    {
        $access = $this->access;
    
        if ($access) {
            switch ($access->acs_accounttype) {
                case 1:
                    $role = "👑 ILC";
                    $color = '#dc2626'; // No department color for Admin
                    break;
                case 2:
                    $deptName = $this->department ? $this->department->dept_shortname : null;
                    $deptColor = $this->department ? $this->department->dept_color : 'bg-red-500';
                    $role = "⭐ " . ($deptName ? $deptName : "");
                    $color = $deptColor;
                    break;
                default:
                    $role = "Unknown";
                    $color = '#aaaaaa'; // Default color
                    break;
            }
    
            return [
                'role' => $role,
                'access_type' => $access->acs_accounttype,
                'color' => $color,
            ];
        } else {
            if ($this->department) {
                return [
                    'role' => $this->department->dept_shortname,
                    'access_type' => null,
                    'color' => $this->department->dept_color,
                ];
            } else {
                return [
                    'role' => null,
                    'access_type' => null,
                    'color' => 'bg-red-500',
                ];
            }
        }
    }

    

    public function wishes()
    {
        return $this->hasOne(WishAgreement::class, 'acc_id', 'acc_id');
    }

    public function getWishCountAttribute()
    {
        $wishes = $this->wishes;
        if (!$wishes) {
            return 0;
        }

        $count = 0;
        foreach (['wsha_one', 'wsha_two', 'wsha_three', 'wsha_four', 'wsha_five'] as $wish) {
            if (!is_null($wishes->$wish)) {
                $count++;
            }
        }

        return $count;
    }


}

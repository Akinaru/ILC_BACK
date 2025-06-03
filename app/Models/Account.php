<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Account extends Authenticatable 
{
    use HasApiTokens, HasFactory;
    protected $table = 't_e_account_acc';

    protected $primaryKey = 'acc_id';
    protected $keyType = 'string';
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
        'acc_parcours',
        'acc_validechoixcours',
        'dept_id',
        'acc_amenagement',
        'acc_amenagementdesc',
        'acc_ancienetuconsent',
        'acc_anneemobilite',
        'acc_periodemobilite',
        'acc_temoignage',
        'acc_arbitragefait',
        'acc_ancienetu',
        'agree_id',
        'acc_json_department',
        'acc_json_agreement',
        'acc_tokenapplimsg'
    ];

    public function favoris()
{
    return $this->hasMany(Favoris::class, 'acc_id', 'acc_id');
}


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

    public function destination()
    {
        return $this->hasOne(Agreement::class, 'agree_id', 'agree_id');
    } 

    public function hasRole($role)
    {
        return match($role) {
            'admin' => $this->access?->acs_accounttype === 1,
            'chefdept' => $this->access?->acs_accounttype === 1 || $this->access?->acs_accounttype === 2,
            default => false
        };
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

    public function getFileCount()
    {
        // Dossiers à vérifier
        $folders = ['choix_cours', 'contrat_peda', 'releve_note'];
        $login = $this->acc_id; // Utilise le login de l'utilisateur (ex: tsanevp)
        $fileSummary = [];
        $totalFiles = 0;

        // Parcourir chaque dossier
        foreach ($folders as $folder) {
            // Construire le chemin complet du répertoire
            $directoryPath = "documents/etu/{$folder}";

            // Récupérer tous les fichiers dans le répertoire
            $files = Storage::disk('private')->allFiles($directoryPath);

            // Construire le nom de fichier attendu (ex: choix_cours_tsanevp)
            $expectedFilePrefix = "{$folder}_{$login}";
            $fileFound = null;

            // Parcourir les fichiers pour trouver celui qui commence par le préfixe attendu
            foreach ($files as $file) {
                // Extraire le nom du fichier sans l'extension
                $fileWithoutExtension = pathinfo($file, PATHINFO_FILENAME);

                // Utiliser Str::startsWith pour vérifier si le fichier commence par le préfixe attendu
                if (Str::startsWith($fileWithoutExtension, $expectedFilePrefix)) {
                    $fileFound = $file; // Chemin complet du fichier
                    $totalFiles++; // Incrémenter le nombre total de fichiers trouvés
                    break; // On arrête la recherche dès qu'on trouve un fichier
                }
            }

            // Ajouter le résultat pour ce dossier au résumé
            $fileSummary[$folder] = $fileFound ? Storage::disk('private')->url($fileFound) : null;
        }

        // Ajouter le nombre total de fichiers au résumé
        $fileSummary['count'] = $totalFiles;
        $fileSummary['countmax'] = 3;

        // Retourner le résumé
        return $fileSummary;
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $table = 't_e_article_art';

    protected $primaryKey = 'art_id';
    
    //Evite de devoir ajouter updated_at et created_at dans la table
    public $timestamps = false;

    protected $fillable = [
        'art_id',
        'art_title',
        'art_description',
        'art_creationdate',
        'art_lastmodif',
        'art_pin',
        'art_image',
    ];
}



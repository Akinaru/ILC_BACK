<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    protected $table = 't_e_document_doc';

    protected $primaryKey = 'doc_id';
    
    //Evite de devoir ajouter updated_at et created_at dans la table
    public $timestamps = false;

    protected $fillable = [
        'doc_id',
        'doc_name',
        'doc_path',
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 't_j_documentarticle_docart', 'doc_id', 'art_id');
    }
}



<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentArticle extends Model
{
    use HasFactory;

    protected $table = 't_j_documentarticle_docart';

    protected $primaryKey = 'docart_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'docart_id',
        'art_id',
        'doc_id',
    ];
}

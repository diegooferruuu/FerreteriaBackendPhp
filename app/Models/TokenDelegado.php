<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TokenDelegado extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;

    protected $table = 'token_delegado';
    protected $primaryKey = 'id';
    protected $fillable = [
        'valor',
        'validez',
        'estado'
    ];
}

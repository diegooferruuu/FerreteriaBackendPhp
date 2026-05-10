<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedencia extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "procedencias";
    protected $primaryKey = 'id';
    protected $fillable = [
        'procedencia'
    ];
}

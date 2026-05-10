<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offline extends Model
{
    use HasFactory;
    protected $table = "offline";
    protected $fillable = [
        'estado',
    ];
}

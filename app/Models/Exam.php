<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'name', 'params'];

    protected $casts = [
        'params' => 'array', // JSON pour les params
    ];
}
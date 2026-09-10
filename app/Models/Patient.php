<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'dob', 
        'sex', // AJOUTEZ ICI
        'dossier_number', 
        'address', 
        'phone'
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    // Ajoutez aussi une valeur par défaut si vous voulez
    protected $attributes = [
        'sex' => 'Masculin', // Valeur par défaut optionnelle
    ];

    // Accesseurs pour formatage
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    // Relation pour résultats futurs
    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
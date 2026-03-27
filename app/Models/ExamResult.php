<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'result_id',
        'exam_id',
        'param',
        'resultat',
        'norme',
        'observation',
    ];

    // Relation vers le résultat principal
    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    // Relation vers l'examen
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}

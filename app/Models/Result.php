<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'exam_id',
        'code',
        'type_examen',
        'statut',
        'date',
        'global_observation', // ✅ observation globale
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

      public function exams()
    {
        return $this->hasManyThrough(
            Exam::class,       // modèle final
            ExamResult::class, // modèle pivot
            'result_id',       // clé étrangère sur ExamResult
            'id',              // clé primaire sur Exam
            'id',              // clé locale sur Result
            'exam_id'          // clé locale sur ExamResult
        );
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class, 'result_id');
    }
}

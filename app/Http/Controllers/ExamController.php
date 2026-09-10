<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::latest()->paginate(10);
        return view('exams.index', compact('exams'));
    }

    public function create()
    {
        $lastExam = Exam::orderByDesc('type')->first();
        $nextType = $lastExam ? ((int)$lastExam->type + 1) : 1;

        return view('exams.create', compact('nextType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|integer|unique:exams,type',
            'name' => 'required|string|max:255',
            'params' => 'nullable|array',
            'params.*.param' => 'nullable|string|max:255',
            'params.*.norme' => 'nullable|string|max:255',
        ]);

        // Nettoyer les paramètres vides avant enregistrement
        $params = $request->params ?? [];
        $cleanedParams = array_filter($params, function($param) {
            return !empty($param['param']) || !empty($param['norme']);
        });

        Exam::create([
            'type' => $request->type,
            'name' => $request->name,
            'params' => array_values($cleanedParams), // Réindexer le tableau
        ]);

        return redirect()
            ->route('exams.index')
            ->with('success', 'Examen créé : ' . $request->name);
    }

    public function show(Exam $exam)
    {
        return view('exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        return view('exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
        $request->validate([
            'type' => 'required|integer|unique:exams,type,' . $exam->id,
            'name' => 'required|string|max:255',
            'params' => 'nullable|array',
            'params.*.param' => 'nullable|string|max:255',
            'params.*.norme' => 'nullable|string|max:255',
        ]);

        // Nettoyer les paramètres vides avant enregistrement
        $params = $request->params ?? [];
        $cleanedParams = array_filter($params, function($param) {
            return !empty($param['param']) || !empty($param['norme']);
        });

        $exam->update([
            'type' => $request->type,
            'name' => $request->name,
            'params' => array_values($cleanedParams), // Réindexer le tableau
        ]);

        return redirect()
            ->route('exams.show', $exam)
            ->with('success', 'Examen mis à jour : ' . $request->name);
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()
            ->route('exams.index')
            ->with('success', 'Examen supprimé.');
    }

    /**
     * Récupérer les paramètres d'un examen (API pour JavaScript)
     */
    public function getParams(Exam $exam)
    {
        return response()->json([
            'id' => $exam->id,
            'name' => $exam->name,
            'type' => $exam->type,
            'params' => $exam->params ?? []
        ]);
    }

    /**
     * API pour récupérer tous les examens avec leurs paramètres
     */
    public function getAllExams()
    {
        $exams = Exam::select('id', 'name', 'type', 'params')->get();
        return response()->json($exams);
    }
}
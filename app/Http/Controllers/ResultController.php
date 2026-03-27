<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Result;
use App\Models\Exam;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ResultController extends Controller
{
    // Liste des résultats
    public function index()
    {
        $results = Result::with(['patient', 'examResults.exam'])->latest()->paginate(10);
        return view('resultats.index', compact('results'));
    }

    // Formulaire création
    public function create()
    {
        $patients = Patient::all();
        $exams = Exam::all();
        $lastResult = Result::latest('id')->first();
        $nextResultNumber = $lastResult ? $lastResult->id + 1 : 1;

        return view('resultats.create', compact('patients', 'exams', 'nextResultNumber'));
    }



     public function dashboard()
    {
        $patientsCount = Patient::count();
        $examsCount = Exam::count();
        $resultsTotal = Result::count();

        // Pour les derniers résultats
        $recentResults = Result::with('patient')->latest()->take(5)->get();

        // Pour le graphique résultats par mois
        $resultsByMonth = Result::selectRaw("strftime('%m/%Y', date) as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $chartLabels = $resultsByMonth->pluck('month');
        $chartData = $resultsByMonth->pluck('total');

        return view('dashboard', compact(
            'patientsCount',
            'examsCount',
            'resultsTotal',
            'recentResults',
            'chartLabels',
            'chartData'
        ));
    }

    // Stockage d'un nouveau résultat
  public function store(Request $request)
{
    $request->validate([
        'patient_id' => 'required|exists:patients,id',
        'exam_ids' => 'required|array',
        'exam_ids.*' => 'exists:exams,id',
    ]);

    // Génération code résultat auto
    $lastResult = Result::latest('id')->first();
    $nextNumber = $lastResult ? $lastResult->id + 1 : 1;
    $code = 'LAB-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    // Création du résultat principal
    $result = Result::create([
        'patient_id' => $request->patient_id,
        'code' => $code,
        'date' => now(),
        'statut' => 'pending',
        'global_observation' => $request->global_observation ?? null,
    ]);

    // Parcours des examens sélectionnés
    foreach ($request->exam_ids as $examId) {
        $exam = Exam::findOrFail($examId);
        $params = $exam->params ?? [];
        
        // Si aucun paramètre n'est défini, créer un paramètre par défaut
        if (empty($params)) {
            $params = [['param' => 'Résultat', 'norme' => '-']];
        }
        
        // Gestion spéciale pour antibiogramme et spermogramme
        if (in_array($exam->type, ['spermogramme', 'atb'])) {
            $antibiotics = $request->input("exams.$examId.antibiotics", []);
            
            foreach ($antibiotics as $index => $antibio) {
                if (!empty($antibio['name']) && !empty($antibio['result'])) {
                    ExamResult::create([
                        'result_id' => $result->id,
                        'exam_id' => $examId,
                        'param' => $antibio['name'],
                        'resultat' => $antibio['result'],
                        'norme' => '-',
                        'observation' => '-',
                    ]);
                }
            }
        } else {
            // Pour les examens normaux
            $examData = $request->input("exams.$examId", []);
            
            // Parcourir tous les paramètres de l'examen
            foreach ($params as $index => $param) {
                $userData = isset($examData[$index]) ? $examData[$index] : [];
                
                // Vérifier si un résultat a été saisi
                $resultat = $userData['resultat'] ?? '';
                
                // Créer une entrée même si le résultat est vide (pour garder tous les paramètres)
                ExamResult::create([
                    'result_id' => $result->id,
                    'exam_id' => $examId,
                    'param' => $param['param'] ?? "Paramètre " . ($index + 1),
                    'resultat' => $resultat,
                    'norme' => $param['norme'] ?? '-',
                    'observation' => $userData['observation'] ?? '-',
                ]);
            }
        }
    

    // ... (reste du code pour QR Code et PDF inchangé) ...
}

        // Crée dossier PDF si manquant
        $pdfDir = storage_path('app/public/pdf');
        if (!file_exists($pdfDir)) mkdir($pdfDir, 0755, true);

        // Génération QR Code
        $qrCode = QrCode::create("LAB-{$code} - Vérification: /verifier/{$code}");
        $writer = new PngWriter();
        $qrResult = $writer->write($qrCode);
        $qrPath = storage_path("app/public/qr/{$code}.png");
        if (!file_exists(dirname($qrPath))) mkdir(dirname($qrPath), 0755, true);
        $qrResult->saveToFile($qrPath);

        // Récupération patient
        $patient = Patient::findOrFail($request->patient_id);
        $patientAge = floor($patient->dob->diffInYears(now()));

        // Tous les examens liés au résultat pour le PDF
        $examResults = $result->examResults()->with('exam')->get()->groupBy('exam_id');

        // Génération PDF
        $pdf = Pdf::loadView('pdf.result_multi', compact('result', 'patient', 'examResults', 'qrPath', 'patientAge'));
        $pdf->save($pdfDir . '/' . $code . '.pdf');

        return redirect()->route('resultats.index')
            ->with('success', "Résultat #$code généré ! PDF : /storage/pdf/$code.pdf");
    }
}

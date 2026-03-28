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
    public function index()
    {
        $results = Result::with(['patient', 'examResults.exam'])->latest()->paginate(10);
        return view('resultats.index', compact('results'));
    }

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
        $recentResults = Result::with('patient')->latest()->take(5)->get();
        $resultsByMonth = Result::selectRaw("strftime('%m/%Y', date) as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $chartLabels = $resultsByMonth->pluck('month');
        $chartData = $resultsByMonth->pluck('total');
        return view('dashboard', compact(
            'patientsCount', 'examsCount', 'resultsTotal',
            'recentResults', 'chartLabels', 'chartData'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'exam_ids' => 'required|array',
            'exam_ids.*' => 'exists:exams,id',
        ]);

        $lastResult = Result::latest('id')->first();
        $nextNumber = $lastResult ? $lastResult->id + 1 : 1;
        $code = 'LAB-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $result = Result::create([
            'patient_id' => $request->patient_id,
            'code' => $code,
            'date' => now(),
            'statut' => 'pending',
            'global_observation' => $request->global_observation ?? null,
        ]);

        foreach ($request->exam_ids as $examId) {
            $exam = Exam::findOrFail($examId);
            $params = $exam->params ?? [];

            if (empty($params)) {
                $params = [['param' => 'Résultat', 'norme' => '-']];
            }

            if (in_array($exam->type, ['spermogramme', 'atb'])) {
                $antibiotics = $request->input("exams.$examId.antibiotics", []);
                foreach ($antibiotics as $antibio) {
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
                $examData = $request->input("exams.$examId", []);
                foreach ($params as $index => $param) {
                    $userData = isset($examData[$index]) ? $examData[$index] : [];
                    ExamResult::create([
                        'result_id' => $result->id,
                        'exam_id' => $examId,
                        'param' => $param['param'] ?? "Paramètre " . ($index + 1),
                        'resultat' => $userData['resultat'] ?? '',
                        'norme' => $param['norme'] ?? '-',
                        'observation' => $userData['observation'] ?? '-',
                    ]);
                }
            }
        }

        // Dossiers dans public/ directement (pas storage)
        $pdfDir = public_path('pdf');
        $qrDir = public_path('qr');
        if (!file_exists($pdfDir)) mkdir($pdfDir, 0755, true);
        if (!file_exists($qrDir)) mkdir($qrDir, 0755, true);

        // Génération QR Code
        $qrCode = QrCode::create("LAB-{$code} - Vérification: /verifier/{$code}");
        $writer = new PngWriter();
        $qrResult = $writer->write($qrCode);
        $qrPath = $qrDir . DIRECTORY_SEPARATOR . $code . '.png';
        $qrResult->saveToFile($qrPath);

        // Logo en base64
        $logoPath = public_path('images/logo.jpg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // QR en base64
        $qrBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($qrPath));

        $patient = Patient::findOrFail($request->patient_id);
        $patientAge = $patient->dob ? floor($patient->dob->diffInYears(now())) : 'N/A';
        $examResults = $result->examResults()->with('exam')->get()->groupBy('exam_id');

        $pdf = Pdf::loadView('pdf.result_multi', compact(
            'result', 'patient', 'examResults',
            'qrBase64', 'logoBase64', 'patientAge'
        ));
        $pdf->save($pdfDir . DIRECTORY_SEPARATOR . $code . '.pdf');

        return redirect()->route('resultats.index')
            ->with('success', "Résultat #$code généré !");
    }
}
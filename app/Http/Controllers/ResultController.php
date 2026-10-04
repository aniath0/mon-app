<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Result;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Support\ExamTemplates;
use App\Support\HematoLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ResultController extends Controller
{
    /** Marqueur (colonne norme) des lignes libres saisies dans le formulaire. */
    private const LIBRE = '__libre__';

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
            'global_observation' => 'nullable|string|max:5000',
        ]);

        // Tout est enregistré ou rien : pas de résultat à moitié créé.
        $result = DB::transaction(function () use ($request) {
            $lastResult = Result::latest('id')->first();
            $nextNumber = $lastResult ? $lastResult->id + 1 : 1;
            $code = 'LAB-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $result = Result::create([
                'patient_id' => $request->patient_id,
                'code' => $code,
                'date' => now(),
                'statut' => 'pending',
                'global_observation' => $request->input('global_observation'),
            ]);

            foreach ($request->exam_ids as $examId) {
                $exam = Exam::findOrFail($examId);
                $data = $request->input("exams.$examId", []);
                $this->saveExam($result, $exam, is_array($data) ? $data : []);
            }

            return $result;
        });

        $code = $result->code;

        $pdfDir = public_path('pdf');
        $qrDir = public_path('qr');
        if (!file_exists($pdfDir)) mkdir($pdfDir, 0755, true);
        if (!file_exists($qrDir)) mkdir($qrDir, 0755, true);

        // Le QR contient directement l'adresse de vérification.
        $qrCode = QrCode::create(url("/verifier/{$code}"));
        $writer = new PngWriter();
        $qrResult = $writer->write($qrCode);
        $qrPath = $qrDir . DIRECTORY_SEPARATOR . $code . '.png';
        $qrResult->saveToFile($qrPath);

        $logoPath = public_path('images/logo.jpg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $qrBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($qrPath));

        $patient = Patient::findOrFail($request->patient_id);
        $patientAge = $patient->dob ? (int) floor($patient->dob->diffInYears(now(), true)) : 'N/A';
        $examResults = $result->examResults()->with('exam')->get()->groupBy('exam_id');

        $pdf = Pdf::loadView('pdf.result_multi', compact(
            'result', 'patient', 'examResults',
            'qrBase64', 'logoBase64', 'patientAge'
        ));
        $pdf->save($pdfDir . DIRECTORY_SEPARATOR . $code . '.pdf');

        return redirect()->route('resultats.index')
            ->with('success', "Résultat #$code généré !");
    }

    /**
     * Enregistre les lignes d'un examen. Le type de tableau est détecté par le NOM
     * de l'examen, exactement comme dans le formulaire (create.blade.php).
     * Une ligne supprimée dans le formulaire n'est pas envoyée : elle est simplement ignorée.
     */
    private function saveExam(Result $result, Exam $exam, array $data): void
    {
        $kind = ExamTemplates::kind($exam->name);
        $rows = []; // [param, resultat, norme, observation]

        switch ($kind) {
            case 'serologie':
            case 'serologie_hb':
                foreach (ExamTemplates::serologieParams($kind) as $i => $param) {
                    if (!isset($data[$i]) || !is_array($data[$i])) continue;
                    $rows[] = [$param, $data[$i]['resultat'] ?? '', '-', $data[$i]['observation'] ?? ''];
                }
                $sdw = $data['sdw'] ?? null;
                if (is_array($sdw)) {
                    foreach (ExamTemplates::SDW_FIELDS as $field) {
                        $rows[] = [$field, $sdw[$field] ?? '', '-', ''];
                    }
                    // Observation propre au bloc SDW
                    $rows[] = ['SDW_OBS', '', '-', $sdw['observation'] ?? ''];
                }
                break;

            case 'biochimie':
                foreach (ExamTemplates::biochimieFlat() as $i => $row) {
                    if (!isset($data[$i]) || !is_array($data[$i])) continue;
                    $rows[] = [$row['nom'], $data[$i]['resultat'] ?? '', $row['norme'], ''];
                }
                break;

            case 'hemato':
                // Pourcentage % -> "resultat" ; Absolues G/L -> "observation" (colonne inutilisée en hémato).
                foreach (HematoLayout::all() as $i => [$param, $norme, $type]) {
                    if (!isset($data[$i]) || !is_array($data[$i])) continue;
                    $rows[] = [
                        $param,
                        $data[$i]['resultat'] ?? '',
                        $norme,
                        $type === 'split' ? ($data[$i]['absolu'] ?? '') : '',
                    ];
                }
                break;

            case 'atb':
                foreach (($data['antibiotics'] ?? []) as $antibio) {
                    $name = trim(strip_tags((string) ($antibio['name'] ?? '')));
                    $res = (string) ($antibio['result'] ?? '');
                    if ($name !== '' && in_array($res, ['S', 'I', 'R'], true)) {
                        $rows[] = [$name, $res, '-', ''];
                    }
                }
                break;

            default:
                $params = $exam->params ?? [];
                if (is_string($params)) {
                    $params = json_decode($params, true) ?: [];
                }
                foreach ($params as $i => $param) {
                    if (!isset($data[$i]) || !is_array($data[$i])) continue;
                    $rows[] = [
                        $param['param'] ?? 'Paramètre ' . ($i + 1),
                        $data[$i]['resultat'] ?? '',
                        $param['norme'] ?? '-',
                        $data[$i]['observation'] ?? '',
                    ];
                }
                break;
        }

        // Sérologie, biochimie et hématologie : le PDF reproduit la fiche complète,
        // donc on garde aussi les lignes laissées vides (seules les lignes supprimées
        // avec ✕ disparaissent). Pour les autres examens, on ne garde que le rempli.
        $keepEmpty = in_array($kind, ['serologie', 'serologie_hb', 'biochimie', 'hemato'], true);

        foreach ($rows as [$param, $resultat, $norme, $observation]) {
            $resultat = $this->cleanRich($resultat);
            $observation = $this->cleanRich($observation);

            if (!$keepEmpty && $resultat === '' && $observation === '') continue;

            ExamResult::create([
                'result_id' => $result->id,
                'exam_id' => $exam->id,
                'param' => $param,
                'resultat' => $resultat,
                'norme' => $norme,
                'observation' => $observation,
            ]);
        }

        // Lignes libres du bas (nom du paramètre modifiable) : enregistrées seulement si
        // quelque chose y est écrit ; sinon le PDF laisse simplement une ligne vide.
        $free = $data['free'] ?? [];
        if (is_array($free)) {
            // Accepte une seule ligne (ancien format) ou une liste de lignes.
            if (isset($free['param']) || isset($free['resultat']) || isset($free['observation'])) {
                $free = [$free];
            }
            foreach ($free as $line) {
                if (!is_array($line)) continue;
                $fParam = trim(strip_tags((string) ($line['param'] ?? '')));
                $fRes = $this->cleanRich($line['resultat'] ?? '');
                $fObs = $this->cleanRich($line['observation'] ?? '');
                if ($fParam === '' && $fRes === '' && $fObs === '') continue;
                ExamResult::create([
                    'result_id' => $result->id,
                    'exam_id' => $exam->id,
                    'param' => $fParam !== '' ? $fParam : '—',
                    'resultat' => $fRes,
                    'norme' => self::LIBRE,
                    'observation' => $fObs,
                ]);
            }
        }
    }

    /**
     * Nettoie le texte riche des cellules : garde gras / italique / souligné / retours à la ligne
     * et la taille du texte (font-size en px). Tout le reste est supprimé.
     * Le formulaire s'affiche en 14px et le PDF en 12px : la taille est mise à l'échelle.
     */
    private function cleanRich($html): string
    {
        $html = strip_tags((string) $html, '<b><strong><i><em><u><span><br><div><p>');
        $html = preg_replace_callback('/<(\/?)([a-zA-Z0-9]+)([^>]*)>/', function ($m) {
            $tag = strtolower($m[2]);
            if ($m[1] === '/') return "</$tag>";
            if ($tag === 'br') return '<br>';
            if ($tag === 'span' && preg_match('/font-size\s*:\s*([0-9.]+)px/i', $m[3], $s)) {
                $px = max(7, min(30, round(((float) $s[1]) * 12 / 14, 1)));
                return '<span style="font-size:' . $px . 'px">';
            }
            return "<$tag>";
        }, $html);
        $text = str_replace("\xC2\xA0", ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
        return trim($text) === '' ? '' : trim($html);
    }
}
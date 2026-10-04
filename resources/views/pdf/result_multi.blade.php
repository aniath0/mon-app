<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Résultat {{ $result->code }}</title>
@php
    // ===== À MODIFIER ICI : en-tête de la Clinique =====
    $entete = [
        'lignes'   => ['RÉPUBLIQUE DU BÉNIN', 'MINISTÈRE DE LA SANTÉ'],
        'centre'   => 'CLINIQUE GRÂCE DIVINE',
        'adresse'  => '',   // ex. : 'Sakété, Plateau – Bénin'  (vide = rien d'affiché)
        'contacts' => '',   // ex. : 'Tél : +229 00 00 00 00'   (vide = rien d'affiché)
        'ville'    => 'Fait à Sakété',
    ];
    $LIBRE = '__libre__';
    $globalDone = false; // devient true si l'observation générale est placée dans le tableau d'hématologie

    $ageTexte = is_numeric($patientAge) ? $patientAge . ' ans' : $patientAge;

    // Une cellule est "vide" si elle ne contient que du HTML vide / espaces.
    $isEmpty = function ($v) {
        $t = html_entity_decode(strip_tags((string) $v), ENT_QUOTES, 'UTF-8');
        $t = str_replace("\xC2\xA0", ' ', $t);
        return trim($t) === '';
    };
    $cell = fn($v) => $isEmpty($v) ? '&nbsp;' : $v;
@endphp
<style>
@page { margin: 26px 34px; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; color: #000; margin: 0; line-height: 1.4; }

/* ===== En-tête ===== */
.head { width: 100%; table-layout: fixed; border-collapse: collapse; }
.head td { vertical-align: middle; padding: 0; }
.h-left { width: 20%; text-align: left; }
.h-left img { width: 85px; }
.h-center { width: 60%; text-align: center; }
.h-right { width: 20%; text-align: right; }
.qr-code { width: 74px; height: 74px; }
.result-info { font-size: 11px; font-weight: bold; margin-top: 2px; }
.ministry { font-size: 10px; font-weight: bold; letter-spacing: 1px; }
.centre-name { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #1a3c6e; margin: 6px 0 2px; letter-spacing: 1px; }
.coords { font-size: 10px; color: #333; margin-top: 2px; }
.rule { border-top: 2px solid #1a3c6e; border-bottom: 1px solid #1a3c6e; height: 2px; margin: 10px 0 12px; }
.title-wrap { text-align: center; margin-bottom: 14px; }
.bulletin-title { display: inline-block; border: 1.5px solid #1a3c6e; color: #1a3c6e; font-size: 14px; font-weight: bold; letter-spacing: 3px; padding: 5px 26px; }

/* ===== Identité du patient (sans cadre) ===== */
.patient-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin: 0 0 18px; }
.patient-table td { padding: 4px 0; font-size: 13px; vertical-align: top; }
.patient-table td.pl { width: 20%; font-weight: bold; }
.patient-table td.pv { width: 30%; }

/* ===== Examens ===== */
.exam-block { margin-bottom: 16px; }
.exam-title { text-align: center; font-weight: bold; font-size: 14px; text-transform: uppercase; margin-bottom: 8px; color: #1a3c6e; }
.fiche { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 14px; }
.fiche th, .fiche td { border: 1px solid #000; padding: 4px 8px; font-size: 12px; vertical-align: middle; }
.fiche th { background: #f0f0f0; font-weight: bold; text-align: left; text-transform: uppercase; }
.fiche tr { page-break-inside: avoid; }
.fiche td.row-h { height: 22px; }
.section-title { background: #e8e8ff; font-weight: bold; }
.muted { color: #555; font-size: 10px; }
.nw { white-space: nowrap; }

/* SDW : AO ( ) AH ( ) sur deux colonnes alignées, sans quadrillage */
.fiche .sdw-plain { width: 100%; table-layout: fixed; border-collapse: collapse; border: none; margin: 0; }
.fiche .sdw-plain td { width: 50%; border: none; padding: 1px 0; font-size: 12px; line-height: 1.2; }
.sdw-conclusion { margin-top: 3px; font-size: 12px; line-height: 1.2; }

.date-line { margin-top: 26px; text-align: right; font-size: 13px; }
.signature-section { margin-top: 8px; text-align: right; font-size: 13px; }
</style>
</head>
<body>

<table class="head">
    <tr>
        <td class="h-left">
            @if($logoBase64)<img src="{{ $logoBase64 }}">@endif
        </td>
        <td class="h-center">
            @foreach($entete['lignes'] as $ligne)
                <div class="ministry">{{ $ligne }}</div>
            @endforeach
            <div class="centre-name">{{ $entete['centre'] }}</div>
            @if($entete['adresse'])<div class="coords">{{ $entete['adresse'] }}</div>@endif
            @if($entete['contacts'])<div class="coords">{{ $entete['contacts'] }}</div>@endif
        </td>
        <td class="h-right">
            <img src="{{ $qrBase64 }}" class="qr-code">
            <div class="result-info">N° {{ $result->code }}<br>{{ $result->date->format('d/m/Y') }}</div>
        </td>
    </tr>
</table>
<div class="rule"></div>
<div class="title-wrap"><span class="bulletin-title">BULLETIN D'ANALYSES</span></div>

<table class="patient-table">
    <tr>
        <td class="pl">Nom et Prénoms</td>
        <td class="pv">{{ $patient->name }}</td>
        <td class="pl">Âge</td>
        <td class="pv">{{ $ageTexte }}</td>
    </tr>
    <tr>
        <td class="pl">Sexe</td>
        <td class="pv">{{ $patient->sex ?? 'Non spécifié' }}</td>
        <td class="pl">N° Dossier</td>
        <td class="pv">{{ $patient->dossier_number }}</td>
    </tr>
</table>

@foreach($examResults as $examId => $rows)
@php
    $exam  = $rows->first()->exam;
    $kind  = \App\Support\ExamTemplates::kind($exam->name ?? '');
    // Lignes libres saisies dans le formulaire (0, 1 ou plusieurs) + lignes normales
    $libres = $rows->filter(fn($r) => $r->norme === $LIBRE)->values();
    $std    = $rows->reject(fn($r) => $r->norme === $LIBRE);
    $byParam = $std->keyBy('param');
    $libreObs = $libres->contains(fn($r) => !$isEmpty($r->observation));
    // S'il n'y en a aucune, on laisse quand même une ligne vide à la main.
    $libreRows = $libres->isNotEmpty() ? $libres : collect([null]);
@endphp
<div class="exam-block">
    <div class="exam-title">{{ $exam->name ?? 'Examen' }}</div>

    {{-- ===================== SÉROLOGIE ===================== --}}
    @if($kind === 'serologie' || $kind === 'serologie_hb')
        @php
            $sdwFields = \App\Support\ExamTemplates::SDW_FIELDS;
            $paramRows = $std->filter(fn($r) => !in_array($r->param, $sdwFields) && $r->param !== 'SDW_OBS')->keyBy('param');
            $sdw       = $std->filter(fn($r) => in_array($r->param, $sdwFields))->pluck('resultat', 'param');
            $sdwObsRow = $std->first(fn($r) => $r->param === 'SDW_OBS');
            $hasSdw    = $sdw->isNotEmpty();
            $showObs = $paramRows->contains(fn($r) => !$isEmpty($r->observation))
                    || $libreObs
                    || ($hasSdw && $sdwObsRow && !$isEmpty($sdwObsRow->observation));
        @endphp
        <table class="fiche">
            <thead>
                <tr>
                    <th style="width:{{ $showObs ? '28%' : '35%' }}">Paramètres</th>
                    <th>Résultats</th>
                    @if($showObs)<th style="width:28%">Observations</th>@endif
                </tr>
            </thead>
            <tbody>
            @foreach(\App\Support\ExamTemplates::serologieParams($kind) as $p)
                @if($paramRows->has($p))
                <tr>
                    <td class="row-h">{{ $p }}</td>
                    <td>{!! $cell($paramRows->get($p)->resultat) !!}</td>
                    @if($showObs)<td>{!! $cell($paramRows->get($p)->observation) !!}</td>@endif
                </tr>
                @endif
            @endforeach
            @foreach($libreRows as $lr)
                <tr>
                    <td class="row-h">{!! $lr ? e($lr->param) : '&nbsp;' !!}</td>
                    <td>{!! $lr ? $cell($lr->resultat) : '&nbsp;' !!}</td>
                    @if($showObs)<td>{!! $lr ? $cell($lr->observation) : '&nbsp;' !!}</td>@endif
                </tr>
            @endforeach
            @if($hasSdw)
                <tr>
                    <td style="font-weight:bold">SDW</td>
                    <td>
                        <table class="sdw-plain">
                            @foreach([['AO','AH'],['BO','BH'],['CO','CH'],['TO','TH']] as [$a, $b])
                            <tr>
                                <td>{{ $a }} ( {!! $sdw[$a] ?? '' !!} )</td>
                                <td>{{ $b }} ( {!! $sdw[$b] ?? '' !!} )</td>
                            </tr>
                            @endforeach
                        </table>
                        <div class="sdw-conclusion">
                            <span style="font-weight:bold;text-decoration:underline">Conclusion :</span>
                            {!! $sdw['conclusion'] ?? '' !!}
                        </div>
                    </td>
                    @if($showObs)<td>{!! $sdwObsRow ? $cell($sdwObsRow->observation) : '&nbsp;' !!}</td>@endif
                </tr>
            @endif
            </tbody>
        </table>

    {{-- ===================== BIOCHIMIE ===================== --}}
    @elseif($kind === 'biochimie')
        @php
            $allRows = collect(\App\Support\ExamTemplates::biochimie())
                ->flatMap(fn($s) => $s['rows'])
                ->filter(fn($r) => $byParam->has($r['nom']));
            $showUnite = $allRows->contains(fn($r) => !in_array(trim((string) $r['unite']), ['', '-'], true));
            $showNorme = $allRows->contains(fn($r) => !in_array(trim((string) $r['norme']), ['', '-'], true));
            $nc = 2 + ($showUnite ? 1 : 0) + ($showNorme ? 1 : 0);
        @endphp
        <table class="fiche">
            <thead>
                <tr>
                    <th style="width:33%">Paramètres</th>
                    <th>Résultats</th>
                    @if($showUnite)<th style="width:10%;text-align:center">Unités</th>@endif
                    @if($showNorme)<th style="width:33%">Valeurs normales</th>@endif
                </tr>
            </thead>
            <tbody>
            @foreach(\App\Support\ExamTemplates::biochimie() as $section)
                @php $present = collect($section['rows'])->filter(fn($r) => $byParam->has($r['nom'])); @endphp
                @if($present->isNotEmpty())
                    @if($section['title'])
                    <tr><td colspan="{{ $nc }}" class="section-title">{{ $section['title'] }}</td></tr>
                    @endif
                    @foreach($present as $r)
                    <tr>
                        <td class="row-h">{{ $r['nom'] }}</td>
                        <td>{!! $cell($byParam->get($r['nom'])->resultat) !!}</td>
                        @if($showUnite)<td style="text-align:center">{{ $r['unite'] }}</td>@endif
                        @if($showNorme)<td>{{ $r['norme'] }}</td>@endif
                    </tr>
                    @endforeach
                @endif
            @endforeach
            @foreach($libreRows as $lr)
                <tr>
                    <td class="row-h">{!! $lr ? e($lr->param) : '&nbsp;' !!}</td>
                    <td>{!! $lr ? $cell($lr->resultat) : '&nbsp;' !!}</td>
                    @if($showUnite)<td>&nbsp;</td>@endif
                    @if($showNorme)<td>&nbsp;</td>@endif
                </tr>
            @endforeach
            </tbody>
        </table>

    {{-- ===================== HÉMATOLOGIE / NFS ===================== --}}
    @elseif($kind === 'hemato')
        @php $HL = \App\Support\HematoLayout::all(); @endphp
        <table class="fiche">
            <thead>
                <tr>
                    <th rowspan="2" class="nw" style="width:23%">Paramètres</th>
                    <th rowspan="2" class="nw" style="width:14%">Résultats</th>
                    <th rowspan="2" class="nw" style="width:28%">Paramètres</th>
                    <th colspan="2" class="nw" style="text-align:center">Résultats</th>
                </tr>
                <tr>
                    <th class="nw" style="text-align:center;width:17.5%">Pourcentage %</th>
                    <th class="nw" style="text-align:center;width:17.5%">Absolues G/L</th>
                </tr>
            </thead>
            <tbody>
            @for($j = 0; $j < 7; $j++)
                @php
                    $lp = $HL[$j * 2];
                    $rp = $HL[$j * 2 + 1];
                    $l = $byParam->get($lp[0]);
                    $r = $rp[2] === 'text' ? null : $byParam->get($rp[0]);
                @endphp
                {{-- Chaque moitié est indépendante : si l'une est supprimée, l'autre reste. --}}
                @if($l || $r)
                <tr>
                    <td class="row-h nw">@if($l){{ $lp[0] }} @if($lp[1])<span class="muted">{{ $lp[1] }}</span>@endif @else &nbsp; @endif</td>
                    <td>{!! $l ? $cell($l->resultat) : '&nbsp;' !!}</td>
                    <td class="nw">@if($r){{ $rp[0] }} @if($rp[1])<span class="muted">{{ $rp[1] }}</span>@endif @else &nbsp; @endif</td>
                    @if($rp[2] === 'split')
                        <td>{!! $r ? $cell($r->resultat) : '&nbsp;' !!}</td>
                        <td>{!! $r ? $cell($r->observation) : '&nbsp;' !!}</td>
                    @else
                        <td colspan="2">{!! $r ? $cell($r->resultat) : '&nbsp;' !!}</td>
                    @endif
                </tr>
                @endif
            @endfor
            @foreach($libreRows as $lr)
                <tr>
                    <td class="row-h">{!! $lr ? e($lr->param) : '&nbsp;' !!}</td>
                    <td>{!! $lr ? $cell($lr->resultat) : '&nbsp;' !!}</td>
                    <td>&nbsp;</td>
                    <td colspan="2">&nbsp;</td>
                </tr>
            @endforeach
            {{-- Observation : une ligne du tableau --}}
            @php $obsRow = $byParam->get('OBSERVATION'); @endphp
            @if($obsRow)
                <tr>
                    <td class="nw" style="font-weight:bold">Observation</td>
                    <td colspan="4">{!! $cell($obsRow->resultat) !!}</td>
                </tr>
            @endif
            {{-- GE / DP : ligne collée à la suite du tableau (c'est le champ de l'ancienne « observation générale ») --}}
            @if($result->global_observation)
                @php $globalDone = true; @endphp
                <tr>
                    <td class="nw" style="font-weight:bold">GE / DP</td>
                    <td colspan="4">{!! nl2br(e($result->global_observation)) !!}</td>
                </tr>
            @endif
            </tbody>
        </table>

    {{-- ===================== ANTIBIOGRAMME ===================== --}}
    @elseif($kind === 'atb')
        @php $labels = ['S' => 'Sensible', 'I' => 'Intermédiaire', 'R' => 'Résistant']; @endphp
        @if($std->count() > 0)
        <table class="fiche">
            <thead>
                <tr>
                    <th style="width:60%">Antibiotique</th>
                    <th>Résultat</th>
                </tr>
            </thead>
            <tbody>
            @foreach($std as $r)
                <tr>
                    <td class="row-h">{{ $r->param }}</td>
                    <td>{{ $labels[$r->resultat] ?? $r->resultat }}</td>
                </tr>
            @endforeach
                <tr>
                    <td class="row-h">&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>
        @endif

    {{-- ===================== AUTRES EXAMENS ===================== --}}
    @else
        @if($std->count() > 0 || $libres->isNotEmpty())
        @php
            $showNorme = $std->contains(fn($r) => !in_array(trim((string) $r->norme), ['', '-'], true));
            $showObs   = $std->contains(fn($r) => !$isEmpty($r->observation)) || $libreObs;
        @endphp
        <table class="fiche">
            <thead>
                <tr>
                    <th>Paramètres</th>
                    <th>Résultats</th>
                    @if($showNorme)<th>Norme</th>@endif
                    @if($showObs)<th>Observations</th>@endif
                </tr>
            </thead>
            <tbody>
            @foreach($std as $r)
                <tr>
                    <td class="row-h">{{ $r->param }}</td>
                    <td>{!! $cell($r->resultat) !!}</td>
                    @if($showNorme)<td>{{ $r->norme }}</td>@endif
                    @if($showObs)<td>{!! $cell($r->observation) !!}</td>@endif
                </tr>
            @endforeach
            @foreach($libreRows as $lr)
                <tr>
                    <td class="row-h">{!! $lr ? e($lr->param) : '&nbsp;' !!}</td>
                    <td>{!! $lr ? $cell($lr->resultat) : '&nbsp;' !!}</td>
                    @if($showNorme)<td>&nbsp;</td>@endif
                    @if($showObs)<td>{!! $lr ? $cell($lr->observation) : '&nbsp;' !!}</td>@endif
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    @endif
</div>
@endforeach

@if($result->global_observation && !$globalDone)
<div style="border: 1px solid #000; padding: 12px; margin-top: 16px; font-size: 13px; line-height: 1.5;">
    <strong>Observation Générale :</strong><br>
    {!! nl2br(e($result->global_observation)) !!}
</div>
@endif

<div class="date-line">
    {{ $entete['ville'] }}, le {{ $result->date->format('d/m/Y') }}
</div>
<div class="signature-section">
    Le Biologiste Responsable<br>
    {{ auth()->user()->name ?? '' }}
</div>

</body>
</html>
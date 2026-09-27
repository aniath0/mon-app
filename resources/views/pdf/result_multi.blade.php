<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Résultat {{ $result->code }}</title>
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 14px; color: #000; margin: 20px; line-height: 1.5; }
.container { max-width: 800px; margin: auto; position: relative; }
.logo { position: absolute; left: 0; top: 0; }
.logo img { width: 90px; }
.header { text-align: center; margin-bottom: 25px; }
.ministry { font-size: 12px; font-weight: bold; }
.clinic-name { font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 5px 0; text-decoration: underline; }
.service-name { font-size: 14px; font-weight: bold; margin: 4px 0; }
.bulletin-title { font-size: 16px; font-weight: bold; margin: 8px 0; text-decoration: underline; }
.right-section { position: absolute; right: 0; top: 0; text-align: center; width: 140px; }
.qr-code { width: 80px; height: 80px; margin-bottom: 6px; }
.result-info { font-size: 14px; font-weight: bold; }
.patient-table { width: 65%; margin-top: 20px; margin-bottom: 25px; border-collapse: collapse; }
.patient-table td { padding: 5px 0; width: 50%; font-size: 14px; }
.patient-label { font-weight: bold; padding-right: 6px; }
.results-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px; font-size: 13px; }
.results-table th, .results-table td { border: 1px solid #000; padding: 6px 8px; font-size: 13px; }
.results-table th { background: #f0f0f0; font-weight: bold; }
.section-title { background: #e8e8ff; font-weight: bold; padding: 6px 8px; font-size: 13px; }
.date-line { margin-top: 30px; text-align: right; font-size: 14px; }
.signature-section { margin-top: 10px; text-align: right; font-size: 14px; }
.exam-block { margin-bottom: 20px; }
.exam-title { background: #f0f0ff; padding: 8px; font-weight: bold; font-size: 15px; }
.sdw-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px; font-size: 13px; }
.sdw-table td { border: 1px solid #000; padding: 8px; }
</style>
</head>
<body>
<div class="container">

<div class="logo">
    @if($logoBase64)
        <img src="{{ $logoBase64 }}">
    @endif
</div>

<div class="right-section">
    <img src="{{ $qrBase64 }}" class="qr-code">
    <div class="result-info">
        <strong>N° {{ $result->code }}</strong><br>
        {{ $result->date->format('d/m/Y') }}
    </div>
</div>

<div class="header">
    <div class="ministry">RÉPUBLIQUE DU BÉNIN</div>
    <div class="ministry">MINISTÈRE DE LA SANTÉ</div>
    <div class="clinic-name">CLINIQUE GRÂCE DIVINE</div>
    <div class="service-name">Service de Diagnostics Biologiques</div>
    <div class="bulletin-title">BULLETIN D'ANALYSES</div>
</div>

<table class="patient-table">
    <tr>
        <td><span class="patient-label">Sexe :</span>{{ $patient->sex ?? 'Non spécifié' }}</td>
        <td style="padding-left: 40px;"><span class="patient-label">N° Dossier :</span>{{ $patient->dossier_number }}</td>
    </tr>
    <tr>
        <td colspan="2" style="white-space: nowrap;"><span class="patient-label">Nom et Prénoms :</span>{{ $patient->name }}</td>
    </tr>
</table>

@foreach($examResults as $examId => $results)
@php
    $exam = $results->first()->exam;
    $examType = $exam ? strtolower(trim($exam->type)) : '';
@endphp
<div class="exam-block">
    <div class="exam-title">{{ $exam->name ?? 'Examen' }}</div>

    @if($examType === 'serologie' || $examType === 'serologie_hb')
        @php
            $sdwFields = ['AO','AH','BO','BH','CO','CH','TO','TH','conclusion'];
            $normalResults = $results->filter(fn($r) => !in_array($r->param, $sdwFields) && !empty(trim($r->resultat)));
            $sdwResults = $results->filter(fn($r) => in_array($r->param, $sdwFields));
            $sdw = $sdwResults->pluck('resultat', 'param');
        @endphp

        @if($normalResults->count() > 0)
        <table class="results-table">
            <thead>
                <tr>
                    <th style="width:30%">PARAMÈTRES</th>
                    <th style="width:40%">RÉSULTATS</th>
                    <th style="width:30%">OBSERVATIONS</th>
                </tr>
            </thead>
            <tbody>
            @foreach($normalResults as $r)
                <tr>
                    <td>{{ $r->param }}</td>
                    <td>{!! $r->resultat !!}</td>
                    <td>{!! $r->observation !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif

        @if($sdwResults->count() > 0)
        <table class="sdw-table">
            <tbody>
                <tr>
                    <td style="width:15%;font-weight:bold">AO</td>
                    <td style="width:35%">( {!! $sdw['AO'] ?? '' !!} )</td>
                    <td style="width:15%;font-weight:bold">AH</td>
                    <td style="width:35%">( {!! $sdw['AH'] ?? '' !!} )</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">BO</td>
                    <td>( {!! $sdw['BO'] ?? '' !!} )</td>
                    <td style="font-weight:bold">BH</td>
                    <td>( {!! $sdw['BH'] ?? '' !!} )</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">CO</td>
                    <td>( {!! $sdw['CO'] ?? '' !!} )</td>
                    <td style="font-weight:bold">CH</td>
                    <td>( {!! $sdw['CH'] ?? '' !!} )</td>
                </tr>
                <tr>
                    <td style="font-weight:bold">TO</td>
                    <td>( {!! $sdw['TO'] ?? '' !!} )</td>
                    <td style="font-weight:bold">TH</td>
                    <td>( {!! $sdw['TH'] ?? '' !!} )</td>
                </tr>
                @if(!empty($sdw['conclusion']))
                <tr>
                    <td style="font-weight:bold;text-decoration:underline">Conclusion</td>
                    <td colspan="3">{!! $sdw['conclusion'] !!}</td>
                </tr>
                @endif
            </tbody>
        </table>
        @endif

    @elseif($examType === 'biochimie')
        @php
            $sections = [
                '' => ['Glycémie à jeun','Glycémie PP','Urée','Créatininémie','Uricémie','Calcémie','Magnésémie','Cholestérol total','HDL Cholestérol','LDL Cholestérol','Phosphatases alcalines','Amylasémie'],
                'Transaminases' => ['TGO','TGP'],
                'Bilirubine' => ['Totale','Direct'],
                '_suite' => ['Protéines Totales','Triglycérides','Gamma GT','Phosphore'],
                'Ionogramme sanguin' => ['Na+','K+','Cl-'],
            ];
            $unites = [
                'Glycémie à jeun'=>'g/l','Glycémie PP'=>'g/l','Urée'=>'g/l','Créatininémie'=>'mg/l',
                'Uricémie'=>'mg/l','Calcémie'=>'mg/l','Magnésémie'=>'mg/l','Cholestérol total'=>'g/l',
                'HDL Cholestérol'=>'g/l','LDL Cholestérol'=>'g/l','Phosphatases alcalines'=>'U/l',
                'Amylasémie'=>'U/l','TGO'=>'U/l','TGP'=>'U/l','Totale'=>'mg/l','Direct'=>'mg/l',
                'Protéines Totales'=>'g/l','Triglycérides'=>'g/l','Gamma GT'=>'U/l','Phosphore'=>'mg/l',
                'Na+'=>'mEq/l','K+'=>'mEq/l','Cl-'=>'mEq/l',
            ];
            $normes = [
                'Glycémie à jeun'=>'0.74 - 1.10 g/l','Glycémie PP'=>'0.80 - 1.40 g/l',
                'Urée'=>'0.10 - 0.50 g/l','Créatininémie'=>'6 - 13 mg/l','Uricémie'=>'30 - 57 mg/l',
                'Calcémie'=>'85 - 105 mg/l','Magnésémie'=>'16 - 25 mg/l','Cholestérol total'=>'1.50 - 2.60 g/l',
                'HDL Cholestérol'=>'0.35 - 0.65 g/l','LDL Cholestérol'=>'< 1.50 g/l',
                'Phosphatases alcalines'=>'Adlt: 98-279, Enf: <6','Amylasémie'=>'< 90 U/l',
                'TGO'=>'< 40 U/l','TGP'=>'< 40 U/l','Totale'=>'< 10 mg/l','Direct'=>'< 2.50 mg/l',
                'Protéines Totales'=>'62 - 82 g/l','Triglycérides'=>'0.40 - 1.40 g/l',
                'Gamma GT'=>'H: 11-50, F: 7-32','Phosphore'=>'Adlt: 25-50, Enft: 40-70',
                'Na+'=>'135 - 145 mEq/l','K+'=>'3.50 - 5.50 mEq/l','Cl-'=>'98 - 108 mEq/l',
            ];
            $byParam = $results->keyBy('param');
        @endphp

        <table class="results-table">
            <thead>
                <tr>
                    <th style="width:35%">PARAMÈTRES</th>
                    <th style="width:20%">RÉSULTATS</th>
                    <th style="width:10%">UNITÉS</th>
                    <th style="width:35%">VALEURS NORMALES</th>
                </tr>
            </thead>
            <tbody>
            @foreach($sections as $sectionTitle => $paramList)
                @if($sectionTitle && $sectionTitle !== '_suite')
                <tr><td colspan="4" class="section-title">{{ $sectionTitle }}</td></tr>
                @endif
                @foreach($paramList as $paramName)
                    @if(isset($byParam[$paramName]) && !empty(trim($byParam[$paramName]->resultat)))
                    <tr>
                        <td>{{ $paramName }}</td>
                        <td>{!! $byParam[$paramName]->resultat !!}</td>
                        <td style="text-align:center">{{ $unites[$paramName] ?? '' }}</td>
                        <td>{{ $normes[$paramName] ?? '' }}</td>
                    </tr>
                    @endif
                @endforeach
            @endforeach
            </tbody>
        </table>

    @elseif($examType === 'hemato')
        @php
            $byParam = $results->keyBy('param');
            $leftParams = [
                'G.BLANCS' => '3 à 8 G/l',
                'G.ROUGES' => '4 à 6 T/l',
                'HEMOGLOBINE' => '12 à 16 g/dl',
                'HEMATOCRITE' => '35 à 45 %',
                'VGM' => '80 à 90 FL',
                'CCMH' => '30 à 36 g/dl',
                'TCMH' => '25 à 32 pg',
            ];
            $rightParams = ['NEUTROPHILE %','EOSINOPHILE %','BASOPHILE %','LYMPHOCYTE %','MONOCYTE %','PLAQUETTES','GE'];
        @endphp

        <table class="results-table">
            <thead>
                <tr>
                    <th style="width:25%">PARAMÈTRES</th>
                    <th style="width:25%">RÉSULTATS</th>
                    <th style="width:25%">PARAMÈTRES</th>
                    <th style="width:25%">RÉSULTATS</th>
                </tr>
            </thead>
            <tbody>
            @foreach(array_keys($leftParams) as $i => $leftParam)
                @php $rightParam = $rightParams[$i] ?? null; @endphp
                @if((isset($byParam[$leftParam]) && !empty(trim($byParam[$leftParam]->resultat))) || ($rightParam && isset($byParam[$rightParam]) && !empty(trim($byParam[$rightParam]->resultat))))
                <tr>
                    <td>{{ $leftParam }} <small style="color:#888">{{ $leftParams[$leftParam] }}</small></td>
                    <td>{!! $byParam[$leftParam]->resultat ?? '' !!}</td>
                    <td>{{ $rightParam ?? '' }}</td>
                    <td>{!! isset($byParam[$rightParam]) ? $byParam[$rightParam]->resultat : '' !!}</td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>

    @else
        @php
            $filteredResults = $results->filter(fn($r) => !empty(trim($r->resultat)) && $r->resultat != '-');
        @endphp
        @if($filteredResults->count() > 0)
        <table class="results-table">
            <thead>
                <tr>
                    <th>Paramètres</th>
                    <th>Résultats</th>
                    <th>Norme</th>
                    <th>Observations</th>
                </tr>
            </thead>
            <tbody>
            @foreach($filteredResults as $r)
                <tr>
                    <td>{{ $r->param }}</td>
                    <td>{!! $r->resultat !!}</td>
                    <td>{{ $r->norme }}</td>
                    <td>{!! $r->observation !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    @endif
</div>
@endforeach

@if($result->global_observation)
<div style="border: 1px solid #000; padding: 15px; margin-top: 20px; font-size: 14px; line-height: 1.6;">
    <strong>Observation Générale :</strong><br>
    {!! $result->global_observation !!}
</div>
@endif

<div class="date-line">
    Fait à Sakété, le {{ $result->date->format('d/m/Y') }}
</div>
<div class="signature-section">
    Le Biologiste Responsable<br>
    {{ auth()->user()->name ?? '' }}
</div>

</div>
</body>
</html>
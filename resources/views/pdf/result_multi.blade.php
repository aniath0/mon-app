<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Résultat {{ $result->code }}</title>
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 17px; color: #000; margin: 20px; line-height: 1.5; }
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
.results-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px; font-size: 14px; }
.results-table th, .results-table td { border: 1px solid #000; padding: 8px; font-size: 14px; }
.results-table th { background: #f0f0f0; font-weight: bold; }
.date-line { margin-top: 30px; text-align: right; font-size: 14px; }
.signature-section { margin-top: 10px; text-align: right; font-size: 14px; }
.exam-block { margin-bottom: 20px; }
.exam-title { background: #f0f0ff; padding: 8px; font-weight: bold; font-size: 16px; }
</style>
</head>
<body>
<div class="container">

<div class="logo"><img src="{{ public_path('images/logo.jpg') }}"></div>

<div class="right-section">
    <img src="{{ $qrPath }}" class="qr-code">
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

{{-- Boucle sur tous les examens --}}
@foreach($examResults as $examId => $results)
<div class="exam-block">
    <div class="exam-title">{{ $results->first()->exam->name ?? 'Examen' }}</div>
    
    @php
        // Filtrer seulement les résultats qui ont un champ "résultat" rempli
        $filteredResults = $results->filter(function($r) {
            return !empty(trim($r->resultat)) && $r->resultat != '-';
        });
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
                <td>{{ $r->resultat }}</td>
                <td>{{ $r->norme }}</td>
                <td>{{ $r->observation }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <div style="padding: 10px; border: 1px dashed #ccc; text-align: center; color: #666;">
        Aucun résultat saisi pour cet examen
    </div>
    @endif
</div>
@endforeach

@if($result->global_observation)
<div style="border: 1px solid #000; padding: 15px; margin-top: 20px; font-size: 16px; font-weight: bold; line-height: 1.6;">
    <strong>Observation Générale :</strong><br>
    {{ $result->global_observation }}
</div>
@endif

<div class="date-line">
    Fait à Sakété, le {{ $result->date->format('d/m/Y') }}
</div>
<div class="signature-section">
    Le Biologiste Responsable<br>
    {{ auth()->user()->name ?? '' }}
</div>
</body>
</html>
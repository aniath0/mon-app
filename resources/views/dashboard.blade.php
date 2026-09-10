@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stat Patients -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.67 3.623a10.95 10.95 0 01-.67 3.623"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Patients</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $patientsCount }}</p>
                </div>
            </div>
        </div>

        <!-- Stat Examens -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 mr-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Examens</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $examsCount }}</p>
                </div>
            </div>
        </div>

        <!-- Stat Résultats Total -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Résultats Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $resultsTotal }}</p>
                </div>
            </div>
        </div>

        
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Graph Résultats par Mois -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Résultats par Mois</h3>
            <canvas id="resultsChart" width="400" height="200"></canvas>
        </div>

        <!-- Derniers Résultats -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Derniers Résultats</h3>
            <div class="space-y-3">
                @forelse($recentResults as $result)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <div>
                        <p class="font-medium text-gray-900">{{ $result->code }}</p>
                        <p class="text-sm text-gray-500">{{ $result->patient->name ?? 'N/A' }}</p>
                    </div>
                    <a href="{{ asset('storage/pdf/' . $result->code . '.pdf') }}" class="text-indigo-600 hover:text-indigo-500 text-sm">PDF</a>
                </div>
                @empty
                <p class="text-sm text-gray-500">Aucun résultat récent.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('resultsChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Nombre de résultats',
                data: {!! json_encode($chartData) !!},
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>

@endsection
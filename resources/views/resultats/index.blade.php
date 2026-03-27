@extends('layouts.app')

@section('title', 'Historique des Résultats')
@section('header', 'Historique des Résultats')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-medium text-gray-800">Historique des Résultats</h1>
        <div class="space-x-2">
            <select class="p-2 border border-gray-300 rounded text-sm">
                <option>Tous</option>
                <option>Validés</option>
                <option>En attente</option>
            </select>
            <input type="date" class="p-2 border border-gray-300 rounded text-sm">
            <button class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700">Exporter PDF</button>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($results as $result)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $result->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $result->patient->name ?? 'N/A' }} ({{ $result->patient->dossier_number ?? '' }})</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $result->exam->name ?? $result->type_examen }}</td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $result->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ asset('storage/pdf/' . $result->code . '.pdf') }}" class="text-indigo-600 hover:text-indigo-500" target="_blank">Télécharger PDF</a>
                           
                            </form>
                            
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Aucun résultat trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 mt-4">
            {{ $results->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-medium text-gray-800">{{ $patient->name }}</h1>
        <div class="space-x-2">
            <a href="{{ route('patients.edit', $patient) }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Éditer</a>
            <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50" onclick="return confirm('Supprimer ?')">Supprimer</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <p class="text-lg text-gray-900">{{ $patient->name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dossier</label>
                <p class="text-lg text-gray-900">{{ $patient->dossier_number }}</p>
            </div>
            
            
        </div>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
            <p class="text-gray-900">{{ $patient->phone ?? 'Non renseigné' }}</p>
        </div>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
            <p class="text-gray-900">{{ $patient->address ?? 'Non renseignée' }}</p>
        </div>
    </div>

    <!-- Résultats du patient (futur lien) -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-medium text-gray-800 mb-4">Résultats</h2>
        <p class="text-sm text-gray-500">Aucun résultat pour l'instant. <a href="/resultats/create?patient={{ $patient->id }}" class="text-indigo-600 hover:text-indigo-700">Ajouter un test</a></p>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Titre + bouton ajouter -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-medium text-gray-800">Patients</h1>

    
            <a href="{{ route('patients.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4"></path>
                </svg>
                Ajouter patient
            </a>
       
    </div>

    <!-- Recherche -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100/50">
        <form method="GET" action="{{ route('patients.index') }}">
            <div class="flex gap-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Rechercher par nom ou dossier..."
                       class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                <button type="submit"
                        class="px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                    Rechercher
                </button>
            </div>
        </form>
    </div>

    <!-- Liste patients -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100/50 overflow-hidden">
        @if($patients->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dossier</th>
                            
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sexe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($patients as $patient)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $patient->name }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                    {{ $patient->dossier_number }}
                                </td>

                                

                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $patient->sex ?? 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $patient->phone ?? 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-sm font-medium">
                                    <a href="{{ route('patients.show', $patient) }}"
                                       class="text-indigo-600 hover:text-indigo-500 mr-3">
                                        Voir
                                    </a>

                                   
                                        <a href="{{ route('patients.edit', $patient) }}"
                                           class="text-indigo-600 hover:text-indigo-500 mr-3">
                                            Éditer
                                        </a>

                                        <form method="POST"
                                              action="{{ route('patients.destroy', $patient) }}"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-500"
                                                    onclick="return confirm('Supprimer {{ $patient->name }} ?')">
                                                Supprimer
                                            </button>
                                        </form>
                                   
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $patients->appends(request()->query())->links() }}
            </div>
        @else
            <div class="p-8 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun patient</h3>
                <p class="text-gray-500 mb-4">Commencez par ajouter le premier.</p>
            </div>
        @endif
    </div>

    <!-- Toast succès -->
    @if (session('success'))
        <div class="fixed top-4 right-4 bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg shadow-sm z-50 animate-fade-in">
            {{ session('success') }}
        </div>
    @endif
</div>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
@endsection

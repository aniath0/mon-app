@extends('layouts.app')

@section('title', 'Ajouter Examen')
@section('header', 'Ajouter Examen')

@section('content')
<div class="space-y-6">
    <form method="POST" action="{{ route('exams.store') }}" class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 space-y-6">
        @csrf
        <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Type (auto-incrémenté)</label>
    <input type="text" name="type" id="exam-type" value="{{ $nextType }}" readonly
       class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">

           
</div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom (ex. 'Biochimie Sanguine')</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Paramètres (ajoutez ligne par ligne, ex. de tes PDFs)</label>
            <div id="params-container" class="space-y-2">
                <div class="flex gap-2">
                    <input type="text" name="params[0][param]" placeholder="Paramètre (ex. 'Glycémie à jeun')" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" name="params[0][norme]" placeholder="Norme (ex. '0.74 - 1.10 g/l')" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="button" onclick="removeParam(this)" class="px-4 py-3 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Supprimer</button>
                </div>
            </div>
            <button type="button" onclick="addParam()" class="mt-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Ajouter Paramètre</button>
            @error('params') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('exams.index') }}" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</a>
            <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Enregistrer</button>
        </div>
    </form>
</div>

<script>
    let paramIndex = 1;
    function addParam() {
        const container = document.getElementById('params-container');
        const div = document.createElement('div');
        div.className = 'flex gap-2';
        div.innerHTML = `
            <input type="text" name="params[${paramIndex}][param]" placeholder="Paramètre" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            <input type="text" name="params[${paramIndex}][norme]" placeholder="Norme" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            <button type="button" onclick="removeParam(this)" class="px-4 py-3 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Supprimer</button>
        `;
        container.appendChild(div);
        paramIndex++;
    }

    function removeParam(button) {
        button.parentElement.remove();
    }
</script>
@endsection
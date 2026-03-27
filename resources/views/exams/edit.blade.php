@extends('layouts.app')

@section('title', 'Éditer ' . $exam->name)
@section('header', 'Éditer ' . $exam->name)

@section('content')
<div class="space-y-6">
    <form method="POST" action="{{ route('exams.update', $exam) }}" class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 space-y-6">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type (unique)</label>
            <input type="text" name="type" value="{{ old('type', $exam->type) }}" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
            <input type="text" name="name" value="{{ old('name', $exam->name) }}" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Paramètres</label>
            <div id="params-container" class="space-y-2">
                @forelse($exam->params as $index => $param)
                <div class="flex gap-2">
                    <input type="text" name="params[{{ $index }}][param]" value="{{ $param['param'] }}" placeholder="Paramètre" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" name="params[{{ $index }}][norme]" value="{{ $param['norme'] }}" placeholder="Norme" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="button" onclick="removeParam(this)" class="px-4 py-3 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Supprimer</button>
                </div>
                @empty
                <div class="flex gap-2">
                    <input type="text" name="params[0][param]" placeholder="Paramètre" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" name="params[0][norme]" placeholder="Norme" class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="button" onclick="removeParam(this)" class="px-4 py-3 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Supprimer</button>
                </div>
                @endforelse
            </div>
            <button type="button" onclick="addParam()" class="mt-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Ajouter Paramètre</button>
            @error('params') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('exams.show', $exam) }}" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</a>
            <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Mettre à jour</button>
        </div>
    </form>
</div>

<script>
    let paramIndex = {{ count($exam->params) }};
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
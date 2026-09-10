@extends('layouts.app')

@section('title', $exam->name)
@section('header', $exam->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-medium text-gray-800">{{ $exam->name }}</h1>
        <div class="space-x-2">
            <a href="{{ route('exams.edit', $exam) }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Éditer</a>
            <form method="POST" action="{{ route('exams.destroy', $exam) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50" onclick="return confirm('Supprimer ?')">Supprimer</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <p class="text-lg text-gray-900">{{ $exam->type }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <p class="text-lg text-gray-900">{{ $exam->name }}</p>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Paramètres ({{ count($exam->params) }})</label>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paramètre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Norme</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($exam->params as $param)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $param['param'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $param['norme'] ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">Aucun paramètre.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
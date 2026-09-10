@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-xl font-medium text-gray-800">Paramètres</h1>
    <form class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du Laboratoire</label>
            <input type="text" value="Grace Divine Labo" class="w-full p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
            <input type="text" value="123 Rue de la Santé, Ville" class="w-full p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo (URL)</label>
            <input type="url" value="" class="w-full p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500" placeholder="https://example.com/logo.png">
        </div>
        <div class="flex justify-end space-x-3">
            <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Annuler</button>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">Sauvegarder</button>
        </div>
    </form>
</div>
@endsection
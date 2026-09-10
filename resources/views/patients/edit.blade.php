@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-xl font-medium text-gray-800">Modifier Patient</h1>
    
    <form method="POST" action="{{ route('patients.update', $patient) }}" class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                <input type="text" name="name" value="{{ old('name', $patient->name) }}" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
                <input type="date" name="dob" value="{{ old('dob', $patient->dob) }}"  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                @error('dob') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sexe</label>
                <select name="sex" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sélectionnez le sexe</option>
                    <option value="Masculin" {{ old('sex', $patient->sex) == 'Masculin' ? 'selected' : '' }}>Masculin</option>
                    <option value="Féminin" {{ old('sex', $patient->sex) == 'Féminin' ? 'selected' : '' }}>Féminin</option>
                </select>
                @error('sex') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de dossier</label>
                <input type="text" 
                       value="{{ $patient->dossier_number }}"
                       disabled
                       class="w-full p-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-500 cursor-not-allowed">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
            <textarea name="address" rows="3" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $patient->address) }}</textarea>
            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        
        <div class="flex justify-end space-x-3">
            <a href="{{ route('patients.show', $patient) }}" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</a>
            <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Mettre à jour</button>
        </div>
    </form>
</div>
@endsection
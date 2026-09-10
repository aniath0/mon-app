@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-xl font-medium text-gray-800">Vérifier un Résultat</h1>
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 max-w-md mx-auto">
        <form method="POST" action="{{ route('verifier.check') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code ou QR Code</label>
                <input type="text" name="code" class="w-full p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500" placeholder="Saisir le code LAB-XXXX...">
            </div>
            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">Vérifier Authenticité</button>
        </form>
        <div class="mt-4 p-3 bg-green-50 rounded text-sm text-green-700 hidden" id="result">Authentique !</div>
        <div class="mt-4 p-3 bg-red-50 rounded text-sm text-red-700 hidden" id="error">Falsifié ou inexistant.</div>
    </div>
</div>

<script>
    // Simple JS pour afficher résultat (hooke au submit)
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        // Stub pour test : simule check
        const code = document.querySelector('input[name="code"]').value;
        if (code.includes('VALID')) {
            document.getElementById('result').classList.remove('hidden');
            document.getElementById('error').classList.add('hidden');
        } else {
            document.getElementById('error').classList.remove('hidden');
            document.getElementById('result').classList.add('hidden');
        }
    });
</script>
@endsection
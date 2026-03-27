@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Nouveau Résultat</h1>

    <form method="POST" action="{{ route('resultats.store') }}" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
        @csrf

        {{-- Numéro résultat auto --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de Résultat</label>
            <input type="text" value="LAB-{{ str_pad($nextResultNumber, 4, '0', STR_PAD_LEFT) }}" readonly class="w-full p-3 border rounded-lg bg-gray-100 text-gray-500">
        </div>

        {{-- Patient --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Patient</label>
            <select name="patient_id" required class="w-full p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Sélectionnez un patient</option>
                @foreach($patients as $patient)
                    <option value="{{ $patient->id }}">{{ $patient->name }} ({{ $patient->dossier_number }})</option>
                @endforeach
            </select>
        </div>

        {{-- Examens --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Examens</label>
            <select name="exam_ids[]" id="exam_ids" multiple required class="w-full p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-1">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs examens.</p>
        </div>

        {{-- Tableaux dynamiques --}}
        <div id="tableau-container" class="hidden space-y-6"></div>

        {{-- Observation générale --}}
        <div id="global-observation" class="mb-6 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Observation générale</label>
            <textarea name="global_observation" rows="3" class="w-full p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Conclusion globale de l’examen"></textarea>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end pt-4 border-t">
            <button type="submit" id="submit-btn" disabled class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Enregistrer & Générer PDF</button>
        </div>
    </form>
</div>

<script>
const examSelect = document.getElementById('exam_ids');
const container = document.getElementById('tableau-container');
const submitBtn = document.getElementById('submit-btn');
const globalObs = document.getElementById('global-observation');

examSelect.addEventListener('change', async () => {
    container.innerHTML = ''; // reset
    const selected = Array.from(examSelect.selectedOptions).map(opt => opt.value);

    if(selected.length === 0){
        container.classList.add('hidden');
        submitBtn.disabled = true;
        globalObs.classList.add('hidden');
        return;
    }

    globalObs.classList.remove('hidden');

    for(const examId of selected){
        try{
            const res = await fetch(`/exams/${examId}/params`);
            const data = await res.json();

            const tableDiv = document.createElement('div');
            tableDiv.classList.add('bg-gray-50', 'rounded-lg', 'shadow-sm', 'overflow-hidden');

            // Gestion spéciale pour antibiogramme et spermogramme
            if (['spermogramme', 'atb'].includes(data.type)) {
                tableDiv.innerHTML = `
                    <div class="bg-indigo-100 px-4 py-3 font-semibold text-gray-700 border-b">${data.name} (Antibiogramme)</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-indigo-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Antibiotique</th>
                                    <th class="px-4 py-2 text-center">Sensible</th>
                                    <th class="px-4 py-2 text-center">Intermédiaire</th>
                                    <th class="px-4 py-2 text-center">Résistant</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${Array.from({length: 10}).map((_,i) => `
                                    <tr>
                                        <td class="px-4 py-2">
                                            <input type="text" 
                                                   name="exams[${examId}][antibiotics][${i}][name]" 
                                                   class="w-full p-2 border rounded-lg"
                                                   placeholder="Nom de l'antibiotique">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="radio" name="exams[${examId}][antibiotics][${i}][result]" value="S">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="radio" name="exams[${examId}][antibiotics][${i}][result]" value="I">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="radio" name="exams[${examId}][antibiotics][${i}][result]" value="R">
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                // Pour les examens normaux
                tableDiv.innerHTML = `
                    <div class="bg-indigo-100 px-4 py-3 font-semibold text-gray-700 border-b">${data.name}</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-indigo-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Paramètre</th>
                                    <th class="px-4 py-2 text-center">Résultat</th>
                                    <th class="px-4 py-2 text-center">Norme</th>
                                    <th class="px-4 py-2 text-center">Observation</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${data.params.map((row,i) => `
                                    <tr>
                                        <td class="px-4 py-2 font-medium">${row.param || 'Paramètre ' + (i+1)}</td>
                                        <td class="px-4 py-2">
                                            <input type="text" 
                                                   name="exams[${examId}][${i}][resultat]" 
                                                   class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                                   placeholder="Saisir résultat">
                                        </td>
                                        <td class="px-4 py-2 text-gray-600 text-center">${row.norme || '-'}</td>
                                        <td class="px-4 py-2">
                                            <input type="text" 
                                                   name="exams[${examId}][${i}][observation]" 
                                                   class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                                   placeholder="Observation optionnelle">
                                        </td>
                                    </tr>
                                `).join('')}
                                
                                ${data.params.length === 0 ? `
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                            Aucun paramètre défini pour cet examen. 
                                            <a href="/exams/${examId}/edit" class="text-blue-600 hover:underline">
                                                Définir des paramètres
                                            </a>
                                        </td>
                                    </tr>
                                ` : ''}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            container.appendChild(tableDiv);
        } catch(err) {
            console.error(err);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'bg-red-100 text-red-700 p-3 rounded-lg mb-3';
            errorDiv.textContent = 'Erreur lors du chargement des paramètres de l\'examen ID: ' + examId;
            container.appendChild(errorDiv);
        }
    }

    container.classList.remove('hidden');
    submitBtn.disabled = false;
});
</script>
@endsection

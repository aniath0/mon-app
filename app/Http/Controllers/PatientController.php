<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $patients = Patient::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('dossier_number', 'like', "%{$search}%");
        })->latest()->paginate(10);

        return view('patients.index', compact('patients', 'search'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'dob' => 'nullable|date', // ici nullable
        'sex' => 'required|string|in:Masculin,Féminin',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:20',
    ]);

    // Création du patient
    $lastPatient = Patient::latest('id')->first();
    $nextNumber = $lastPatient ? $lastPatient->id + 1 : 1;
    $dossier = 'PAT-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

    Patient::create([
        'name' => $request->name,
        'dob' => $request->dob,  // peut être null
        'sex' => $request->sex,
        'dossier_number' => $dossier,
        'address' => $request->address,
        'phone' => $request->phone,
    ]);

    return redirect()->route('patients.index')
        ->with('success', "Patient ajouté 🩺 | Dossier : $dossier");
}

public function update(Request $request, Patient $patient)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'dob' => 'nullable|date', // ici nullable
        'sex' => 'required|string|in:Masculin,Féminin',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:20',
    ]);

    $patient->update($request->only([
        'name', 'dob', 'sex', 'address', 'phone'
    ]));

    return redirect()->route('patients.show', $patient)
        ->with('success', 'Patient mis à jour ✨');
}

    public function show(Patient $patient)
    {
        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Patient supprimé.');
    }
}

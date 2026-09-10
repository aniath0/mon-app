<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ResultController;

/*
|--------------------------------------------------------------------------
| Route racine → login
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Routes protégées (auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [ResultController::class, 'dashboard'])
        ->name('dashboard');

    // Profil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Examens
    Route::resource('exams', ExamController::class);

    // Fetch params d’un examen (AJAX)
    Route::get('/exams/{exam}/params', function (App\Models\Exam $exam) {
        return response()->json([
            'name'   => $exam->name,
            'params' => $exam->params,
        ]);
    });

    // Patients
    Route::resource('patients', PatientController::class);

    // Résultats
    Route::get('/resultats', [ResultController::class, 'index'])->name('resultats.index');
    Route::get('/resultats/create', [ResultController::class, 'create'])->name('resultats.create');
    Route::post('/resultats', [ResultController::class, 'store'])->name('resultats.store');

    // Validation résultat
    Route::post('/resultats/{id}/validate', function ($id) {
        session()->flash('success', 'Résultat validé !');
        return redirect()->route('resultats.index');
    })->name('resultats.validate');

    // Vérification QR / Code
    Route::get('/verifier', function () {
        return view('verifier');
    })->name('verifier');

    Route::post('/verifier/check', function (Request $request) {
        $code = $request->input('code');

        if ($code === 'TEST-VALID') {
            return response()->json(['status' => 'Authentique']);
        }

        return response()->json(['status' => 'Falsifié']);
    })->name('verifier.check');

    // Paramètres
    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');
});

/*
|--------------------------------------------------------------------------
| Auth routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

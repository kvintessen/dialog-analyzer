<?php

use App\Http\Controllers\AnalysisRuleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DialogAnalysisController;
use App\Http\Controllers\DialogController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dialogs', [DialogController::class, 'index'])->name('dialogs.index');
    Route::get('/dialogs/{dialog}', [DialogController::class, 'show'])->name('dialogs.show');
    Route::post('/dialogs/{dialog}/analyze', [DialogAnalysisController::class, 'store'])->name('dialogs.analyze');

    Route::get('/analysis-rules', [AnalysisRuleController::class, 'index'])->name('analysis-rules.index');
    Route::patch('/analysis-rules/{analysisRule}', [AnalysisRuleController::class, 'update'])->name('analysis-rules.update');
});

require __DIR__.'/auth.php';

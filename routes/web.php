<?php

use App\Http\Controllers\EvolutionSettingController;
use App\Http\Controllers\MessageAutomationController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('people', PersonController::class);
    Route::resource('message-templates', MessageTemplateController::class);
    Route::resource('message-automations', MessageAutomationController::class);
    Route::resource('evolution-settings', EvolutionSettingController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

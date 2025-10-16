<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\MembershipPlanController; // import the controller
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Redirects '/admin' to the plans list page for convenience
        Route::get('/', function () {
            return redirect()->route('admin.plans.index');
        });

        Route::resource('plans', MembershipPlanController::class);
});

require __DIR__.'/auth.php';

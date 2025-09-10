<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\MeterCategoryController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

});

Route::middleware('auth')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [UserController::class, 'showDashboard'])->name('users.dashboard');

    // Manage Generators Route
    Route::get('/manage-generators', [UserController::class, 'manageGenerators'])->name('manage.generators');
    // Manage Prices Route
    Route::get('/manage-prices', [UserController::class, 'managePrices'])->name('manage.prices');

});


Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
    Route::post('/users/create', [AdminController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/restore', [AdminController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{user}/force-delete', [AdminController::class, 'forceDelete'])->name('users.forceDelete');
});


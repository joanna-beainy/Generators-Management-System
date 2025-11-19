<?php

use App\Livewire\ShowClients;
use App\Livewire\MaintenanceList;
use App\Livewire\MaintenanceEntry;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;


Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/', [AuthController::class, 'login']);

});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [UserController::class, 'showDashboard'])->name('users.dashboard');
    Route::get('/user-profile', [UserController::class, 'userProfile'])->name('user.profile');

    // Manage Generators Route
    Route::get('/manage-generators', [UserController::class, 'manageGenerators'])->name('manage.generators');
    // Manage Prices Route
    Route::get('/manage-prices', [UserController::class, 'managePrices'])->name('manage.prices');


    //Clients Routes
    Route::get('/clients', [UserController::class, 'ClientsIndex'])->name('clients.index');
    Route::get('/clients/create', [UserController::class, 'createClient'])->name('clients.create');

    // Meter Readings Route
    Route::get('/meter-readings', [UserController::class, 'meterReadings'])->name('meter.readings');
    Route::get('/client-meter-readings/{clientId}', [UserController::class, 'clientMeterReadings'])->name('client.meter.readings');

    // Payments Route
    Route::get('/payment-entry', [UserController::class, 'paymentEntry'])->name('payment.entry');
    Route::get('/payment-history/{clientId?}', [UserController::class, 'paymentHistory'])->name('payment.history');
    Route::get('/monthly-payment-report', [UserController::class, 'monthlyPaymentReport'])->name('monthly.payment.report');

    // Maintenance Routes
    Route::get('/maintenance-entry', [UserController::class, 'maintenanceEntry'])->name('maintenance.entry');
    Route::get('/maintenance-list/{clientId}', [UserController::class, 'maintenanceList'])->name('maintenance.list');

    // Monthly Client Report Route
    Route::get('/monthly-client-report', [UserController::class, 'monthlyClientReport'])->name('meter-readings.monthly-report');

    // Meter Reading Form Report Route
    Route::get('/meter-reading-form-report', [UserController::class, 'meterReadingFormReport'])->name('meter-reading.form-report');

    // Outstanding Amounts Report Route
    Route::get('/outstanding-amounts-report', [UserController::class, 'outstandingAmountsReport'])->name('outstanding.amounts.report');

    //Fuel purchase Report Route
    Route::get('/fuel-purchase-report', [UserController::class, 'fuelPurchaseReport'])->name('fuel.purchase.report');

    //Fuel Consumption Report Route
    Route::get('/fuel-consumption-report', [UserController::class, 'fuelConsumptionReport'])->name('fuel.consumption.report');

});


Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
    Route::post('/users/create', [AdminController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/restore', [AdminController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{user}/force-delete', [AdminController::class, 'forceDelete'])->name('users.forceDelete');
});


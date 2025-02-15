<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('flights.index');
});

Route::get('/database', function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed');

    return redirect()->back()->with('success', 'Database migrated and seeded successfully!');
})->name('migrate');

Route::middleware(['auth'])->group(function () {
    Route::get('airlines', App\Livewire\Airline\Index::class)->name('airlines.index');
    Route::get('airlines/{airline}', App\Livewire\Airline\Show::class)->name('airlines.show');
    Route::get('aircraft_types', App\Livewire\AircraftType\Manager::class)->name('aircraft_types.index');
    Route::get('aircraft_types/{aircraft_type}', App\Livewire\AircraftType\Show::class)->name('aircraft_types.show');
    Route::get('flights', App\Livewire\Flight\FlightManager::class)->name('flights.index');
    Route::get('flights/{flight}', App\Livewire\Flight\Show::class)->name('flights.show');
    Route::get('flights/{flight}/containers', App\Livewire\Container\Manager::class)->name('flights.containers');
    Route::get('containers', App\Livewire\Container\Manager::class)->name('containers.index');
    Route::get('crews', App\Livewire\Crew\Manager::class)->name('crews.index');
});

Route::middleware(['auth', 'role:super-admin|admin|user'])->prefix('admin')->group(function () {
    Route::get('/users', App\Livewire\User\Manager::class)->name('admin.users');
    Route::get('/roles', App\Livewire\Role\Manager::class)->name('admin.roles');
    Route::get('/permissions', App\Livewire\Permission\Manager::class)->name('admin.permissions');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Auth::routes();
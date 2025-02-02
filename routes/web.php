<?php

use App\Http\Controllers\FuelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('airlines.index');
});


Route::get('/database', function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed');

    return redirect()->back()->with('success', 'Database migrated and seeded successfully!');
})->name('migrate');

// Public routes
Route::get('airlines', App\Livewire\Airline\Index::class)->name('airlines.index');
Route::get('aircraft_types', App\Livewire\AircraftType\Manager::class)->name('aircraft_types.index');

// Airline-specific routes
Route::get('airlines/{airline}', App\Livewire\Airline\Show::class)->name('airlines.show');

// Routes requiring selected airline
Route::middleware('selected.airline')->group(function () {
    Route::get('aircraft_types/{aircraft_type}', App\Livewire\AircraftType\Show::class)->name('aircraft_types.show');
    Route::get('flights', App\Livewire\Flight\Index::class)->name('flights.index');
    Route::get('flights/create', App\Livewire\Flight\Form::class)->name('flights.create');
    Route::get('flights/{flight}/edit', App\Livewire\Flight\Form::class)->name('flights.edit');
    Route::get('flights/{flight}', App\Livewire\Flight\Show::class)->name('flights.show');
    Route::get('flights/{flight}/weight-balance', App\Livewire\Flight\WeightBalance::class)->name('flights.weight-balance');
    Route::get('flights/{flight}/containers', App\Livewire\Flight\Container::class)->name('flights.containers');

    Route::get('cargo', App\Livewire\Cargo\Manager::class)->name('cargo.index');
    Route::get('fuel', App\Livewire\Fuel\Manager::class)->name('fuel.index');
    Route::get('containers', App\Livewire\Container\Manager::class)->name('containers.index');
    Route::get('passengers', App\Livewire\Passenger\Manager::class)->name('passengers.index');
    Route::get('crews', App\Livewire\Crew\Manager::class)->name('crews.index');
});
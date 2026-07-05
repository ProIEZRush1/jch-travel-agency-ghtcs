<?php

use App\Http\Controllers\AutoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\PaqueteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WhatsAppController;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::get('/health', function () {
    try {
        return response()->json(['ok' => true, 'users' => \App\Models\User::count()]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => 'db'], 503);
    }
});

// Público — meta-buscador de autos JCH (sin login), consulta AgentCars en vivo.
Route::get('/autos', [AutoController::class, 'index'])->name('autos.index');
Route::get('/autos/sugerencias', [AutoController::class, 'sugerencias'])->name('autos.sugerencias');
Route::get('/autos/buscar', [AutoController::class, 'buscar'])->name('autos.buscar');

// Público — buscador de hoteles JCH (sin login), igual que /autos.
Route::get('/hoteles', [HotelController::class, 'index'])->name('hoteles.index');
Route::get('/hoteles/sugerencias', [HotelController::class, 'sugerencias'])->name('hoteles.sugerencias');
Route::get('/hoteles/buscar', [HotelController::class, 'buscar'])->name('hoteles.buscar');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'clientes'    => Cliente::count(),
            'cotizaciones' => Pedido::whereMonth('created_at', now()->month)->count(),
            'confirmadas' => Pedido::where('estado', 'confirmado')->whereMonth('created_at', now()->month)->count(),
            'servicios'   => Plan::where('activo', true)->count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/conectar', [WhatsAppController::class, 'conectar'])->name('conectar');

    Route::get('/autos/historial', [AutoController::class, 'historial'])->name('autos.historial');

    Route::resource('paquetes', PaqueteController::class);
    Route::resource('clientes', ClienteController::class)->only(['index', 'show', 'destroy']);
    Route::resource('cotizaciones', CotizacionController::class)->only(['index', 'show', 'update', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

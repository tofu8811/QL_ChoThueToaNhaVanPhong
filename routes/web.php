<?php

use App\Http\Controllers\Web\PortalWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalWebController::class, 'index'])->name('home');
Route::get('toa-nha/{slug}', [PortalWebController::class, 'showBuilding'])->name('buildings.show');

Route::get('docs', function () {
    $path = storage_path('api-docs/api-docs.json');
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->json(json_decode(file_get_contents($path), true));
});

Route::get('api/documentation', function () {
    return response('<!DOCTYPE html><html><head><title>API Documentation</title></head><body><h1>API Documentation</h1></body></html>', 200)
        ->header('Content-Type', 'text/html');
});

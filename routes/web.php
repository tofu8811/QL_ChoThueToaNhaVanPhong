<?php

use App\Http\Controllers\Web\PortalWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalWebController::class, 'index'])->name('home');
Route::get('toa-nha/{slug}', [PortalWebController::class, 'showBuilding'])->name('buildings.show');
Route::get('nhan-bao-gia', [PortalWebController::class, 'quotePage'])->name('quote.page');
Route::get('quan-ly-hop-dong', [PortalWebController::class, 'contractsPage'])->name('contracts.page');
Route::get('khach-thue', [PortalWebController::class, 'tenantsPage'])->name('tenants.page');
Route::get('cong-khach-thue', [PortalWebController::class, 'tenantPortalPage'])->name('tenant-portal.page');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\TransactionController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports.index');
    Route::get('/reports/create', [DashboardController::class, 'createTransaction'])->name('reports.create');
    Route::post('/reports', [DashboardController::class, 'storeTransaction'])->name('reports.store');
    Route::get('/reports/export', [DashboardController::class, 'exportReports'])->name('reports.export');
    Route::get('/reports/print', [DashboardController::class, 'printReport'])->name('reports.print');
    Route::get('/reports/{id}', [DashboardController::class, 'reportDetail'])->name('reports.detail');
    Route::get('/geospatial', [DashboardController::class, 'geospatial']);
    Route::get('/audit', [DashboardController::class, 'auditTrail']);
    Route::get('/users', [DashboardController::class, 'users']);
    Route::post('/users', [DashboardController::class, 'storeUser']);
    Route::get('/ministries', [DashboardController::class, 'ministries']);
    Route::get('/ministries/{id}', [DashboardController::class, 'ministryDetail'])->name('ministries.detail');
    Route::get('/approvals', [DashboardController::class, 'approvals']);
    Route::post('/approvals/{id}', [DashboardController::class, 'approveTransaction'])->name('approvals.action');
    Route::get('/api-gateway', [\App\Http\Controllers\ApiGatewayController::class, 'index'])->name('api.gateway');
    Route::post('/api-gateway', [\App\Http\Controllers\ApiGatewayController::class, 'generate'])->name('api.gateway.generate');

    // API Endpoints for Real-Time Ticker (Now Secured)
    Route::get('/api/live-ticker', [TransactionController::class, 'liveTicker']);
    Route::get('/api/reports-metrics', [TransactionController::class, 'reportsMetrics']);
    Route::get('/api/simulate-transaction', [TransactionController::class, 'simulate']);
    Route::post('/api/chat', [\App\Http\Controllers\Api\CopilotController::class, 'chat']);
});

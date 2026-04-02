<?php

use App\Http\Controllers\Client\ActionPlanController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DepartmentController;
use App\Http\Controllers\Client\ComplaintController;
use App\Http\Controllers\Client\ExportController;
use App\Http\Controllers\Client\ImportController;
use App\Http\Controllers\Client\TrainingController;
use App\Http\Controllers\Client\PositionController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\Client\SurveyController;
use App\Http\Controllers\Client\SurveyResultsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'company'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('positions', PositionController::class)->except(['show']);
    Route::post('import/departments', [ImportController::class, 'departments'])->name('import.departments');
    Route::resource('surveys', SurveyController::class);
    Route::get('surveys/{survey}/results', [SurveyResultsController::class, 'show'])->name('surveys.results');
    Route::post('surveys/{survey}/ai-analysis', [SurveyResultsController::class, 'generateAiAnalysis'])->name('surveys.ai-analysis');
    Route::post('surveys/{survey}/recalculate', [SurveyResultsController::class, 'recalculate'])->name('surveys.recalculate');
    Route::get('surveys/{survey}/action-plan', [ActionPlanController::class, 'show'])->name('surveys.action-plan');
    Route::post('surveys/{survey}/action-plan/generate', [ActionPlanController::class, 'generate'])->name('surveys.action-plan.generate');
    Route::patch('action-plan-items/{item}', [ActionPlanController::class, 'updateItem'])->name('action-plan-items.update');
    Route::get('surveys/{survey}/reports/executive', [ReportController::class, 'executive'])->name('surveys.reports.executive');
    Route::get('surveys/{survey}/reports/technical', [ReportController::class, 'technical'])->name('surveys.reports.technical');
    Route::get('surveys/{survey}/export/json', [ExportController::class, 'json'])->name('surveys.export.json');
    Route::get('surveys/{survey}/export/csv', [ExportController::class, 'csv'])->name('surveys.export.csv');

    Route::get('capacitacao', [TrainingController::class, 'index'])->name('training.index');
    Route::get('complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::patch('complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.status');
    Route::post('complaints/{complaint}/messages', [ComplaintController::class, 'storeMessage'])->name('complaints.messages.store');
});

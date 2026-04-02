<?php

use App\Http\Controllers\Complaint\PublicComplaintController;
use Illuminate\Support\Facades\Route;

Route::prefix('denuncia')->name('denuncia.')->group(function () {
    Route::get('{token}/obrigado/{protocol}', [PublicComplaintController::class, 'thanks'])->name('thanks');
    Route::get('{token}/acompanhar', [PublicComplaintController::class, 'track'])->name('track');
    Route::post('{token}/acompanhar', [PublicComplaintController::class, 'trackLookup'])->name('track.lookup');
    Route::get('{token}/p/{protocol}', [PublicComplaintController::class, 'showProtocol'])->name('protocol');
    Route::post('{token}/p/{protocol}/mensagem', [PublicComplaintController::class, 'reporterMessage'])->name('reporter.message');
    Route::get('{token}', [PublicComplaintController::class, 'create'])->name('create');
    Route::post('{token}', [PublicComplaintController::class, 'store'])->name('store');
});

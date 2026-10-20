<?php

use App\Http\Controllers\Api\TelegramConnectionController;
use App\Http\Controllers\Api\TelegramServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    //
});

Route::post('/telegram/connect', [TelegramConnectionController::class, 'connect']);
Route::post('/telegram/login', [TelegramConnectionController::class, 'login']);
Route::post('/telegram/logout', [TelegramConnectionController::class, 'logout']);
Route::get('/telegram/user/{telegram_id}', [TelegramConnectionController::class, 'getUser']);

// WhatsApp Bot Routes
use App\Http\Controllers\Api\WhatsAppController;

Route::prefix('whatsapp')->group(function () {
    Route::post('/check-user', [WhatsAppController::class, 'checkUser']);
    Route::post('/login', [WhatsAppController::class, 'login']);
    Route::post('/logout', [WhatsAppController::class, 'logout']);
    Route::get('/services', [WhatsAppController::class, 'getDocumentServices']);
    
    // Persuratan
    Route::post('/documents', [WhatsAppController::class, 'createDocumentRequest']);
    Route::get('/documents/{whatsapp_number}', [WhatsAppController::class, 'getDocumentRequests']);
    Route::get('/documents/{id}/pdf', [WhatsAppController::class, 'downloadDocumentPdf']);
    
    // Pengaduan
    Route::post('/complaints', [WhatsAppController::class, 'createComplaint']);
    Route::get('/complaints/{whatsapp_number}', [WhatsAppController::class, 'getComplaints']);
    
    // Polling & Musyawarah
    Route::get('/polls/{whatsapp_number}', [WhatsAppController::class, 'getPolls']);
    Route::post('/polls/vote', [WhatsAppController::class, 'votePoll']);
});

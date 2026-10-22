<?php

use App\Http\Controllers\Api\TelegramConnectionController;
use App\Http\Controllers\Api\TelegramServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    //
});

Route::prefix('telegram')->group(function () {
    Route::post('/connect', [TelegramConnectionController::class, 'connect']);
    Route::post('/login', [TelegramConnectionController::class, 'login']);
    Route::post('/logout', [TelegramConnectionController::class, 'logout']);
    Route::get('/user/{telegram_id}', [TelegramConnectionController::class, 'getUser']);

    Route::get('/services', [TelegramServiceController::class, 'getDocumentServices']);
    Route::post('/documents', [TelegramServiceController::class, 'createDocumentRequest']);
    Route::get('/documents/{telegram_id}', [TelegramServiceController::class, 'getDocumentRequests']);
    Route::get('/documents/{id}/pdf', [TelegramServiceController::class, 'downloadDocumentPdf']);

    Route::post('/complaints', [TelegramServiceController::class, 'createComplaint']);
    Route::get('/complaints/{telegram_id}', [TelegramServiceController::class, 'getComplaints']);
});

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

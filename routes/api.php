<?php

use Essasabbagh\LaravelChat\Http\Controllers\AdminController;
use Essasabbagh\LaravelChat\Http\Controllers\AttachmentsController;
use Essasabbagh\LaravelChat\Http\Controllers\ConversationsController;
use Essasabbagh\LaravelChat\Http\Controllers\GroupsController;
use Essasabbagh\LaravelChat\Http\Controllers\MessagesController;
use Essasabbagh\LaravelChat\Http\Controllers\ReactionsController;
use Essasabbagh\LaravelChat\Http\Controllers\ReadsController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api/chat')->group(function () {
    Route::get('conversations', [ConversationsController::class, 'index']);
    Route::post('conversations', [ConversationsController::class, 'store']);
    Route::get('conversations/{conversation}', [ConversationsController::class, 'show']);
    Route::delete('conversations/{conversation}', [ConversationsController::class, 'destroy']);

    Route::get('conversations/{conversation}/messages', [MessagesController::class, 'index']);
    Route::post('conversations/{conversation}/messages', [MessagesController::class, 'store']);
    Route::get('conversations/{conversation}/messages/{message}', [MessagesController::class, 'show']);
    Route::delete('conversations/{conversation}/messages/{message}', [MessagesController::class, 'destroy']);

    Route::post('conversations/{conversation}/messages/{message}/attachments', [AttachmentsController::class, 'store']);
    Route::delete('conversations/{conversation}/messages/{message}/attachments/{attachment}', [AttachmentsController::class, 'destroy']);

    Route::post('conversations/{conversation}/messages/{message}/reactions', [ReactionsController::class, 'store']);
    Route::delete('conversations/{conversation}/messages/{message}/reactions/{reaction}', [ReactionsController::class, 'destroy']);

    Route::post('conversations/{conversation}/messages/{message}/read', [ReadsController::class, 'store']);
    Route::post('conversations/{conversation}/read-all', [ReadsController::class, 'markAllRead']);

    Route::post('conversations/{conversation}/members', [GroupsController::class, 'addMember']);
    Route::delete('conversations/{conversation}/members/{participant}', [GroupsController::class, 'removeMember']);
    Route::put('conversations/{conversation}/members/{participant}/role', [GroupsController::class, 'updateRole']);

    Route::post('admin/block', [AdminController::class, 'block']);
    Route::post('admin/unblock', [AdminController::class, 'unblock']);
    Route::post('admin/users/force-offline', [AdminController::class, 'forceOffline']);
    Route::delete('admin/conversations/{conversation}', [AdminController::class, 'deleteConversation']);
    Route::delete('admin/conversations/{conversation}/messages/{message}', [AdminController::class, 'deleteMessage']);
    Route::post('admin/users/status', [AdminController::class, 'changeStatus']);
});

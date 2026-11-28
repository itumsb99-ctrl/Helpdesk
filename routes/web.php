<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('tickets', TicketController::class)->only(['index','create','store','show']);
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->middleware('role:admin')->name('tickets.assign');
    Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->middleware('role:admin,teknisi')->name('tickets.status');
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'comment'])->name('tickets.comments');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->only(['index','create','store','edit','update']);
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::resource('categories', CategoryController::class)->except(['show','destroy']);
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::resource('units', UnitController::class)->except(['show','destroy']);
        Route::patch('/units/{unit}/toggle', [UnitController::class, 'toggle'])->name('units.toggle');
        Route::prefix('reports/tickets')->name('reports.tickets.')->group(function () { Route::get('/', [TicketReportController::class, 'index'])->name('index'); Route::get('/excel', [TicketReportController::class, 'excel'])->name('excel'); Route::get('/pdf', [TicketReportController::class, 'pdf'])->name('pdf'); });
    });

    Route::post('/tickets/{ticket}/attachments', [TicketAttachmentController::class, 'store'])->name('tickets.attachments.store');
    Route::get('/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [TicketAttachmentController::class, 'destroy'])->name('attachments.destroy');
});

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\KategoriNamaTabunganController;
use App\Http\Controllers\KategoriJenisTabunganController;
use App\Http\Controllers\PlannedTransactionController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\QuickCaptureController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ViewerController;
use App\Http\Controllers\DiscoveryController;
use Illuminate\Support\Facades\Http;

use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

// =======================
// Public Route (Termasuk Impersonate)
// =======================
Route::get('/', function () {
    return view('welcome');
});

Route::get('/cms/impersonate/{token}', function ($token) {
    $accessToken = PersonalAccessToken::findToken($token);
    
    if (!$accessToken || $accessToken->name !== 'impersonation-token') {
        abort(403, 'Invalid or expired impersonation token.');
    }

    $user = $accessToken->tokenable;
    
    // Login user ke session web
    Auth::login($user);
    
    // Hapus token setelah digunakan agar tidak bisa dipakai ulang
    $accessToken->delete();
    
    return redirect('/dashboard')->with('status', 'Berhasil masuk sebagai ' . $user->name);
})->name('cms.impersonate');

// =======================
// Dashboard Redirect (Universal setelah login)
// =======================
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $role = auth()->user()->role;
    return match ($role) {
        'dins' => redirect()->route('admin.dashboard'),
        'viewer' => redirect()->route('viewer.dashboard'),
        default => abort(403),
    };
})->name('dashboard');

// =======================
// Profile (Semua User)
// =======================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =======================
// Universal Tabungan Routes (Untuk semua role yang login)
// =======================
Route::middleware(['auth'])->group(function () {
    // Route tabungan index untuk semua role
    Route::get('/tabungan', [TabunganController::class, 'index'])->name('tabungan.index');
    Route::get('/wallets', [TabunganController::class, 'walletIndex'])->name('wallet.index');
    Route::get('/tabungan/wallet/{id}', function($id) {
        return redirect()->route('management.index', ['id' => $id]);
    })->name('tabungan.category.show');
    Route::get('/help', function () {
        return view('help');
    })->name('help');

    Route::get('/notification', [NotificationController::class, 'index'])->name('notification.index');
    Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction.index');
    Route::get('/transaction/create', [TransactionController::class, 'create'])->name('transaction.create');
    Route::post('/transaction', [TransactionController::class, 'store'])->name('transaction.store');

    Route::post('/quick-capture', [QuickCaptureController::class, 'store'])->name('quick-capture.store');
    
    // Search Feature
    Route::get('/search', [SearchController::class, 'query'])->name('search.query');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::post('/search/log', [SearchController::class, 'log'])->name('search.log');
    
    // Export routes
    Route::get('/tabungan/export/excel', [TabunganController::class, 'exportExcel'])->name('tabungan.export.excel');
    Route::get('/tabungan/export/pdf', [TabunganController::class, 'exportPdf'])->name('tabungan.export.pdf');
    
    // Route khusus harus ditempatkan SEBELUM resource route
    // Route untuk fitur sampah (hanya untuk dins, tapi ditempatkan di sini untuk urutan)
    Route::middleware('role:dins')->group(function () {
        Route::get('/tabungan/trash', [TabunganController::class, 'trash'])->name('tabungan.trash');
        Route::patch('/tabungan/restore/{id}', [TabunganController::class, 'restore'])->name('tabungan.restore');
        Route::delete('/tabungan/force-delete/{id}', [TabunganController::class, 'forceDelete'])->name('tabungan.force-delete');
        Route::patch('/tabungan/restore-all', [TabunganController::class, 'restoreAll'])->name('tabungan.restore-all');
        Route::delete('/tabungan/empty-trash', [TabunganController::class, 'emptyTrash'])->name('tabungan.empty-trash');
    });
    
    // Resource route untuk tabungan (ditempatkan setelah route khusus)
    Route::resource('tabungan', TabunganController::class)->except(['index', 'create'])->names('tabungan');
});

// =======================
// Viewer Routes
// =======================
Route::middleware(['auth', 'role:viewer'])->group(function () {
    Route::get('/viewer/dashboard', [ViewerController::class, 'index'])->name('viewer.dashboard');
});

// =======================
// Dins (Admin) Routes
// =======================
Route::middleware(['auth', 'role:dins'])->group(function () {
    // Dashboard Admin
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/ai-assistant', [DiscoveryController::class, 'aiAssistant'])->name('ai.assistant');
    
    // Backup
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup/download', [BackupController::class, 'download'])->name('backup.download');
    
    // Agenda & Tasks (Replacement for Planned Transactions)
    Route::resource('agenda', AgendaController::class)->names([
        'index' => 'agenda.index',
        'create' => 'agenda.create',
        'store' => 'agenda.store',
        'show' => 'agenda.show',
        'edit' => 'agenda.edit',
        'update' => 'agenda.update',
        'destroy' => 'agenda.destroy',
    ]);
    Route::post('/agenda/{id}/complete', [AgendaController::class, 'complete'])->name('agenda.complete');
    Route::post('/agenda/bulk-delete', [AgendaController::class, 'bulkDelete'])->name('agenda.bulk-delete');
    Route::patch('/agenda/{id}/timeline', [AgendaController::class, 'toggleTimeline'])->name('agenda.timeline.toggle');
    
    // Unified Management System (Pos, Dompet, Tabungan)
    Route::prefix('manajemen')->group(function () {
        Route::get('/', [ManagementController::class, 'index'])->name('management.index');
        
        // Redirect old specific routes to the new unified index with type param
        Route::get('/pos', fn() => redirect()->route('management.index', ['type' => 'pos']))->name('management.pos');
        Route::get('/dompet', fn() => redirect()->route('management.index', ['type' => 'dompet']))->name('management.dompet');
        Route::get('/tabungan', fn() => redirect()->route('management.index', ['type' => 'tabungan']))->name('management.tabungan');
        
        // CRUD for categories
        Route::get('/create', [ManagementController::class, 'create'])->name('management.create');
        Route::post('/store', [ManagementController::class, 'store'])->name('management.store');
        Route::get('/store', fn() => redirect()->route('management.create')); // Safety redirect for GET on store
        Route::get('/{id}/edit', [ManagementController::class, 'edit'])->name('management.edit');
        Route::put('/{id}', [ManagementController::class, 'update'])->name('management.update');
        Route::patch('/{id}/update-name', [ManagementController::class, 'updateName'])->name('management.update-name');
        Route::delete('/{id}', [ManagementController::class, 'destroy'])->name('management.destroy');
    });

    // Master Kategori Jenis (Keep for now or move)
    Route::resource('kategori-jenis-tabungan', KategoriJenisTabunganController::class)->names('kategori.jenis');

    // Analisis Reports
    Route::get('/reports/analysis', [ReportController::class, 'categoryAnalysis'])->name('reports.analysis');

    // AI Features (Moved to DiscoveryController)
    Route::get('/ai/scan-struk', [DiscoveryController::class, 'aiScan'])->name('ai.scan-struk');
    Route::get('/ai/voice', [DiscoveryController::class, 'aiVoice'])->name('ai.voice');
    Route::post('/ai/process', [DiscoveryController::class, 'processAI'])->name('ai.process');
    Route::post('/ai/scan-receipt', [DiscoveryController::class, 'processReceipt'])->name('ai.scan-receipt');
    Route::post('/ai/process-voice', [DiscoveryController::class, 'processVoice'])->name('ai.process-voice');
    Route::post('/ai/save-transaction', [DiscoveryController::class, 'saveAiTransaction'])->name('ai.save-transaction');

    // Gamification & Interactive Hub
    Route::post('/check-in', [App\Http\Controllers\AdminController::class, 'checkIn'])->name('user.check-in');
    Route::patch('/agenda/{id}/toggle', [AdminController::class, 'toggleAgenda'])->name('agenda.toggle');
    Route::get('/eksplor', [DiscoveryController::class, 'index'])->name('eksplor.index');
});

// =======================
// Auth Routes
// =======================
require __DIR__ . '/auth.php';
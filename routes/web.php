<?php

use App\Livewire\Accounting\Index as AccountingIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Customers\Create as CustomersCreate;
use App\Livewire\Customers\Edit as CustomersEdit;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Dashboard;
use App\Livewire\Documents\VerifyImport;
use App\Livewire\Invoices\Create as InvoicesCreate;
use App\Livewire\Invoices\Edit as InvoicesEdit;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Quotes\Create as QuotesCreate;
use App\Livewire\Quotes\Edit as QuotesEdit;
use App\Livewire\Quotes\Index as QuotesIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Transactions\VerifyImports;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes
Route::get('/login', Login::class)->name('login');
Route::post('/logout', function () {
    session()->forget('authenticated');

    return redirect()->route('login');
})->name('logout');

// Protected routes
Route::middleware(['auth.simple'])->group(function () {
    // Dashboard
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Document verification (shared across invoices, quotes, expenses)
    Route::get('/documents/verify-import', VerifyImport::class)->name('documents.verify-import');

    // Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', InvoicesIndex::class)->name('index');
        Route::get('/create', InvoicesCreate::class)->name('create');
        Route::get('/{invoice}/edit', InvoicesEdit::class)->name('edit');
        Route::get('/import', \App\Livewire\Invoices\Import::class)->name('import');
        Route::get('/{invoice}/preview-html', [\App\Http\Controllers\InvoicePreviewController::class, 'showHtml'])->name('preview-html');
        Route::get('/{invoice}/preview-pdf', [\App\Http\Controllers\InvoicePreviewController::class, 'showPdf'])->name('preview-pdf');
    });

    // Preview routes for dummy invoice (branding settings)
    Route::get('/preview/invoice-html', [\App\Http\Controllers\InvoicePreviewController::class, 'previewHtml'])->name('preview.invoice.html');
    Route::get('/preview/invoice-pdf', [\App\Http\Controllers\InvoicePreviewController::class, 'previewPdf'])->name('preview.invoice.pdf');

    // Quotes
    Route::prefix('quotes')->name('quotes.')->group(function () {
        Route::get('/', QuotesIndex::class)->name('index');
        Route::get('/create', QuotesCreate::class)->name('create');
        Route::get('/{quote}/edit', QuotesEdit::class)->name('edit');
        Route::get('/import', \App\Livewire\Quotes\Import::class)->name('import');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', CustomersIndex::class)->name('index');
        Route::get('/create', CustomersCreate::class)->name('create');
        Route::get('/{customer}/edit', CustomersEdit::class)->name('edit');
    });

    // Accounting
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/', AccountingIndex::class)->name('index');
    });

    // Transactions
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/verify-imports', VerifyImports::class)->name('verify-imports');
    });

    // Paperless proxy routes (for authenticated thumbnail/preview requests)
    Route::prefix('paperless')->name('paperless.')->group(function () {
        Route::get('/documents/{documentId}/thumbnail', [\App\Http\Controllers\PaperlessProxyController::class, 'thumbnail'])->name('thumbnail');
        Route::get('/documents/{documentId}/preview', [\App\Http\Controllers\PaperlessProxyController::class, 'preview'])->name('preview');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', ReportsIndex::class)->name('index');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', SettingsIndex::class)->name('index');
    });
});

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaptivePortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Organization\AdminDashboardController;
use App\Http\Controllers\Organization\CampaignController;
use App\Http\Controllers\Organization\CustomerReportController;
use App\Http\Controllers\Organization\GuestController;
use App\Http\Controllers\Organization\PortalController;
use App\Http\Controllers\Organization\ResponseController;
use App\Http\Controllers\Organization\SettingsController;
use App\Http\Controllers\Organization\SurveyQuestionController;
use App\Http\Controllers\Organization\SurveyTemplateController;
use App\Http\Controllers\SuperAdmin\OrganizationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard');

Route::prefix('superadmin')
    ->name('superadmin.')
    ->middleware(['auth', 'role:superadmin'])
    ->group(function () {
        Route::resource('organizations', OrganizationController::class);
    });

Route::prefix('organization')
    ->name('organization.')
    ->middleware(['auth', 'role:org_admin'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::resource('portals', PortalController::class);
        Route::patch('/portals/{portal}/template', [PortalController::class, 'updateTemplate'])->name('portals.template.update');
        Route::resource('surveys', SurveyTemplateController::class);
        Route::post('/surveys/{survey}/questions', [SurveyQuestionController::class, 'store'])->name('surveys.questions.store');
        Route::patch('/surveys/{survey}/questions/{question}', [SurveyQuestionController::class, 'update'])->name('surveys.questions.update');
        Route::delete('/surveys/{survey}/questions/{question}', [SurveyQuestionController::class, 'destroy'])->name('surveys.questions.destroy');

        Route::resource('campaigns', CampaignController::class);
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
        Route::get('/guests/{guest}', [GuestController::class, 'show'])->name('guests.show');
        Route::get('/reports/customers', [CustomerReportController::class, 'index'])->name('reports.customers');
        Route::get('/reports/customers/export/csv', [CustomerReportController::class, 'export'])->name('reports.customers.export');
        Route::get('/responses', [ResponseController::class, 'index'])->name('responses.index');
        Route::get('/responses/export/csv', [ResponseController::class, 'export'])->name('responses.export');
    });

Route::get('/portal/{portal}', [CaptivePortalController::class, 'show'])->name('portal.show');
Route::post('/portal/{portal}/submit', [CaptivePortalController::class, 'submit'])
    ->middleware('throttle:portal-form')
    ->name('portal.submit');

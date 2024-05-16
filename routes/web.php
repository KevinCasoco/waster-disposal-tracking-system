<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CollectorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WasteCollectionSchedule;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\TrashBinController;

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

Route::get('/', function () {
    return view('welcome');
});

// Route::redirect('/', destination: 'login');

Route::get('/dashboard', [DashboardController::class, 'countUsersByRole'])
    ->middleware('auth', 'verified', 'checkActiveStatus')
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// guess routes

Route::middleware('guest')->group(function () {

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth', 'status')->group(function () {

    // Route::get('register', [RoleController::class, 'roleInRegister'])->name('register');

    // Route::get('register', [RegisteredUserController::class, 'create'])
    //             ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
}); // end of middleware group

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')->middleware('auth');

// Admin Dashboard Sidebar
Route::middleware('auth', 'checkActiveStatus')->group(function () {

    Route::post('/admin/create_admin', [AdminController::class, 'create_admin'])->name('admin.create_admin');

    Route::get('/collector', [CollectorController::class, 'index'])->name('collector');
    Route::post('/collector/create_collector', [AdminController::class, 'create_collector'])->name('collector.create_collector');

    Route::get('/residents', [UserController::class, 'index'])->name('residents');
    Route::post('/admin/create_residents', [AdminController::class, 'create_residents'])->name('admin.create_residents');

    // collection schedule
    Route::get('/schedule', [AdminController::class, 'showSchedule'])->name('schedule');

    // sensor data notification for maximum kg
    Route::get('/check-weight', [WasteCollectionSchedule::class, 'checkWeightAndNotify']);

    // truck-weight auto refresh
    Route::get('/get-truck-weight', [DashboardController::class, 'getTruckWeight'])->name('dashboard.getTruckWeight');

    // trash-weight auto refresh
    Route::get('/get-trash-weight', [DashboardController::class, 'getTrashWeight'])->name('dashboard.getTrashWeight');

    // trash-weight status auto refresh
    Route::get('/get-trash-weight-status', [DashboardController::class, 'getTrashWeightStatus'])->name('dashboard.getTrashWeightStatus');

    // sensor data notification for maximum kg
    Route::get('/check-weight-trash', [WasteCollectionSchedule::class, 'checkTrashCanAndNotify']);

    // trash-weight status auto refresh
    Route::get('/get-truck-weight-status', [DashboardController::class, 'getTruckWeightStatus'])->name('dashboard.getTruckWeightStatus');

    // collection schedule list
    Route::get('/schedule-list', [ScheduleController::class, 'index_schedule'])->name('schedule-list');
    Route::patch('/schedule-list/update/{id}', [ScheduleController::class, 'update_schedule'])->name('schedule-list.update_schedule');

    // start calendar
    Route::get('full-calender', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/events', [ScheduleController::class, 'getEvents'])->name('schedule.getEvents');
    Route::delete('/schedule/{id}', [ScheduleController::class, 'deleteEvent'])->name('schedule.deleteEvent');
    Route::put('/schedule/{id}', [ScheduleController::class, 'update'])->name('schedule.update');
    Route::put('/schedule/{id}/resize', [ScheduleController::class, 'resize'])->name('schedule.resize');
    Route::get('/events/search', [ScheduleController::class, 'search'])->name('schedule.search');

    Route::get('/add', [ScheduleController::class, 'add_schedule'])->name('add');
    Route::post('/admin-create-schedule', [ScheduleController::class, 'create'])->name('schedule.create');

    // collection schedule
    Route::get('/admin-schedule', [WasteCollectionSchedule::class, 'showAdminSchedule'])->name('admin-schedule');

    // notify the users
    Route::get('/admin', [WasteCollectionSchedule::class, 'showNotificationForm'])->name('admin');
    Route::post('/send-notification', [WasteCollectionSchedule::class, 'admin_sendNotification'])->name('schedule.admin_sendNotification');

    // active and inactive for admin
    Route::get('/admin/activate/{id}', [AdminController::class, 'activateUser'])->name('admin.activateUser');
    Route::get('/admin/deactivate/{id}', [AdminController::class, 'deactivateUser'])->name('admin.deactivateUser');
    Route::put('/admin/toggle/{id}', [AdminController::class, 'toggleUserStatus'])->name('admin.toggleUserStatus');

    // active and inactive for collector
    Route::get('/collector/activate/{id}', [AdminController::class, 'activateCollector'])->name('collector.activateCollector');
    Route::get('/collector/deactivate/{id}', [AdminController::class, 'deactivateCollector'])->name('collector.deactivateCollector');
    Route::put('/collector/toggle/{id}', [AdminController::class, 'toggleCollectorStatus'])->name('collector.toggleCollectorStatus');

    // active and inactive for residents
    Route::get('/residents/activate/{id}', [AdminController::class, 'activateResidents'])->name('residents.activateResidents');
    Route::get('/residents/deactivate/{id}', [AdminController::class, 'deactivateResidents'])->name('residents.deactivateResidents');
    Route::put('/residents/toggle/{id}', [AdminController::class, 'toggleResidentsStatus'])->name('residents.toggleResidentsStatus');

    // edit admin
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::patch('/admin/update/{id}', [AdminController::class, 'update'])->name('admin.update');

    // edit collector
    Route::get('/collector', [CollectorController::class, 'index'])->name('collector');
    Route::patch('/collector/update/{id}', [AdminController::class, 'update_collector'])->name('collector.update_collector');

    // edit residents/users
    Route::get('/residents', [UserController::class, 'index'])->name('residents');
    Route::patch('/residents/update/{id}', [AdminController::class, 'update_residents'])->name('residents.update_residents');

    // delete
    Route::delete('/admin/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
    Route::delete('/admin/collector/{id}', [AdminController::class, 'admin_destroy_collector'])->name('collector.admin_destroy_collector');
    Route::delete('/admin/residents/{id}', [AdminController::class, 'admin_destroy_residents'])->name('residents.admin_destroy_residents');
    Route::delete('/admin/schedule/{id}', [ScheduleController::class, 'schedule_destroy'])->name('schedule-list.schedule_destroy');

    // scheduling for waste collection
    Route::get('/calendar/schedule', [WasteCollectionSchedule::class, 'schedule'])->name('calendar.schedule');
    Route::post('/calendar', [WasteCollectionSchedule::class, 'store'])->name('calendar.store');
    Route::patch('/calendar/update/{id}', [WasteCollectionSchedule::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/delete/{id}', [WasteCollectionSchedule::class, 'delete'])->name('calendar.delete');

    // Route for the user roles chart
    Route::get('/user-roles-chart', [ChartController::class, 'index'])->name('dashboard.index');

    // Route for the active and inactive users
    Route::get('/chart-data', [ChartController::class, 'getChartData'])->name('dashboard.getChartData');

    Route::post('/users/{userId}/schedules', [WasteCollectionSchedule::class, 'store']);

    // send sms
    Route::post('/sms', [SmsController::class, 'sms'])->name('schedule.sms');

    // Trash Bin routes
    Route::get('/admin-trash-bin', [TrashBinController::class, 'index'])->name('index.admin-trash-bin');
    Route::post('/admin-trash-bin/create', [TrashBinController::class, 'create'])->name('admin-trash-bin.create');
    // Route::post('/trash-bins', [TrashBinController::class, 'store'])->name('trash-bins.store');
    // Route::get('/trash-bins/{trashBin}', [TrashBinController::class, 'show'])->name('trash-bins.show');
    // Route::get('/trash-bins/{trashBin}/edit', [TrashBinController::class, 'edit'])->name('trash-bins.edit');
    // Route::put('/trash-bins/{trashBin}', [TrashBinController::class, 'update'])->name('trash-bins.update');
    // Route::delete('/trash-bins/{trashBin}', [TrashBinController::class, 'destroy'])->name('trash-bins.destroy');

    // soft delete schedule list
    Route::get('/schedule-list-restore', [ScheduleController::class, 'schedule_restore'])->name('schedule-list-restore.schedule_restore');
    Route::patch('/collector-schedules/{id}/restore', [ScheduleController::class, 'restore'])->name('schedule-list-restore.restore');

    // soft delete residents list
    Route::get('/residents-restore', [UserController::class, 'residents_restore_data'])->name('residents-restore.residents_restore_data');
    Route::patch('/residents/{id}/restore', [UserController::class, 'restore_residents_info'])->name('residents-restore.restore_residents_info');

    // soft delete collector list
    Route::get('/collector-restore', [CollectorController::class, 'restore_collector_data'])->name('collector-restore.restore_collector_data');
    Route::patch('/collector/{id}/restore', [CollectorController::class, 'restore_collector_info'])->name('collector-restore.restore_collector_info');

    // soft delete admin list
    Route::get('/admin-restore', [AdminController::class, 'admin_collector_data'])->name('admin-restore.admin_collector_data');
    Route::patch('/admin/{id}/restore', [AdminController::class, 'admin_collector_info'])->name('admin-restore.admin_collector_info');

}); // end of middleware group

// Collector Dashboard Sidebar
Route::middleware('auth', 'checkActiveStatus')->group(function () {

    Route::post('/collector/create_collector_residents', [CollectorController::class, 'create_collector_residents'])->name('collector-residents.create_collector_residents');

    // start calendar
    Route::get('collector-full-calender', [ScheduleController::class, 'index_collector'])->name('collector-schedule.index_collector');
    Route::get('/collector-events', [ScheduleController::class, 'getEvents'])->name('collector-schedule.getEvents');
    Route::delete('/collector-schedule/{id}', [ScheduleController::class, 'deleteEvent'])->name('collector-schedule.deleteEvent');
    Route::put('/collector-schedule/{id}', [ScheduleController::class, 'update'])->name('collector-schedule.update');
    Route::put('/collector-schedule/{id}/resize', [ScheduleController::class, 'resize'])->name('collector-schedule.resize');
    Route::get('/collector-events/search', [ScheduleController::class, 'search'])->name('collector-schedule.search');

    Route::get('/add-collector', [ScheduleController::class, 'add_schedule_collector'])->name('add-collector');
    Route::post('/create-schedule', [ScheduleController::class, 'create_collector'])->name('collector-schedule.create_collector');

    // collection schedule
    Route::get('/collector-schedule', [WasteCollectionSchedule::class, 'showCollectorSchedule'])->name('collector-schedule');

    // collection schedule list
    Route::get('/collector-schedule-list', [ScheduleController::class, 'index_collector_schedule'])->name('collector-schedule-list');
    Route::patch('/collector-schedule-list/update/{id}', [ScheduleController::class, 'collector_update_schedule'])->name('collector-schedule-list.collector_update_schedule');
    Route::delete('/collector/schedule/{id}', [ScheduleController::class, 'collector_schedule_destroy'])->name('collector-schedule-list.collector_schedule_destroy');

    // static maps
    Route::get('/location', [CollectorController::class, 'showLocation'])->name('location');

    // notify the users
    Route::get('/collector-email', [WasteCollectionSchedule::class, 'collector_showNotificationForm'])->name('collector-email');
    Route::post('/collector-send-email', [WasteCollectionSchedule::class, 'collector_sendNotification'])->name('collector-send-email.collector-send-notification');

    // active and inactive for admin
    Route::get('/collector-residents/activate/{id}', [CollectorController::class, 'activateUser'])->name('collector-residents.activateUser');
    Route::get('/collector-residents/deactivate/{id}', [CollectorController::class, 'deactivateUser'])->name('collector-residents.deactivateUser');
    Route::put('/collector-residents/toggle/{id}', [CollectorController::class, 'toggleResidentsStatus'])->name('collector-residents.toggleResidentsStatus');

    // edit collector
    Route::patch('/residents-collector/update/{id}', [CollectorController::class, 'update_collector'])->name('collector-residents.update_collector');

    // delete residents-collector
    Route::get('/collector-residents', [CollectorController::class, 'collector'])->name('collector-residents');
    Route::delete('/collector/{id}', [CollectorController::class, 'destroy_collector_residents'])->name('collector-residents.destroy_collector_residents');

    // send sms
    Route::post('/sms-controller', [SmsController::class, 'sms_controller'])->name('collector-schedule.sms_controller');

    // soft delete data
    Route::get('/collector-schedule-list-restore', [ScheduleController::class, 'collector_schedule_restore'])->name('collector-schedule-list-restore.collector_schedule_restore');
    Route::patch('/collector-schedules-list/{id}/restore', [ScheduleController::class, 'collector_restore'])->name('collector-schedule-list-restore.collector_restore');

}); // end of middleware group

// User-Residents Dashboard Sidebar
Route::middleware('auth', 'checkActiveStatus', 'verified')->group(function () {

    // calendar for collection
    Route::get('/user-schedule', [UserController::class, 'showUserSchedule'])->name('user-schedule');

    // dynamic augmented reality 3 marker for 3 sample images
    Route::get('/augmented-reality', [UserController::class, 'showAugmentedReality'])->name('augmented-reality');

    Route::get('/residents-trash-bin', [UserController::class, 'showTrashBin'])->name('showTrashBin.residents-trash-bin');

    // // augmented reality with marker
    // Route::get('/kitchen-waste', [UserController::class, 'showKitchenWaste'])->name('kitchen-waste');

    // Route::get('/recyclable-waste', [UserController::class, 'showRecyclableWaste'])->name('recyclable-waste');

    // Route::get('/hazardous-waste', [UserController::class, 'showHazardousWaste'])->name('hazardous-waste');

}); // end of middleware group

require __DIR__.'/auth.php';

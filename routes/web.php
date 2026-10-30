<?php

use App\Http\Controllers\Admin\ChatbotFaqController;
use App\Http\Controllers\Admin\WhatsAppSenderController;
use App\Http\Controllers\BodyMeasurementController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\GymReminderController;
use App\Http\Controllers\NutritionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkoutController;
use Illuminate\Support\Facades\Route;

// الصفحة الرئيسية تحول لصفحة الدخول مباشرة
Route::get('/', function () {
    return redirect()->route('login');
});

// مسارات جميع المستخدمين المسجلين (مشتركين + سوبر أدمن)
Route::middleware(['auth', 'active_account'])->group(function () {

    // Personal training/nutrition screens belong to members, including direct API/form requests.
    Route::middleware('member')->group(function () {

        // لوحة التحكم الرئيسية والتمارين
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/workout/start/{day}', [WorkoutController::class, 'start'])->name('workout.start');
        Route::post('/workout/store', [WorkoutController::class, 'store'])->name('workout.store');
        Route::post('/workout/store-single', [WorkoutController::class, 'storeSingleExercise'])->name('workout.store-single');
        Route::get('/history', [WorkoutController::class, 'history'])->name('history.index');

        // بنك التمارين
        Route::resource('exercises', ExerciseController::class)->except(['create', 'show', 'edit']);

        // إدارة الأنظمة والأيام
        Route::get('/programs/exercises/search', [ProgramController::class, 'searchExercises'])
            ->middleware('throttle:120,1')->name('programs.exercises.search');
        Route::resource('programs', ProgramController::class)->except(['create', 'show', 'edit']);
        Route::post('/programs/{program}/activate', [ProgramController::class, 'activate'])->name('programs.activate');

        Route::post('/programs/{program}/days', [ProgramController::class, 'storeDay'])->name('programs.days.store');
        Route::put('/days/{day}', [ProgramController::class, 'updateDay'])->name('days.update');
        Route::delete('/days/{day}', [ProgramController::class, 'destroyDay'])->name('days.destroy');

        Route::post('/days/{day}/exercises', [ProgramController::class, 'attachExercise'])->name('days.exercises.attach');
        Route::put('/day-exercises/{dayExercise}', [ProgramController::class, 'updateDayExercise'])->name('day-exercises.update');
        Route::delete('/day-exercises/{dayExercise}', [ProgramController::class, 'destroyDayExercise'])->name('day-exercises.destroy');

        // القياسات البدنية
        Route::get('/measurements', [BodyMeasurementController::class, 'index'])->name('measurements.index');
        Route::post('/measurements', [BodyMeasurementController::class, 'store'])->name('measurements.store');
        Route::delete('/measurements/{measurement}', [BodyMeasurementController::class, 'destroy'])->name('measurements.destroy');

        // التغذية
        Route::get('/nutrition/foods/search', [NutritionController::class, 'searchFoods'])
            ->middleware('throttle:120,1')->name('nutrition.searchFoods');
        Route::get('/nutrition', [NutritionController::class, 'index'])->name('nutrition.index');
        Route::post('/nutrition/food', [NutritionController::class, 'storeFood'])->name('nutrition.storeFood');
        Route::put('/nutrition/food/{food}', [NutritionController::class, 'updateFood'])->name('nutrition.updateFood');
        Route::delete('/nutrition/food/{food}', [NutritionController::class, 'destroyFood'])->name('nutrition.destroyFood');
        Route::post('/nutrition/item', [NutritionController::class, 'storeMealItem'])->name('nutrition.storeMealItem');
        Route::delete('/nutrition/item/{mealItem}', [NutritionController::class, 'destroyMealItem'])->name('nutrition.destroyMealItem');
        Route::get('/nutrition/history', [NutritionController::class, 'history'])->name('nutrition.history');

    });

    // الملف الشخصي
    Route::put('/profile/gym-reminder', [GymReminderController::class, 'update'])
        ->middleware(['member', 'throttle:20,1,gym-reminder-settings:'])->name('profile.gym-reminder.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // مسار الشات بوت الذكي (يعمل للمشترك والأدمن معاً)
    Route::post('/chatbot/send', [ChatbotController::class, 'sendMessage'])
        ->middleware('throttle:30,1')->name('chatbot.send');
});

// مسارات السوبر أدمن فقط (Super Admin Routes)
Route::middleware(['auth', 'active_account', 'super_admin'])->prefix('admin')->group(function () {
    Route::get('/whatsapp', [WhatsAppSenderController::class, 'index'])->name('admin.whatsapp.index');
    Route::put('/whatsapp/message', [WhatsAppSenderController::class, 'updateMessage'])
        ->middleware('throttle:10,1,gym-whatsapp-message:')->name('admin.whatsapp.message.update');
    Route::get('/whatsapp/status', [WhatsAppSenderController::class, 'status'])
        ->middleware('throttle:30,1,gym-whatsapp-status:')->name('admin.whatsapp.status');
    Route::post('/whatsapp/connect', [WhatsAppSenderController::class, 'connect'])
        ->middleware('throttle:5,1,gym-whatsapp-connect:')->name('admin.whatsapp.connect');
    Route::post('/whatsapp/disconnect', [WhatsAppSenderController::class, 'disconnect'])
        ->middleware('throttle:5,1,gym-whatsapp-disconnect:')->name('admin.whatsapp.disconnect');
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/chatbot/faqs', [ChatbotFaqController::class, 'index'])->name('admin.chatbot.faqs.index');
    Route::post('/chatbot/faqs', [ChatbotFaqController::class, 'store'])->name('admin.chatbot.faqs.store');
    Route::put('/chatbot/faqs/{faq}', [ChatbotFaqController::class, 'update'])->name('admin.chatbot.faqs.update');
    Route::delete('/chatbot/faqs/{faq}', [ChatbotFaqController::class, 'destroy'])->name('admin.chatbot.faqs.destroy');
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
});

require __DIR__.'/auth.php';

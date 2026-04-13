<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard (redirect based on role)
    Route::get('/', function () {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        } elseif ($user->isParent()) {
            return redirect()->route('parent.dashboard');
        }
        
        return redirect()->route('login');
    })->name('home');

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        
        // Student management
        Route::resource('students', StudentController::class);
        Route::post('students/{student}/generate-qr', [StudentController::class, 'generateQRCode'])
            ->name('students.generate-qr');
        
        // Teacher management
        Route::resource('teachers', TeacherController::class);
        
        // Class management
        Route::resource('classes', ClassRoomController::class);
        
        // Major management
        Route::resource('majors', MajorController::class);
        
        // Attendance management
        Route::resource('attendance', AttendanceController::class);
        Route::post('attendance/{attendance}/verify', [AttendanceController::class, 'verify'])
            ->name('attendance.verify');
        
        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/export', [ReportController::class, 'export'])->name('reports.export');
        
        // Settings
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Teacher routes
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'teacherDashboard'])->name('dashboard');
        
        // View students in their classes
        Route::get('students', [TeacherController::class, 'students'])->name('students.index');
        Route::get('students/{student}', [TeacherController::class, 'showStudent'])
            ->name('students.show');
        
        // Verify attendance
        Route::post('attendance/{attendance}/verify', [AttendanceController::class, 'verify'])
            ->name('attendance.verify');
        
        // Manual attendance entry
        Route::get('attendance/manual', [AttendanceController::class, 'create'])
            ->name('attendance.create');
        Route::post('attendance/manual', [AttendanceController::class, 'store'])
            ->name('attendance.store');
    });

    // Student routes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'studentDashboard'])->name('dashboard');
        
        // Check-in/out
        Route::get('/check-in', [CheckInController::class, 'showCheckInForm'])->name('checkin.form');
        Route::post('/check-in', [CheckInController::class, 'checkIn'])->name('checkin.submit');
        Route::get('/check-out', [CheckInController::class, 'showCheckOutForm'])->name('checkout.form');
        Route::post('/check-out', [CheckInController::class, 'checkOut'])->name('checkout.submit');
        
        // Attendance history
        Route::get('/history', [CheckInController::class, 'history'])->name('history');
        
        // Profile
        Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
        Route::put('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
    });

    // Parent routes
    Route::prefix('parent')->name('parent.')->middleware('role:parent')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'parentDashboard'])->name('dashboard');
        
        // View children's attendance
        Route::get('children', [TeacherController::class, 'children'])->name('children.index');
        Route::get('children/{student}/history', [CheckInController::class, 'history'])
            ->name('children.history');
    });
});

// API routes for mobile app or AJAX requests
Route::prefix('api')->middleware('auth:sanctum')->group(function () {
    // Check-in/out endpoints
    Route::post('/check-in', [CheckInController::class, 'checkIn']);
    Route::post('/check-out', [CheckInController::class, 'checkOut']);
    
    // Get student attendance history
    Route::get('/attendance/history', [CheckInController::class, 'history']);
});

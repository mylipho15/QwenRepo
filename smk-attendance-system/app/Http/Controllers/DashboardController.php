<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard.
     */
    public function adminDashboard()
    {
        $today = today();
        
        // Today's statistics
        $stats = [
            'total_students' => Student::where('is_active', true)->count(),
            'present_today' => Attendance::today()
                ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])
                ->count(),
            'late_today' => Attendance::today()
                ->byStatus(Attendance::STATUS_LATE)
                ->count(),
            'absent_today' => Student::where('is_active', true)
                ->whereDoesntHave('attendances', function ($query) use ($today) {
                    $query->whereDate('date', $today);
                })
                ->count(),
        ];

        // Calculate attendance percentage
        $stats['attendance_percentage'] = $stats['total_students'] > 0
            ? round(($stats['present_today'] / $stats['total_students']) * 100, 2)
            : 0;

        // Recent attendances
        $recentAttendances = Attendance::with(['student.user', 'student.class'])
            ->today()
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->get();

        // Chart data for last 7 days
        $chartData = $this->getLast7DaysChartData();

        // Top late students this month
        $topLateStudents = Student::with(['user', 'class'])
            ->whereHas('attendances', function ($query) {
                $query->whereMonth('date', now()->month)
                      ->whereYear('date', now()->year)
                      ->where('status', Attendance::STATUS_LATE);
            })
            ->withCount(['attendances as late_count' => function ($query) {
                $query->whereMonth('date', now()->month)
                      ->whereYear('date', now()->year)
                      ->where('status', Attendance::STATUS_LATE);
            }])
            ->orderByDesc('late_count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentAttendances',
            'chartData',
            'topLateStudents'
        ));
    }

    /**
     * Display teacher dashboard.
     */
    public function teacherDashboard(Request $request)
    {
        $teacher = Auth::user()->teacher;
        
        // Get classes taught by teacher (if applicable)
        $classes = $teacher?->classes ?? [];
        
        // Today's statistics for classes taught
        $stats = [
            'total_classes' => count($classes),
            'total_students' => 0,
            'present_today' => 0,
            'late_today' => 0,
        ];

        if (!empty($classes)) {
            $classIds = $classes->pluck('id');
            
            $stats['total_students'] = Student::whereIn('class_id', $classIds)
                ->where('is_active', true)
                ->count();
            
            $stats['present_today'] = Attendance::today()
                ->whereHas('student', function ($query) use ($classIds) {
                    $query->whereIn('class_id', $classIds);
                })
                ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])
                ->count();
            
            $stats['late_today'] = Attendance::today()
                ->whereHas('student', function ($query) use ($classIds) {
                    $query->whereIn('class_id', $classIds);
                })
                ->byStatus(Attendance::STATUS_LATE)
                ->count();
        }

        // Students needing verification
        $pendingVerifications = Attendance::with(['student.user', 'student.class'])
            ->today()
            ->unverified()
            ->limit(10)
            ->get();

        return view('teacher.dashboard', compact('stats', 'pendingVerifications'));
    }

    /**
     * Display student dashboard.
     */
    public function studentDashboard()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return redirect()->route('logout')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        // Today's attendance
        $todayAttendance = $student->todayAttendance();

        // This month's statistics
        $monthlyStats = $student->getAttendanceStats(
            now()->month,
            now()->year
        );

        // Last 7 days attendance
        $last7Days = Attendance::where('student_id', $student->id)
            ->whereDate('date', '>=', now()->subDays(7))
            ->orderBy('date', 'desc')
            ->get();

        // Attendance calendar data for current month
        $calendarData = $this->getMonthCalendarData($student->id);

        return view('student.dashboard', compact(
            'student',
            'todayAttendance',
            'monthlyStats',
            'last7Days',
            'calendarData'
        ));
    }

    /**
     * Display parent dashboard.
     */
    public function parentDashboard()
    {
        $parent = Auth::user()->parent;
        
        if (!$parent) {
            return redirect()->route('logout')
                ->with('error', 'Data orang tua tidak ditemukan.');
        }

        // Get children
        $children = $parent->students()->with(['user', 'class'])->get();

        // Today's summary for all children
        $todaySummary = [];
        foreach ($children as $child) {
            $todayAttendance = $child->todayAttendance();
            $todaySummary[] = [
                'student' => $child,
                'attendance' => $todayAttendance,
            ];
        }

        return view('parent.dashboard', compact('children', 'todaySummary'));
    }

    /**
     * Get chart data for last 7 days.
     */
    private function getLast7DaysChartData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            
            $present = Attendance::whereDate('date', $date)
                ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])
                ->count();
            
            $absent = Student::where('is_active', true)
                ->whereDoesntHave('attendances', function ($query) use ($date) {
                    $query->whereDate('date', $date);
                })
                ->count();
            
            $data[] = $present;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get calendar data for current month.
     */
    private function getMonthCalendarData(int $studentId): array
    {
        $attendances = Attendance::where('student_id', $studentId)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->get()
            ->keyBy('date');

        $calendarData = [];
        $daysInMonth = now()->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = now()->setDay($day)->format('Y-m-d');
            $attendance = $attendances->get($date);
            
            $calendarData[$day] = [
                'date' => $date,
                'status' => $attendance?->status ?? null,
                'check_in' => $attendance?->check_in_time?->format('H:i'),
                'check_out' => $attendance?->check_out_time?->format('H:i'),
                'late_minutes' => $attendance?->getLateDuration() ?? 0,
            ];
        }

        return $calendarData;
    }
}

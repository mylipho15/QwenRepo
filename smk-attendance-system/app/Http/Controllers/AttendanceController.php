<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Display a listing of today's attendance.
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['student.user', 'student.class'])
            ->today()
            ->orderBy('check_in_time', 'desc');

        // Filter by class if provided
        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        $attendances = $query->paginate(20);

        // Statistics for today
        $stats = [
            'total' => Attendance::today()->count(),
            'present' => Attendance::today()->byStatus(Attendance::STATUS_PRESENT)->count(),
            'late' => Attendance::today()->byStatus(Attendance::STATUS_LATE)->count(),
            'absent' => Attendance::today()->byStatus(Attendance::STATUS_ABSENT)->count(),
        ];

        return view('admin.attendance.index', compact('attendances', 'stats'));
    }

    /**
     * Show the form for creating a new attendance (manual).
     */
    public function create()
    {
        $students = Student::with(['user', 'class'])
            ->where('is_active', true)
            ->orderBy('nis')
            ->get();

        return view('admin.attendance.create', compact('students'));
    }

    /**
     * Store a manually created attendance record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,late,absent,permission,sick',
            'notes' => 'nullable|string|max:500',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        
        $date = \Carbon\Carbon::parse($validated['date']);
        
        $attendanceData = [
            'student_id' => $validated['student_id'],
            'user_id' => $student->user_id,
            'date' => $date,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ];

        if (!empty($validated['check_in_time'])) {
            $attendanceData['check_in_time'] = $date->copy()->setTime(
                (int)substr($validated['check_in_time'], 0, 2),
                (int)substr($validated['check_in_time'], 3, 2)
            );
        }

        if (!empty($validated['check_out_time'])) {
            $attendanceData['check_out_time'] = $date->copy()->setTime(
                (int)substr($validated['check_out_time'], 0, 2),
                (int)substr($validated['check_out_time'], 3, 2)
            );
        }

        Attendance::create($attendanceData);

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Absensi berhasil ditambahkan.');
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['student.user', 'student.class', 'verifier']);
        
        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance)
    {
        $students = Student::with(['user', 'class'])
            ->where('is_active', true)
            ->orderBy('nis')
            ->get();

        return view('admin.attendance.edit', compact('attendance', 'students'));
    }

    /**
     * Update the specified attendance record in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,late,absent,permission,sick',
            'notes' => 'nullable|string|max:500',
        ]);

        $date = \Carbon\Carbon::parse($validated['date']);
        
        $updateData = [
            'student_id' => $validated['student_id'],
            'date' => $date,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ];

        if (!empty($validated['check_in_time'])) {
            $updateData['check_in_time'] = $date->copy()->setTime(
                (int)substr($validated['check_in_time'], 0, 2),
                (int)substr($validated['check_in_time'], 3, 2)
            );
        }

        if (!empty($validated['check_out_time'])) {
            $updateData['check_out_time'] = $date->copy()->setTime(
                (int)substr($validated['check_out_time'], 0, 2),
                (int)substr($validated['check_out_time'], 3, 2)
            );
        }

        $attendance->update($updateData);

        return redirect()->route('admin.attendance.show', $attendance)
            ->with('success', 'Absensi berhasil diperbarui.');
    }

    /**
     * Remove the specified attendance record from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Absensi berhasil dihapus.');
    }

    /**
     * Verify an attendance record.
     */
    public function verify(Attendance $attendance)
    {
        $attendance->update([
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Absensi berhasil diverifikasi.');
    }
}

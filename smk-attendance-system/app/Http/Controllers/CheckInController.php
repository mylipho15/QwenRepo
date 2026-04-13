<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CheckInController extends Controller
{
    /**
     * Show the check-in form for students.
     */
    public function showCheckInForm()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $todayAttendance = $student->todayAttendance();
        
        return view('student.checkin', compact('student', 'todayAttendance'));
    }

    /**
     * Process student check-in.
     */
    public function checkIn(Request $request)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        // Validate request
        $validated = $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'required|image|max:2048', // 2MB max
        ]);

        // Verify QR code matches student
        if ($validated['qr_code'] !== $student->qr_code) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid.',
            ], 403);
        }

        // Check if already checked in today
        if ($student->hasCheckedInToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi hari ini.',
            ], 400);
        }

        // Validate location (geofencing)
        $isValidLocation = $this->validateLocation(
            $validated['latitude'],
            $validated['longitude']
        );

        if (!$isValidLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus berada di lingkungan sekolah untuk melakukan absensi.',
            ], 403);
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store(
                'photos/check-in/' . date('Y-m-d'),
                'public'
            );
        }

        // Determine status (present or late)
        $status = Attendance::determineStatus(now());

        // Create attendance record
        $attendance = Attendance::create([
            'student_id' => $student->id,
            'user_id' => Auth::id(),
            'date' => today(),
            'check_in_time' => now(),
            'status' => $status,
            'check_in_location' => json_encode([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]),
            'check_in_photo' => $photoPath,
            'check_in_ip' => $request->ip(),
            'check_in_device' => $request->userAgent(),
        ]);

        // Log activity
        Log::info('Student check-in', [
            'student_id' => $student->id,
            'student_name' => Auth::user()->name,
            'time' => now(),
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dilakukan. Status: ' . ucfirst($status),
            'data' => [
                'attendance_id' => $attendance->id,
                'check_in_time' => $attendance->check_in_time->format('H:i:s'),
                'status' => $attendance->status,
                'late_minutes' => $attendance->getLateDuration(),
            ],
        ]);
    }

    /**
     * Show the check-out form for students.
     */
    public function showCheckOutForm()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Data siswa tidak ditemukan.');
        }

        $todayAttendance = $student->todayAttendance();
        
        if (!$todayAttendance || !$todayAttendance->check_in_time) {
            return redirect()->route('student.checkin.form')
                ->with('error', 'Silakan lakukan absensi datang terlebih dahulu.');
        }

        return view('student.checkout', compact('student', 'todayAttendance'));
    }

    /**
     * Process student check-out.
     */
    public function checkOut(Request $request)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        // Validate request
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'required|image|max:2048', // 2MB max
        ]);

        // Get today's attendance
        $todayAttendance = $student->todayAttendance();

        if (!$todayAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada catatan absensi datang hari ini.',
            ], 400);
        }

        if ($todayAttendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi pulang hari ini.',
            ], 400);
        }

        // Validate location (geofencing)
        $isValidLocation = $this->validateLocation(
            $validated['latitude'],
            $validated['longitude']
        );

        if (!$isValidLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus berada di lingkungan sekolah untuk melakukan absensi pulang.',
            ], 403);
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store(
                'photos/check-out/' . date('Y-m-d'),
                'public'
            );
        }

        // Update attendance record
        $todayAttendance->update([
            'check_out_time' => now(),
            'check_out_location' => json_encode([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]),
            'check_out_photo' => $photoPath,
            'check_out_ip' => $request->ip(),
            'check_out_device' => $request->userAgent(),
        ]);

        // Calculate study duration
        $studyDuration = $todayAttendance->getStudyDuration();

        // Log activity
        Log::info('Student check-out', [
            'student_id' => $student->id,
            'student_name' => Auth::user()->name,
            'time' => now(),
            'duration_hours' => $studyDuration,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi pulang berhasil dilakukan.',
            'data' => [
                'attendance_id' => $todayAttendance->id,
                'check_out_time' => $todayAttendance->check_out_time->format('H:i:s'),
                'study_duration_hours' => $studyDuration,
            ],
        ]);
    }

    /**
     * Validate if the location is within school geofence.
     */
    private function validateLocation($latitude, $longitude): bool
    {
        // School coordinates (example: SMK center point)
        $schoolLat = config('attendance.school_latitude', -6.2088);
        $schoolLon = config('attendance.school_longitude', 106.8456);
        $maxDistance = config('attendance.max_distance_meters', 100); // 100 meters radius

        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance(
            $schoolLat,
            $schoolLon,
            $latitude,
            $longitude
        );

        return $distance <= $maxDistance;
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula.
     * Returns distance in meters.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get student's attendance history.
     */
    public function history(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = Attendance::where('student_id', $student->id)
            ->orderBy('date', 'desc');

        // Filter by month and year
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereYear('date', $request->year)
                  ->whereMonth('date', $request->month);
        }

        $attendances = $query->paginate(20);

        // Get statistics
        $stats = $student->getAttendanceStats(
            $request->month,
            $request->year
        );

        return view('student.history', compact('attendances', 'stats'));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'check_in_location',
        'check_out_location',
        'check_in_photo',
        'check_out_photo',
        'check_in_ip',
        'check_out_ip',
        'check_in_device',
        'check_out_device',
        'notes',
        'verified_by',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PRESENT = 'present';
    const STATUS_LATE = 'late';
    const STATUS_ABSENT = 'absent';
    const STATUS_PERMISSION = 'permission';
    const STATUS_SICK = 'sick';

    /**
     * Get the student for the attendance
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user for the attendance
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the verifier (teacher/admin)
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if check-in is late
     */
    public function isLate(): bool
    {
        return $this->status === self::STATUS_LATE;
    }

    /**
     * Calculate late duration in minutes
     */
    public function getLateDuration(): int
    {
        if (!$this->check_in_time) {
            return 0;
        }

        $schoolStartTime = config('attendance.school_start_time', '07:00');
        $startTime = today()->setTime(
            (int)substr($schoolStartTime, 0, 2),
            (int)substr($schoolStartTime, 3, 2)
        );

        if ($this->check_in_time > $startTime) {
            return $this->check_in_time->diffInMinutes($startTime);
        }

        return 0;
    }

    /**
     * Calculate work/study duration in hours
     */
    public function getStudyDuration(): float
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }

        return round($this->check_out_time->diffInMinutes($this->check_in_time) / 60, 2);
    }

    /**
     * Scope to get today's attendances
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get unverified attendances
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    /**
     * Generate attendance status based on check-in time
     */
    public static function determineStatus($checkInTime)
    {
        $schoolStartTime = config('attendance.school_start_time', '07:00');
        $lateThreshold = config('attendance.late_threshold', 15); // minutes
        
        $startTime = today()->setTime(
            (int)substr($schoolStartTime, 0, 2),
            (int)substr($schoolStartTime, 3, 2)
        );

        $lateMinutes = $checkInTime->diffInMinutes($startTime);

        if ($lateMinutes > $lateThreshold) {
            return self::STATUS_LATE;
        }

        return self::STATUS_PRESENT;
    }
}

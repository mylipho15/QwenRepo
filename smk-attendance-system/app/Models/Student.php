<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'class_id',
        'major_id',
        'gender',
        'birth_date',
        'birth_place',
        'address',
        'phone',
        'parent_name',
        'parent_phone',
        'qr_code',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the student
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the class for the student
     */
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the major for the student
     */
    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Get attendance records for the student
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get today's attendance
     */
    public function todayAttendance()
    {
        return $this->attendances()
            ->whereDate('date', today())
            ->latest()
            ->first();
    }

    /**
     * Check if student has checked in today
     */
    public function hasCheckedInToday(): bool
    {
        return $this->attendances()
            ->whereDate('date', today())
            ->whereNotNull('check_in_time')
            ->exists();
    }

    /**
     * Check if student has checked out today
     */
    public function hasCheckedOutToday(): bool
    {
        return $this->attendances()
            ->whereDate('date', today())
            ->whereNotNull('check_out_time')
            ->exists();
    }

    /**
     * Get attendance statistics
     */
    public function getAttendanceStats($month = null, $year = null)
    {
        $query = $this->attendances();
        
        if ($month && $year) {
            $query->whereYear('date', $year)
                  ->whereMonth('date', $month);
        }
        
        $total = $query->count();
        $present = $query->where('status', 'present')->count();
        $late = $query->where('status', 'late')->count();
        $absent = $query->where('status', 'absent')->count();
        $permission = $query->where('status', 'permission')->count();
        $sick = $query->where('status', 'sick')->count();
        
        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'permission' => $permission,
            'sick' => $sick,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'photo',
        'is_active',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login' => 'datetime',
    ];

    /**
     * Role constants
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_TEACHER = 'teacher';
    const ROLE_STUDENT = 'student';
    const ROLE_PARENT = 'parent';

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is teacher
     */
    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    /**
     * Check if user is student
     */
    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    /**
     * Check if user is parent
     */
    public function isParent(): bool
    {
        return $this->role === self::ROLE_PARENT;
    }

    /**
     * Get student relationship
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get teacher relationship
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get parent relationship
     */
    public function parent()
    {
        return $this->hasOne(Parent::class);
    }

    /**
     * Get attendance records
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

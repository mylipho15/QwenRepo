<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Attendance Configuration
    |--------------------------------------------------------------------------
    */

    // School start time (24-hour format)
    'school_start_time' => env('SCHOOL_START_TIME', '07:00'),

    // School end time (24-hour format)
    'school_end_time' => env('SCHOOL_END_TIME', '15:00'),

    // Late threshold in minutes (grace period)
    'late_threshold' => env('LATE_THRESHOLD', 15),

    // School GPS coordinates for geofencing
    'school_latitude' => env('SCHOOL_LATITUDE', -6.2088),
    'school_longitude' => env('SCHOOL_LONGITUDE', 106.8456),

    // Maximum distance from school in meters for valid check-in/out
    'max_distance_meters' => env('MAX_DISTANCE_METERS', 100),

    // Enable/disable QR code requirement
    'require_qr_code' => env('REQUIRE_QR_CODE', true),

    // Enable/disable photo requirement
    'require_photo' => env('REQUIRE_PHOTO', true),

    // Enable/disable GPS validation
    'require_gps' => env('REQUIRE_GPS', true),

    // Auto-mark absent after this time (24-hour format)
    'auto_absent_time' => env('AUTO_ABSENT_TIME', '09:00'),

    // Minimum study duration in hours before allowing check-out
    'minimum_study_hours' => env('MINIMUM_STUDY_HOURS', 6),

    // Notification settings
    'notifications' => [
        'send_whatsapp' => env('SEND_WHATSAPP_NOTIFICATION', false),
        'send_email' => env('SEND_EMAIL_NOTIFICATION', true),
        'notify_parents' => env('NOTIFY_PARENTS', true),
        'notify_on_late' => env('NOTIFY_ON_LATE', true),
        'notify_on_absent' => env('NOTIFY_ON_ABSENT', true),
    ],

    // Working days (0 = Sunday, 6 = Saturday)
    'working_days' => [1, 2, 3, 4, 5], // Monday to Friday

    // Holiday dates (array of date strings in Y-m-d format)
    'holidays' => [],

];

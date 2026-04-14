<?php
/**
 * Logout Handler - SISDM Absensi Siswa
 */

require_once '../../config/config.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
redirect(BASE_URL . 'modules/auth/login.php');

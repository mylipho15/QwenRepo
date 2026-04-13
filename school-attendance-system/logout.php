<?php
require_once 'config/database.php';
startSession();
session_destroy();
header('Location: index.php?error=logout');
exit;
?>

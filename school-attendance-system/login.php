<?php
require_once 'config/database.php';
startSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('petugas/dashboard.php');
            }
        }
    }

    $stmt->close();
    $conn->close();
}

redirect('index.php?error=invalid');
?>

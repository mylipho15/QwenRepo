<?php
/**
 * Database Installation Script
 * Can be run via CLI or browser to install/reset database
 */

require_once __DIR__ . '/../config/database.php';

// Check if running from CLI
$isCli = php_sapi_name() === 'cli';

if ($isCli) {
    echo "========================================\n";
    echo "School Attendance System - DB Installer\n";
    echo "========================================\n\n";
}

function printMessage($message, $type = 'info') {
    global $isCli;
    
    if ($isCli) {
        $prefixes = [
            'info' => '[INFO] ',
            'success' => '[OK] ',
            'error' => '[ERROR] ',
            'warning' => '[WARNING] '
        ];
        echo $prefixes[$type] . $message . "\n";
    } else {
        $classes = [
            'info' => 'alert-info',
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning'
        ];
        echo "<div class=\"alert {$classes[$type]}\">" . htmlspecialchars($message) . "</div>";
    }
}

function installDatabase() {
    global $isCli;
    
    try {
        // Create connection without selecting database
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        printMessage("Connected to MySQL server successfully", 'success');
        
        // Read SQL file
        $sqlFile = __DIR__ . '/../sql/schema.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL schema file not found at: $sqlFile");
        }
        
        printMessage("Found SQL schema file", 'success');
        
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && 
                       !preg_match('/^--/', $stmt) && 
                       !preg_match('/^\/\*/', $stmt);
            }
        );
        
        printMessage("Found " . count($statements) . " SQL statements to execute", 'info');
        
        // Execute each statement
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            // Skip DELIMITER statements (they're for stored procedures)
            if (stripos($statement, 'DELIMITER') === 0) {
                continue;
            }
            
            // Clean up statement for execution
            $statement = preg_replace('/DELIMITER\s+\S+/i', '', $statement);
            $statement = trim($statement);
            
            if (empty($statement)) {
                continue;
            }
            
            if ($conn->query($statement)) {
                $successCount++;
                
                // Extract table/procedure name for logging
                if (preg_match('/CREATE\s+(?:TABLE|DATABASE|VIEW|PROCEDURE)\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"\']?(\w+)[`"\']?/i', $statement, $matches)) {
                    $name = $matches[1];
                    printMessage("Created: $name", 'success');
                }
            } else {
                $errorCount++;
                printMessage("Error executing statement: " . $conn->error, 'error');
            }
        }
        
        printMessage("\n========================================", 'info');
        printMessage("Installation Summary", 'info');
        printMessage("========================================", 'info');
        printMessage("Successful operations: $successCount", 'success');
        printMessage("Failed operations: $errorCount", $errorCount > 0 ? 'warning' : 'success');
        
        if ($errorCount === 0) {
            printMessage("\n✅ Database installation completed successfully!", 'success');
        } else {
            printMessage("\n⚠️ Database installation completed with errors", 'warning');
        }
        
        $conn->close();
        
        return $errorCount === 0;
        
    } catch (Exception $e) {
        printMessage("Fatal error: " . $e->getMessage(), 'error');
        return false;
    }
}

function resetDatabase() {
    global $isCli;
    
    printMessage("Dropping existing database...", 'warning');
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->query("DROP DATABASE IF EXISTS " . DB_NAME);
    printMessage("Database dropped", 'success');
    
    $conn->close();
    
    return installDatabase();
}

// Handle command line arguments
if ($isCli) {
    $action = $argv[1] ?? 'install';
    
    switch ($action) {
        case 'install':
            installDatabase();
            break;
        case 'reset':
            printMessage("WARNING: This will delete all data!", 'warning');
            echo "Are you sure? (yes/no): ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            if (trim($line) === 'yes') {
                resetDatabase();
            } else {
                printMessage("Reset cancelled", 'info');
            }
            fclose($handle);
            break;
        case 'help':
            echo "\nUsage: php scripts/install-db.php [action]\n\n";
            echo "Actions:\n";
            echo "  install  - Install database (default)\n";
            echo "  reset    - Drop and reinstall database\n";
            echo "  help     - Show this help message\n\n";
            break;
        default:
            printMessage("Unknown action: $action", 'error');
            echo "Use 'php scripts/install-db.php help' for usage information\n";
    }
} else {
    // Browser interface
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Installation</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #0078d4;
                margin-bottom: 10px;
            }
            .alert {
                padding: 15px;
                margin: 10px 0;
                border-radius: 4px;
                border-left: 4px solid;
            }
            .alert-info {
                background: #e3f2fd;
                border-color: #2196f3;
                color: #1976d2;
            }
            .alert-success {
                background: #e8f5e9;
                border-color: #4caf50;
                color: #388e3c;
            }
            .alert-danger {
                background: #ffebee;
                border-color: #f44336;
                color: #d32f2f;
            }
            .alert-warning {
                background: #fff3e0;
                border-color: #ff9800;
                color: #f57c00;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                margin: 10px 5px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
            }
            .btn-primary {
                background: #0078d4;
                color: white;
            }
            .btn-primary:hover {
                background: #005a9e;
            }
            .btn-danger {
                background: #d13438;
                color: white;
            }
            .btn-danger:hover {
                background: #b32d30;
            }
            .actions {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #eee;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🗄️ Database Installation</h1>
            <p>School Attendance System v1.0.0</p>
            
            <?php
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'install') {
                    installDatabase();
                } elseif ($_POST['action'] === 'reset') {
                    resetDatabase();
                }
            } else {
            ?>
                <div class="alert alert-info">
                    <strong>Informasi:</strong><br>
                    Script ini akan menginstall database untuk Sistem Absensi Siswa.<br>
                    Pastikan konfigurasi database di <code>config/database.php</code> sudah benar.
                </div>
                
                <div class="actions">
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="install">
                        <button type="submit" class="btn btn-primary">
                            📥 Install Database
                        </button>
                    </form>
                    
                    <form method="post" style="display: inline;" onsubmit="return confirm('PERINGATAN: Semua data akan dihapus! Lanjutkan?');">
                        <input type="hidden" name="action" value="reset">
                        <button type="submit" class="btn btn-danger">
                            🔄 Reset Database
                        </button>
                    </form>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <h3>Cara Menggunakan:</h3>
                    <ol>
                        <li>Pastikan MySQL/MariaDB sudah berjalan</li>
                        <li>Edit file <code>config/database.php</code> sesuai konfigurasi database Anda</li>
                        <li>Klik tombol "Install Database"</li>
                        <li>Database akan dibuat otomatis beserta tabel dan data awalnya</li>
                    </ol>
                    
                    <h3>Default Login:</h3>
                    <ul>
                        <li><strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></li>
                        <li><strong>Petugas:</strong> username: <code>petugas</code>, password: <code>petugas123</code></li>
                    </ul>
                </div>
            <?php } ?>
        </div>
    </body>
    </html>
    <?php
}
?>

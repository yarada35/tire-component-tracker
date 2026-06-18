<?php
session_start();

// 1. Cloud Database Connection Configurations
$host = 'gateway01.us-east-1.prod.aws.tidbcloud.com'; 
$port = '4000'; 
$db   = 'test'; 
$charset = 'utf8mb4'
$user = '2RGpP9EW5P9nkQ7.root'; // Paste your exact TiDB User string here
$pass = 'dCgJ0E3wwhLePo9L'; // Paste your exact TiDB Password here

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_SSL_CA       => '/etc/ssl/certs/ca-certificates.crt', 
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     echo "<div style='color:#721c24; background-color:#f8d7da; border:2px solid #f5c6cb; padding:20px; margin:20px; border-radius:8px; font-family:sans-serif;'>";
     echo "<h2 style='margin-top:0;'>🚫 Cloud Database Connection Failed</h2>";
     echo "<p>The server responded with the following issue:</p>";
     echo "<pre style='background:#fff; padding:15px; border-radius:4px; border:1px solid #ced4da; white-space:pre-wrap;'><strong>" . htmlspecialchars($e->getMessage()) . "</strong></pre>";
     echo "</div>";
     exit;
}

// 2. Shift Authentication Processor (Login Gate)
if (isset($_POST['login_action'])) {
    $selected_shift = $_POST['login_shift'];
    $entered_pass = $_POST['login_password'];

    $stmt = $pdo->prepare("SELECT shift_password FROM shift_auth WHERE shift_name = ?");
    $stmt->execute([$selected_shift]);
    $auth = $stmt->fetch();

    if ($auth && $entered_pass === $auth['shift_password']) {
        $_SESSION['authenticated_shift'] = $selected_shift;
        header("Location: index.php");
        exit;
    } else {
        $login_error = "Invalid password for Shift $selected_shift";
    }
}

// 3. Shift Exit Processor (Logout)
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// 4. Production Data Submission Processor (Saves Data to Cloud)
if (isset($_POST['action']) && $_POST['action'] === 'save') {
    $record_date = $_POST['record_date'];
    $shift = $_POST['shift'];
    
    foreach ($_POST['components'] as $component_id => $metrics) {
        $prod = floatval($metrics['production']);
        $waste = floatval($metrics['wastage']);
        $rework = floatval($metrics['rework']);
        
        // Inserts metric log or updates existing production log if entry already exists
        $stmt = $pdo->prepare("INSERT INTO shift_records (record_date, shift, component_id, production_qty, wastage_qty, rework_qty) 
            VALUES (?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE production_qty = VALUES(production_qty), wastage_qty = VALUES(wastage_qty), rework_qty = VALUES(rework_qty)");
        $stmt->execute([$record_date, $shift, $component_id, $prod, $waste, $rework]);
    }
    
    header("Location: index.php?success=1");
    exit;
}

// 5. App Core Framework & Analytics Data Fetching
$components = [];
$reports = [];

if (isset($_SESSION['authenticated_shift'])) {
    // Collect raw tire components configured inside your cloud space
    $components = $pdo->query("SELECT * FROM components")->fetchAll();
    
    // Process historic shift calculations with unified metric parameters
    $report_query = "
        SELECT 
            r.record_date, r.shift, c.component_name, c.unit_type,
            r.production_qty AS native_prod,
            (r.production_qty * c.conversion_factor_to_kg) AS prod_kg,
            r.wastage_qty,
            r.rework_qty
        FROM shift_records r
        JOIN components c ON r.component_id = c.id
        ORDER BY r.record_date DESC, r.shift ASC, c.id ASC
    ";
    $reports = $pdo->query($report_query)->fetchAll();

    // Safety Interceptor: Prevents zero division crashes inside the frontend matrix loop
    foreach ($reports as &$row) {
        if (floatval($row['prod_kg']) == 0) {
            $row['prod_kg'] = 0.0001; 
        }
    }
    unset($row); 
}
?>

<?php
// db.php
$host = 'gateway01.us-east-1.prod.aws.tidbcloud.com'; // Your exact TiDB Host string
$port = '4000'; 
$db   = 'test'; // Default TiDB database name
$user = '2RGpP9EW5P9nkQ7.root'; // Make sure the prefix string and .root are exactly what TiDB shows
$pass = 'dCgJ0E3wwhLePo9L'; // Ensure NO spaces at the end and no accidental capital letters
$charset = 'utf8mb4';

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
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
// ... the rest of your code remains exactly the same ...
?>

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Handler for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $date = $_POST['record_date'];
    $shift = $_POST['shift'];
    
    foreach ($_POST['components'] as $component_id => $data) {
        $prod = floatval($data['production']);
        $wastage = floatval($data['wastage']);
        $rework = floatval($data['rework']);
        
        $stmt = $pdo->prepare("INSERT INTO shift_records (record_date, shift, component_id, production_qty, wastage_qty, rework_qty) 
            VALUES (?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE production_qty = VALUES(production_qty), wastage_qty = VALUES(wastage_qty), rework_qty = VALUES(rework_qty)");
        $stmt->execute([$date, $shift, $component_id, $prod, $wastage, $rework]);
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
    exit;
}

// Fetch components for the form
$components = $pdo->query("SELECT * FROM components")->fetchAll();

// Fetch reports with dynamic conversion calculations
$report_query = "
    SELECT 
        r.record_date, r.shift, c.component_name, c.unit_type,
        r.production_qty AS native_prod,
        (r.production_qty * c.conversion_factor_to_kg) AS prod_kg,
        r.wastage_qty AS wastage_kg,
        r.rework_qty AS rework_kg,
        IF((r.production_qty * c.conversion_factor_to_kg) > 0, (r.wastage_qty / (r.production_qty * c.conversion_factor_to_kg)) * 100, 0) AS wastage_pct,
        IF((r.production_qty * c.conversion_factor_to_kg) > 0, (r.rework_qty / (r.production_qty * c.conversion_factor_to_kg)) * 100, 0) AS rework_pct
    FROM shift_records r
    JOIN components c ON r.component_id = c.id
    ORDER BY r.record_date DESC, r.shift ASC, c.id ASC
";
$reports = $pdo->query($report_query)->fetchAll();
?>

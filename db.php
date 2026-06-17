<?php
// db.php
$host = 'gateway01.eu-central-1.prod.aws.tidbcloud.com'; // Paste your TiDB Host here
$port = '4000'; 
$db   = 'test'; // Default TiDB database name
$user = '2RGpP9EW5P9nkQ7.root'; // Paste your exact TiDB User here
$pass = 'dCgJ0E3wwhLePo9L'; // Paste your exact TiDB Password here
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
     // Elegant mobile debug mode: prints the exact database issue on your screen
     echo "<div style='color:#721c24; background-color:#f8d7da; border:2px solid #f5c6cb; padding:20px; margin:20px; border-radius:8px; font-family:sans-serif;'>";
     echo "<h2 style='margin-top:0;'>🚫 Database Connection Failed</h2>";
     echo "<p>The server responded with the following exact error:</p>";
     echo "<pre style='background:#fff; padding:15px; border-radius:4px; border:1px solid #ced4da; overflow-x:auto; white-space:pre-wrap;'><strong>" . htmlspecialchars($e->getMessage()) . "</strong></pre>";
     echo "<p>💡 <b>What to check:</b> Ensure your User string contains the prefix numbers, your password has no extra trailing spaces, and your quotes are straight lines (<code>'</code>) rather than smart curly lines (<code>‘</code>).</p>";
     echo "</div>";
     exit; // Stop executing the page
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

<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HORIZON ADDIS TYRE - Secure Access</title>
    <style>
        :root { --primary: #1a1c2e; --secondary: #00f2fe; --accent: #f35588; }
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        body { 
            background: linear-gradient(-45deg, #1a1c2e, #3a1c41, #0d324d, #1f4068);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        @keyframes gradientAnimation { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .login-card, .dashboard-container { 
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);
            border-radius: 20px; padding: 30px; width: 100%; max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; color: #333;
        }
        .dashboard-container { max-width: 1200px; align-self: flex-start; }
        h1 { color: var(--primary); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 2px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input, select { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; font-size: 1rem; }
        .btn { 
            background: linear-gradient(135deg, #00f2fe, #4facfe); color: white; border: none; 
            padding: 15px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase;
        }
        .error { color: red; margin-bottom: 15px; font-weight: bold; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .logout-btn { color: var(--accent); text-decoration: none; font-weight: bold; }
        
        /* Table & Form styles reused from previous colorful version */
        .component-row { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 12px; margin-bottom: 15px; text-align: left; }
        .inputs-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .table-responsive { overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th { background: #1a1c2e; color: white; padding: 10px; }
        td { border-bottom: 1px solid #eee; padding: 10px; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['authenticated_shift'])): ?>
    <div class="login-card">
        <img src="https://via.placeholder.com/100x50?text=HORIZON" alt="Logo" style="margin-bottom:15px;">
        <h1>Shift Login</h1>
        <p style="margin-bottom:20px; opacity:0.7;">Enter credentials to access Loss Matrix</p>
        
        <?php if (isset($login_error)): ?>
            <div class="error"><?php echo $login_error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="login_action" value="1">
            <div class="input-group">
                <label>Select Your Shift</label>
                <select name="login_shift">
                    <option value="A">Shift A</option>
                    <option value="B">Shift B</option>
                    <option value="C">Shift C</option>
                </select>
            </div>
            <div class="input-group">
                <label>Entry Password</label>
                <input type="password" name="login_password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">Unlock Dashboard</button>
        </form>
    </div>

<?php else: ?>
    <div class="dashboard-container">
        <div class="header-bar">
            <div>
                <h1>HORIZON ADDIS</h1>
                <p>Shift <strong><?php echo $_SESSION['authenticated_shift']; ?></strong> Personnel Active</p>
            </div>
            <a href="?logout=1" class="logout-btn">Log Out</a>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px;">
            <div class="entry-form">
                <form method="POST">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="shift" value="<?php echo $_SESSION['authenticated_shift']; ?>">
                    
                    <div class="input-group">
                        <label>Date</label>
                        <input type="date" name="record_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <?php foreach ($components as $comp): ?>
                        <div class="component-row">
                            <strong><?php echo $comp['component_name']; ?></strong>
                            <div class="inputs-3">
                                <input type="number" step="0.01" name="components[<?php echo $comp['id']; ?>][production]" placeholder="Prod" required>
                                <input type="number" step="0.01" name="components[<?php echo $comp['id']; ?>][wastage]" placeholder="Waste KG" required>
                                <input type="number" step="0.01" name="components[<?php echo $comp['id']; ?>][rework]" placeholder="Rework KG" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn">Save Shift Data</button>
                </form>
            </div>

            <div class="table-responsive">
                <h2 style="margin-bottom:10px;">Recent Loss Records</h2>
                <table>
                    <thead>
                        <tr><th>Date/Shift</th><th>Component</th><th>Prod KG</th><th>Waste %</th><th>Rework %</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $row): ?>
                            <tr>
                                <td><?php echo $row['record_date']; ?> (<?php echo $row['shift']; ?>)</td>
                                <td><?php echo $row['component_name']; ?></td>
                                <td><?php echo number_format($row['prod_kg'], 2); ?></td>
                                <td style="color:red;"><?php echo number_format(($row['wastage_qty']/$row['prod_kg'])*100, 2); ?>%</td>
                                <td style="color:orange;"><?php echo number_format(($row['rework_qty']/$row['prod_kg'])*100, 2); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>

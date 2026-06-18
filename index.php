<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HORIZON ADDIS TYRE - Loss Matrix Tracker</title>
    <style>
        :root {
            --primary: #1a1c2e;
            --secondary: #00f2fe;
            --accent: #f35588;
            --text-dark: #2d3748;
        }
        * { box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; padding: 0; }
        
        /* Vibrant, smooth animated gradient background */
        body { 
            background: linear-gradient(-45deg, #1a1c2e, #3a1c41, #0d324d, #1f4068);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #fff; 
            padding: 15px;
            min-height: 100vh;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container { max-width: 1200px; margin: 0 auto; }
        
        /* Frosted glass header */
        header { 
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white; 
            padding: 25px 15px; 
            border-radius: 16px; 
            margin-bottom: 25px; 
            text-align: center;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }
        header h1 { font-size: 1.8rem; letter-spacing: 2px; text-transform: uppercase; color: var(--secondary); text-shadow: 0 0 10px rgba(0,242,254,0.5); }
        header p { font-size: 0.9rem; opacity: 0.8; margin-top: 5px; color: #fff; }

        .grid-layout { display: grid; grid-template-columns: 1fr; gap: 25px; }
        @media (min-width: 900px) { .grid-layout { grid-template-columns: 1fr 1.5fr; } }
        
        /* Sleek Glassmorphism Cards */
        .card { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-radius: 16px; 
            padding: 20px;
            color: var(--text-dark);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        h2 { margin-bottom: 20px; color: var(--primary); font-size: 1.3rem; border-bottom: 3px solid var(--secondary); padding-bottom: 8px;}
        h3 { color: var(--primary); font-size: 1.1rem; margin: 20px 0 10px 0; }
        
        .form-group { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        label { font-weight: 700; display: block; margin-bottom: 5px; font-size: 0.85rem; color: #4a5568; }
        input, select { width: 100%; padding: 12px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; background: #f7fafc; color: var(--text-dark); }
        input:focus, select:focus { border-color: var(--secondary); outline: none; background: #fff; }
        
        /* Component Row Styling */
        .component-row { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 12px; margin-bottom: 15px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
        .component-title { font-weight: 700; color: #1a202c; margin-bottom: 12px; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center; }
        .inputs-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        
        /* Vibrant Buttons */
        .btn { background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%); color: white; border: none; padding: 15px; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; width: 100%; transition: 0.3s; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4); text-transform: uppercase; letter-spacing: 1px;}
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79, 172, 254, 0.6); }
        
        /* Table Styles */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85rem; }
        th, td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #1a1c2e; color: white; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        tr:hover { background-color: #f8fafc; }
        
        .alert { background: #c6f6d5; color: #22543d; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; border-left: 5px solid #38a169; }
        .badge { background: #e2e8f0; padding: 4px 8px; border-radius: 20px; font-size: 0.7rem; color: #4a5568; font-weight: 600; }
        .pct-red { color: #e53e3e; font-weight: 700; }
        .pct-orange { color: #dd6b20; font-weight: 700; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>HORIZON ADDIS TYRE</h1>
        <p>Product Industrialization & QA — Component Loss Matrix Dashboard</p>
    </header>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert">✓ Entry recorded successfully! Production metrics updated and cloud logs synced.</div>
    <?php endif; ?>

    <div class="grid-layout">
        <div class="card">
            <h2>Data Entry Form</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="save">
                
                <div class="form-group">
                    <div>
                        <label>Date</label>
                        <input type="date" name="record_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label>Shift</label>
                        <select name="shift" required>
                            <option value="A">Shift A</option>
                            <option value="B">Shift B</option>
                            <option value="C">Shift C</option>
                        </select>
                    </div>
                </div>

                <h3>Component Metrics Input</h3>
                <p style="font-size:0.75rem; color:#718096; margin-bottom:15px;">Enter production metrics below. Wastage and Rework must be entered in <b>Kilograms (KG)</b>.</p>

                <?php foreach ($components as $comp): ?>
                    <div class="component-row">
                        <div class="component-title">
                            <?php echo htmlspecialchars($comp['component_name']); ?> 
                            <span class="badge"><?php echo ucfirst($comp['unit_type']); ?>s</span>
                        </div>
                        <div class="inputs-3">
                            <div>
                                <label style="font-size: 0.7rem;">Prod</label>
                                <input type="number" step="0.01" name="components[<?php echo $comp['id']; ?>][production]" required value="0">
                            </div>
                            <div>
                                <label style="font-size: 0.7 0.7rem;">Wastage (KG)</label>
                                <input type="number" step="0.01" name="components[<?php echo $comp['id']; ?>][wastage]" required value="0">
                            </div>
                            <div>
                                <label style="font-size: 0.7rem;">Rework (KG)</label>
                                <input type="number" step="0.01" name="components[<?php echo $comp['id']; ?>][rework]" required value="0">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn">Submit Shift Log</button>
            </form>
        </div>

        <div class="card">
            <h2>Shift Performance & Loss Matrix</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date / Shift</th>
                            <th>Component</th>
                            <th>Native Prod</th>
                            <th>Prod (KG)</th>
                            <th>Wastage (KG) / %</th>
                            <th>Rework (KG) / %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reports)): ?>
                            <tr><td colspan="6" style="text-align:center; color:#a0aec0; padding: 30px 0;">No shift records submitted yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reports as $row): ?>
                                <tr>
                                    <td><strong><?php echo $row['record_date']; ?></strong><br><span class="badge" style="background:#1a1c2e; color:#fff;">Shift <?php echo $row['shift']; ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($row['component_name']); ?></strong></td>
                                    <td><?php echo number_format($row['native_prod'], 1) . ' ' . $row['unit_type']; ?></td>
                                    <td><strong><?php echo number_format($row['prod_kg'], 2); ?> kg</strong></td>
                                    <td>
                                        <?php echo number_format($row['wastage_kg'], 2); ?> kg<br>
                                        <small class="pct-red"><?php echo number_format($row['wastage_pct'], 2); ?>%</small>
                                    </td>
                                    <td>
                                        <?php echo number_format($row['rework_kg'], 2); ?> kg<br>
                                        <small class="pct-orange"><?php echo number_format($row['rework_pct'], 2); ?>%</small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>

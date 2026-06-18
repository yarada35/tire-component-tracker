<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HORIZON ADDIS TYRE - Loss Matrix Tracker</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #18bc9c;
            --background: #f8f9fa;
            --text: #333;
        }
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        body { background-color: var(--background); color: var(--text); padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        header { background: var(--primary); color: white; padding: 20px; border-radius: 8px; margin-bottom: 25px; text-align: center; }
        .grid-layout { display: grid; grid-template-columns: 1fr; gap: 25px; }
        @media (min-width: 900px) { .grid-layout { grid-template-columns: 1fr 2fr; } }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { margin-bottom: 15px; color: var(--primary); font-size: 1.4rem; border-bottom: 2px solid #eee; padding-bottom: 5px;}
        .form-group { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; font-size: 0.9rem; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; }
        .component-row { background: #fdfdfd; border: 1px solid #eaeaea; padding: 15px; border-radius: 6px; margin-bottom: 15px; }
        .component-title { font-weight: bold; color: #444; margin-bottom: 10px; font-size: 1rem; }
        .inputs-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .btn { background: var(--secondary); color: white; border: none; padding: 12px 20px; font-size: 1rem; border-radius: 4px; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn:hover { opacity: 0.9; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9rem; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: var(--primary); color: white; }
        tr:hover { background-color: #f1f1f1; }
        .alert { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        .badge { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; color: #4a5568; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>HORIZON ADDIS TYRE</h1>
        <p>Product Industrialization & QA - Component Loss Matrix</p>
    </header>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert">✓ Entry recorded successfully! Production metrics updated and converted.</div>
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
                <p style="font-size:0.8rem; color:#777; margin-bottom:15px;">Wastage and Rework values must be entered directly in <b>Kilograms (KG)</b>.</p>

                <?php foreach ($components as $comp): ?>
                    <div class="component-row">
                        <div class="component-title">
                            <?php echo htmlspecialchars($comp['component_name']); ?> 
                            <span class="badge">Measured in: <?php echo ucfirst($comp['unit_type']); ?>s</span>
                        </div>
                        <div class="inputs-3">
                            <div>
                                <label style="font-size: 0.75rem;">Prod (<?php echo $comp['unit_type']; ?>)</label>
                                <input type="number" step="0.01" name="components[<?php echo $comp['id']; ?>][production]" required value="0">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem;">Wastage (KG)</label>
                                <input type="number" step="0.01" name="components[<?php echo $comp['id']; ?>][wastage]" required value="0">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem;">Rework (KG)</label>
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
                            <tr><td colspan="6" style="text-align:center; color:#999;">No shift logs submitted yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reports as $row): ?>
                                <tr>
                                    <td><strong><?php echo $row['record_date']; ?></strong> <br> Shift <?php echo $row['shift']; ?></td>
                                    <td><?php echo htmlspecialchars($row['component_name']); ?></td>
                                    <td><?php echo number_format($row['native_prod'], 1) . ' ' . $row['unit_type']; ?></td>
                                    <td><strong><?php echo number_format($row['prod_kg'], 2); ?> kg</strong></td>
                                    <td>
                                        <?php echo number_format($row['wastage_kg'], 2); ?> kg <br>
                                        <small style="color:red; font-weight:bold;"><?php echo number_format($row['wastage_pct'], 2); ?>%</small>
                                    </td>
                                    <td>
                                        <?php echo number_format($row['rework_kg'], 2); ?> kg <br>
                                        <small style="color:orange; font-weight:bold;"><?php echo number_format($row['rework_pct'], 2); ?>%</small>
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

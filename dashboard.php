<?php
require_once 'includes/auth_check.php';
require_once 'includes/data.php';

$students_all = getStudents();

// Get unique classes and years for filters
$classes = array_unique(array_column($students_all, 'class_display'));
$years = array_unique(array_column($students_all, 'academic_year'));
if (empty($years)) { $years = [$school_details['year']]; }

// Filters
$f_search = $_GET['search'] ?? '';
$f_class = $_GET['class'] ?? '';
$f_year = $_GET['year'] ?? '';

$students = array_filter($students_all, function($s) use ($f_search, $f_class, $f_year) {
    $match = true;
    if ($f_search && (strpos(strtolower($s['name']), strtolower($f_search)) === false && $s['roll_no'] != $f_search)) $match = false;
    if ($f_class && $s['class_display'] !== $f_class) $match = false;
    if ($f_year && ($s['academic_year'] ?? '') !== $f_year) $match = false;
    return $match;
});

// Sort by Roll Number (Numeric)
usort($students, function($a, $b) {
    return (int)$a['roll_no'] - (int)$b['roll_no'];
});

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $students_save = array_filter($students_all, function($s) use ($id) { return $s['id'] != $id; });
    saveStudents(array_values($students_save));
    header("Location: dashboard.php");
    exit;
}

// Bulk Print Link
$print_all_link = "index.php?" . http_build_query(['class' => $f_class, 'year' => $f_year, 'search' => $f_search, 'bulk' => 1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - J.P. ACADEMY</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .filter-item input, .filter-item select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-search { background: #1a237e; color: white; }
        .btn-add { background: green; color: white; }
        .btn-print { background: #ff9800; color: white; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table th, table td { border: 1px solid #eee; padding: 12px; text-align: left; }
        table th { background: #1a237e; color: white; }
        
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-printed { background: #e8f5e9; color: #2e7d32; border: 1px solid #2e7d32; }
        .status-pending { background: #fff3e0; color: #ef6c00; border: 1px solid #ef6c00; }
    </style>
</head>
<body>
    <div class="no-print-header" style="background: #1a237e; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: bold; font-size: 20px;">Admin Dashboard | J.P. ACADEMY</span>
        <a href="logout.php" style="color: white; text-decoration: none; background: red; padding: 8px 15px; border-radius: 5px; font-weight: bold;">Logout</a>
    </div>

    <div class="admin-container">
        <form action="" method="GET" class="filter-grid">
            <div class="filter-item">
                <label>Search Student</label>
                <input type="text" name="search" placeholder="Name or Roll No" value="<?php echo htmlspecialchars($f_search); ?>">
            </div>
            <div class="filter-item">
                <label>Class Filter</label>
                <select name="class">
                    <option value="">-- All Classes --</option>
                    <?php foreach($classes as $c): ?>
                        <option value="<?php echo $c; ?>" <?php echo ($f_class == $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <label>Year Filter</label>
                <select name="year">
                    <option value="">-- All Years --</option>
                    <?php foreach($years as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($f_year == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 10px;">
                <button type="submit" class="btn btn-search"><i class="fas fa-filter"></i> Apply</button>
                <a href="dashboard.php" class="btn" style="background: #ccc; color: #333;">Clear</a>
            </div>
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3>Results (<?php echo count($students); ?> Students)</h3>
            <div style="display: flex; gap: 10px;">
                <a href="<?php echo $print_all_link; ?>" class="btn btn-print" target="_blank"><i class="fas fa-print"></i> Print This View</a>
                <a href="manage_student.php" class="btn btn-add"><i class="fas fa-plus"></i> Add Student</a>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Academic Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 30px;">No students found matching your filters.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td style="font-weight: bold;"><?php echo $s['roll_no']; ?></td>
                        <td><?php echo strtoupper($s['name']); ?></td>
                        <td><?php echo $s['class_display']; ?></td>
                        <td><?php echo $s['academic_year'] ?? $school_details['year']; ?></td>
                        <td>
                            <?php if (isset($s['printed']) && $s['printed']): ?>
                                <span class="status-badge status-printed"><i class="fas fa-check-circle"></i> Printed</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="index.php?id=<?php echo $s['id']; ?>" class="btn" style="background: #009688; color: white; padding: 5px 10px; font-size: 13px;" target="_blank"><i class="fas fa-file-invoice"></i> Result</a>
                            <a href="manage_student.php?id=<?php echo $s['id']; ?>" class="btn" style="background: orange; color: white; padding: 5px 10px; font-size: 13px;"><i class="fas fa-edit"></i> Edit</a>
                            <a href="dashboard.php?delete=<?php echo $s['id']; ?>" class="btn" style="background: red; color: white; padding: 5px 10px; font-size: 13px;" onclick="return confirm('Delete Student?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
require_once 'includes/auth_check.php';
require_once 'includes/data.php';

$students = getStudents();
$id = $_GET['id'] ?? null;
$student = null;
if ($id) { foreach ($students as $s) { if ($s['id'] == $id) { $student = $s; break; } } }

$cur_class = $_GET['class'] ?? ($student['class_type'] ?? '4-7');

if (!$student) {
    $student = [
        'id' => time(), 'roll_no' => '', 'section' => 'A', 'class_type' => $cur_class, 'class_display' => $cur_class,
        'academic_year' => $school_details['year'],
        'name' => '', 'father_name' => '', 'address' => '', 'marks' => [],
        'co_scholastic' => ['WORKING EDUCATION' => '', 'CLEANNESS' => '', 'HEALTH & PHYSICAL EDUCATION' => '', 'DISCIPLINE/ BEHAVIOUR' => ''],
        'remarks' => '', 'promoted_to' => '', 'date_of_issue' => date('d/m/Y'), 'printed' => false, 'manual_rank' => ''
    ];
}

if (isset($_POST['save'])) {
    $data = $_POST;
    $new_student = $student;
    $fields = ['roll_no', 'section', 'class_display', 'academic_year', 'name', 'father_name', 'address', 'remarks', 'promoted_to', 'date_of_issue', 'manual_rank'];
    foreach ($fields as $f) { $new_student[$f] = $data[$f]; }
    $new_student['class_type'] = $data['class_type'];
    
    $new_marks = [];
    $subjects = $MASTER_SUBJECTS[$data['class_type']];
    $exam_slots = ['ut1', 'ut2', 'hy', 'ut3', 'ut4', 'annual'];
    foreach ($subjects as $sub_name => $meta) {
        $actual_name = is_array($meta) ? $sub_name : $meta;
        if (isset($meta['is_main'])) { $new_marks[$actual_name] = ['is_main' => true]; }
        else {
            $safe_name = str_replace([' ', '.', '-'], '_', $actual_name);
            $sub_marks = [];
            foreach ($exam_slots as $slot) {
                $val_obt = $data['m_'.$safe_name.'_'.$slot.'_obt'] ?? '';
                $val_max = $data['m_'.$safe_name.'_'.$slot.'_max'] ?? '';
                $sub_marks[$slot.'_obt'] = ($val_obt === '') ? '' : (int)$val_obt;
                $sub_marks[$slot.'_max'] = ($val_max === '') ? '' : (int)$val_max;
            }
            $new_marks[$actual_name] = $sub_marks;
        }
    }
    $new_student['marks'] = $new_marks;
    $new_student['co_scholastic'] = [
        'WORKING EDUCATION' => $data['cs_we'], 'CLEANNESS' => $data['cs_cl'], 
        'HEALTH & PHYSICAL EDUCATION' => $data['cs_hp'], 'DISCIPLINE/ BEHAVIOUR' => $data['cs_db']
    ];
    if ($id) { foreach ($students as &$s) { if ($s['id'] == $id) { $s = $new_student; break; } } }
    else { $students[] = $new_student; }
    saveStudents($students);
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student - J.P. ACADEMY</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { margin: 0; padding: 0; background: #f0f2f5; font-family: sans-serif; overflow-x: hidden; }
        .no-print-header { background: #1a237e; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .form-container { width: 100%; max-width: 1000px; margin: 20px auto; padding: 15px; box-sizing: border-box; }
        .card-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .grid-row { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; }
        .grid-item { flex: 1; min-width: 240px; }
        .grid-item label { font-weight: bold; display: block; margin-bottom: 5px; font-size: 14px; }
        .grid-item input, .grid-item select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .subjects-wrapper { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; }
        .subject-card { background: #fafafa; border: 1px solid #ddd; padding: 15px; border-radius: 6px; }
        .subject-card h4 { margin: 0 0 10px 0; color: #1a237e; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .marks-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .mark-input-pair { display: flex; align-items: center; gap: 3px; }
        .mark-input-pair input { padding: 6px !important; font-size: 12px; text-align: center; width: 40px; flex: 1; }
        .obt-field { background: #fffde7 !important; border: 1px solid #fbc02d !important; }
        .max-field { background: #f1f8e9 !important; border: 1px solid #7cb342 !important; }
        .btn-save { background: #2e7d32; color: white; padding: 15px; border-radius: 5px; font-size: 18px; cursor: pointer; border: none; font-weight: bold; width: 100%; margin-top: 20px; }
        .calc-summary { background: #1a237e; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-around; font-weight: bold; font-size: 18px; position: sticky; top: 60px; z-index: 100; }
    </style>
</head>
<body>
    <div class="no-print-header">
        <span style="font-weight: bold;">Edit Student | J.P. ACADEMY</span>
        <a href="dashboard.php" style="color: white; text-decoration: none; font-size: 14px;">&larr; Dashboard</a>
    </div>

    <div class="form-container">
        <div class="calc-summary">
            <div>OBTAINED: <span id="sum-obt">0</span></div>
            <div>TOTAL: <span id="sum-max">0</span></div>
            <div>PERCENTAGE: <span id="sum-perc">0%</span></div>
        </div>

        <form action="" method="POST">
            <div class="card-box">
                <h3>Basic Details</h3>
                <div class="grid-row">
                    <div class="grid-item"><label>Class Format</label><select name="class_type" onchange="changeTemplate(this.value)"><?php foreach($MASTER_SUBJECTS as $ctype=>$subs){ $sel=($ctype==$cur_class)?'selected':''; echo "<option value='$ctype' $sel>$ctype</option>"; } ?></select></div>
                    <div class="grid-item"><label>Class Display Name</label><input type="text" name="class_display" value="<?php echo $student['class_display']; ?>" required></div>
                    <div class="grid-item"><label>Academic Year</label><input type="text" name="academic_year" value="<?php echo $student['academic_year'] ?? $school_details['year']; ?>" required></div>
                </div>
                <div class="grid-row">
                    <div class="grid-item"><label>Roll No</label><input type="text" name="roll_no" value="<?php echo $student['roll_no']; ?>" required></div>
                    <div class="grid-item"><label>Section</label><input type="text" name="section" value="<?php echo $student['section']; ?>" required></div>
                    <div class="grid-item"><label>Student's Name</label><input type="text" name="name" value="<?php echo $student['name']; ?>" required></div>
                </div>
                <div class="grid-row">
                    <div class="grid-item"><label>Father's Name</label><input type="text" name="father_name" value="<?php echo $student['father_name']; ?>" required></div>
                    <div class="grid-item" style="flex: 2;"><label>Address</label><input type="text" name="address" value="<?php echo $student['address']; ?>" required></div>
                </div>
            </div>

            <h3>Marks Entry (Obt / Max)</h3>
            <div class="subjects-wrapper">
                <?php 
                $subjects = $MASTER_SUBJECTS[$cur_class];
                foreach ($subjects as $sub_name => $meta):
                    $actual_name = is_array($meta) ? $sub_name : $meta;
                    if (isset($meta['is_main'])) { echo "<div style='grid-column: 1/-1; background: #eee; padding: 10px; margin: 10px 0; font-weight: bold; border-radius: 4px;'>$actual_name</div>"; continue; }
                    $safe_name = str_replace([' ', '.', '-'], '_', $actual_name);
                    $m = $student['marks'][$actual_name] ?? [];
                ?>
                <div class="subject-card">
                    <h4><?php echo $actual_name; ?></h4>
                    <div class="marks-grid">
                        <?php foreach($EXAM_CONFIG as $key => $cfg): ?>
                        <div>
                            <label style="font-size: 11px;"><?php echo $cfg['label']; ?></label>
                            <div class="mark-input-pair">
                                <input type="number" name="m_<?php echo $safe_name; ?>_<?php echo $key; ?>_obt" value="<?php echo $m[$key.'_obt'] ?? ''; ?>" class="obt-field mark-calc">
                                <span>/</span>
                                <input type="number" name="m_<?php echo $safe_name; ?>_<?php echo $key; ?>_max" value="<?php echo $m[$key.'_max'] ?? $cfg['max']; ?>" class="max-field mark-calc">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card-box" style="margin-top: 20px;">
                <h3>Other Info & Co-Scholastic</h3>
                <div class="grid-row">
                    <div class="grid-item"><label>Rank (Manual)</label><input type="text" name="manual_rank" placeholder="e.g. 1st or I" value="<?php echo $student['manual_rank'] ?? ''; ?>"></div>
                    <div class="grid-item"><label>Remarks</label><input type="text" name="remarks" value="<?php echo $student['remarks']; ?>"></div>
                    <div class="grid-item"><label>Promoted To</label><input type="text" name="promoted_to" value="<?php echo $student['promoted_to']; ?>"></div>
                </div>
                <div class="grid-row">
                    <div class="grid-item"><label>Date of Issue</label><input type="text" name="date_of_issue" value="<?php echo $student['date_of_issue']; ?>"></div>
                    <div class="grid-item"><label>Working Edu.</label><input type="text" name="cs_we" value="<?php echo $student['co_scholastic']['WORKING EDUCATION'] ?? ''; ?>"></div>
                    <div class="grid-item"><label>Cleanness</label><input type="text" name="cs_cl" value="<?php echo $student['co_scholastic']['CLEANNESS'] ?? ''; ?>"></div>
                </div>
                <div class="grid-row">
                    <div class="grid-item"><label>Health & PE</label><input type="text" name="cs_hp" value="<?php echo $student['co_scholastic']['HEALTH & PHYSICAL EDUCATION'] ?? ''; ?>"></div>
                    <div class="grid-item"><label>Discipline</label><input type="text" name="cs_db" value="<?php echo $student['co_scholastic']['DISCIPLINE/ BEHAVIOUR'] ?? ''; ?>"></div>
                </div>
            </div>

            <button type="submit" name="save" class="btn-save">Save Student Data</button>
        </form>
    </div>

    <script>
        function changeTemplate(c){ window.location.href='manage_student.php?id=<?php echo $id; ?>&class='+c; }
        
        function calculateTotals() {
            let totalObt = 0;
            let totalMax = 0;
            
            document.querySelectorAll('.subject-card').forEach(card => {
                card.querySelectorAll('.mark-input-pair').forEach(pair => {
                    const obtInput = pair.querySelector('.obt-field');
                    const maxInput = pair.querySelector('.max-field');
                    const obtVal = parseInt(obtInput.value);
                    const maxVal = parseInt(maxInput.value);
                    
                    if (!isNaN(obtVal)) {
                        totalObt += obtVal;
                        totalMax += isNaN(maxVal) ? 0 : maxVal;
                    }
                });
            });
            
            document.getElementById('sum-obt').innerText = totalObt;
            document.getElementById('sum-max').innerText = totalMax;
            const perc = totalMax > 0 ? ((totalObt / totalMax) * 100).toFixed(2) : 0;
            document.getElementById('sum-perc').innerText = perc + '%';
        }

        document.querySelectorAll('.mark-calc').forEach(input => {
            input.addEventListener('input', () => {
                if (input.classList.contains('obt-field')) {
                    const pair = input.closest('.mark-input-pair');
                    const maxInput = pair.querySelector('.max-field');
                    if (parseInt(input.value) > parseInt(maxInput.value)) {
                        input.value = maxInput.value;
                    }
                }
                calculateTotals();
            });
        });

        // Run once on load
        calculateTotals();
    </script>
</body>
</html>

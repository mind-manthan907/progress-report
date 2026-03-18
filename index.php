<?php
require_once 'includes/auth_check.php';
require_once 'includes/data.php';
require_once 'includes/functions.php';

$students_all = getStudents();
$target_id = $_GET['id'] ?? null;
$f_class = $_GET['class'] ?? '';
$f_year = $_GET['year'] ?? '';
$f_search = $_GET['search'] ?? '';

// Filter logic matching dashboard
$students = array_filter($students_all, function($s) use ($target_id, $f_class, $f_year, $f_search) {
    if ($target_id) return $s['id'] == $target_id;
    $match = true;
    if ($f_search && (strpos(strtolower($s['name']), strtolower($f_search)) === false && $s['roll_no'] != $f_search)) $match = false;
    if ($f_class && $s['class_display'] !== $f_class) $match = false;
    if ($f_year && ($s['academic_year'] ?? '') !== $f_year) $match = false;
    return $match;
});

// Sort by Roll Number
usort($students, function($a, $b) {
    return (int)$a['roll_no'] - (int)$b['roll_no'];
});

// Mark as printed
$changed = false;
foreach ($students_all as &$s) {
    foreach ($students as $printed_s) {
        if ($s['id'] == $printed_s['id'] && (!isset($s['printed']) || !$s['printed'])) {
            $s['printed'] = true;
            $changed = true;
        }
    }
}
if ($changed) { saveStudents($students_all); }

$CO_SCHOLASTIC_AREAS = ['WORKING EDUCATION', 'CLEANNESS', 'HEALTH & PHYSICAL EDUCATION', 'DISCIPLINE/ BEHAVIOUR'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Progress Report - J.P. ACADEMY</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        @media print {
            .no-print-header, .indiv-control { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .report-container { margin: 0 auto !important; box-shadow: none !important; page-break-after: always !important; border: 3px solid red !important; outline: 8px solid red !important; }
        }
        .btn-print { background: #ff9800; color: white; border: none; padding: 8px 15px; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .scale-control { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); padding: 5px 15px; border-radius: 20px; font-size: 14px; }
        
        .report-wrapper { position: relative; margin-bottom: 20px; text-align: center; }
        .indiv-control { 
            background: #f0f0f0; padding: 5px 10px; border-radius: 5px; 
            display: inline-flex; align-items: center; gap: 10px; margin-bottom: 5px;
            border: 1px solid #ccc; font-size: 12px; font-weight: bold;
        }
        .report-container { transform-origin: top center; transition: transform 0.2s; margin-top: 0 !important; }
    </style>
</head>
<body>

<div class="no-print-header" style="background: #1a237e; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 9999; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
    <span style="font-weight: 800; font-family: sans-serif;">REPORT VIEWER | J.P. ACADEMY</span>
    <div style="display: flex; gap: 20px; align-items: center;">
        <div class="scale-control">
            <label>Master Size:</label>
            <input type="range" id="masterZoom" min="0.5" max="1.2" step="0.01" value="0.95" oninput="updateAllScales(this.value)">
            <span id="masterVal">95%</span>
        </div>
        <button onclick="window.print()" class="btn-print">Print All</button>
        <a href="dashboard.php" class="btn btn-success" style="padding: 8px 15px;">Dashboard</a>
    </div>
</div>

<div class="report-scroll-wrapper">
<?php foreach ($students as $student): 
    $class_type = $student['class_type'] ?? '4-7';
    $subjects_list = $MASTER_SUBJECTS[$class_type] ?? $MASTER_SUBJECTS['4-7'];
?>
    <div class="report-wrapper" id="wrapper-<?php echo $student['id']; ?>">
        <div class="indiv-control">
            <span>Size:</span>
            <input type="range" class="indiv-zoom" min="0.5" max="1.2" step="0.01" value="0.95" oninput="updateSingleScale('<?php echo $student['id']; ?>', this.value)">
            <span class="indiv-val">95%</span>
        </div>

            <div class="report-container page-break student-card-<?php echo $student['id']; ?>">
            <div style="text-align: center;">
                <div style="position: relative; width: 200px; height: 200px; margin: 0 auto 10px;">
                    <img src="logo.png" alt="J.P. Academy Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <h1 class="school-name">J.P. ACADEMY</h1>
                <p class="school-address">SUARHA NEAR CHAKAILA KALWARI, DISTT- BASTI. (U.P.)</p>
                <div class="report-title-box">PROGRESS REPORT<br>ACADEMIC YEAR- <?php echo $student['academic_year'] ?? $school_details['year']; ?></div>
                <div style="margin: 30px auto; width: 90%; border: 3px solid #1a237e; overflow: hidden; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.15); border-radius: 15px;">
                    <div style="height: 450px; background: url('./school.jpeg') center/cover; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 20px;">
                        <div style="background: rgba(26, 35, 126, 0.85); color: white; padding: 10px 30px; border-radius: 50px; font-size: 24px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase;">J.P. ACADEMY CAMPUS</div>
                    </div>
                </div>
                <div class="footer-contact" style="margin-top: 50px; display: flex; justify-content: center; align-items: center; gap: 20px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="45">
                    <span style="font-size: 36px; color: #333; font-weight: 900; letter-spacing: 1px; font-family: sans-serif;"><?php echo implode(', ', $school_details['contacts']); ?></span>
                </div>
            </div>
        </div>

        <div class="report-container student-card-<?php echo $student['id']; ?>">
            <div class="section-header">STUDENTS DETAILS</div>
            <div style="text-align: center; font-size: 28px; font-weight: 900; margin-bottom: 15px;">CLASS :- <?php echo $student['class_display']; ?></div>
            <table class="student-details-table">
                <tr><td width="20%">ROLL NO</td><td width="25%"><?php echo $student['roll_no']; ?></td><td width="20%">SECTION</td><td width="35%"><?php echo $student['section']; ?></td></tr>
                <tr><td>STUDENT'S NAME</td><td colspan="3"><?php echo strtoupper($student['name']); ?></td></tr>
                <tr><td>FATHER'S NAME</td><td colspan="3"><?php echo strtoupper($student['father_name']); ?></td></tr>
                <tr><td>ADDRESS</td><td colspan="3"><?php echo strtoupper($student['address']); ?></td></tr>
            </table>

            <div style="text-align: center; font-size: 20px; font-weight: 900; border: 3px solid black; padding: 8px; margin: 20px 0; background: #fff;">ACHOVENMENT RECORD OF ANNUAL-EXAMINATION- (<?php echo $student['academic_year'] ?? $school_details['year']; ?>)</div>
            
            <table class="marks-table">
                <thead>
                    <tr>
                        <th rowspan="2" width="22%">SUBJECT</th>
                        <th colspan="3">TERM-I (<?php echo $EXAM_CONFIG['ut1']['max'] + $EXAM_CONFIG['ut2']['max'] + $EXAM_CONFIG['hy']['max']; ?> MARKS)</th>
                        <th rowspan="2" width="10%"><?php echo str_replace(' ', '<br>', $EXAM_CONFIG['ut3']['label']); ?><br>(<?php echo $EXAM_CONFIG['ut3']['max']; ?>)</th>
                        <th colspan="2">TERM-II (<?php echo $EXAM_CONFIG['ut4']['max'] + $EXAM_CONFIG['annual']['max']; ?> MARKS)</th>
                        <th rowspan="2" width="10%">GRAND<br>TOTAL<br>(200)</th>
                        <th rowspan="2" width="8%">GRADE</th>
                    </tr>
                    <tr>
                        <th width="10%"><?php echo str_replace(' ', '<br>', $EXAM_CONFIG['ut1']['label']); ?><br>(<?php echo $EXAM_CONFIG['ut1']['max']; ?>)</th>
                        <th width="10%"><?php echo str_replace(' ', '<br>', $EXAM_CONFIG['ut2']['label']); ?><br>(<?php echo $EXAM_CONFIG['ut2']['max']; ?>)</th>
                        <th width="10%"><?php echo str_replace(' ', '<br>', $EXAM_CONFIG['hy']['label']); ?><br>(<?php echo $EXAM_CONFIG['hy']['max']; ?>)</th>
                        <th width="10%"><?php echo str_replace(' ', '<br>', $EXAM_CONFIG['ut4']['label']); ?><br>(<?php echo $EXAM_CONFIG['ut4']['max']; ?>)</th>
                        <th width="10%"><?php echo str_replace(' ', '<br>', $EXAM_CONFIG['annual']['label']); ?><br>(<?php echo $EXAM_CONFIG['annual']['max']; ?>)</th>
                    </tr>
                </thead>                <tbody>
                    <?php 
                    $grand_total_obt_all = 0; $grand_total_max_all = 0; $has_data = false;
                    foreach ($subjects_list as $sub_name => $meta): 
                        $actual_name = is_array($meta) ? $sub_name : $meta;
                        $m = $student['marks'][$actual_name] ?? [];
                        if (isset($meta['is_main'])) { echo "<tr><td colspan='9' style='text-align: left; padding-left: 15px; font-weight: 900; background: #eee; font-size: 16px;'>$actual_name</td></tr>"; continue; }
                        $is_empty_subject = true; $slots = ['ut1', 'ut2', 'hy', 'ut3', 'ut4', 'annual'];
                        foreach($slots as $sl) { if(isset($m[$sl.'_obt']) && $m[$sl.'_obt'] !== '') { $is_empty_subject = false; $has_data = true; break; } }
                        $sub_obt = 0; $sub_max = 0;
                        if (!$is_empty_subject) {
                            $t1_obt = (int)($m['ut1_obt'] ?? 0) + (int)($m['ut2_obt'] ?? 0) + (int)($m['hy_obt'] ?? 0);
                            $t1_max = (int)($m['ut1_max'] ?? 20) + (int)($m['ut2_max'] ?? 20) + (int)($m['hy_max'] ?? 60);
                            $t2_obt = (int)($m['ut3_obt'] ?? 0) + (int)($m['ut4_obt'] ?? 0) + (int)($m['annual_obt'] ?? 0);
                            $t2_max = (int)($m['ut3_max'] ?? 20) + (int)($m['ut4_max'] ?? 20) + (int)($m['annual_max'] ?? 60);
                            $sub_obt = $t1_obt + $t2_obt; $sub_max = $t1_max + $t2_max;
                            $grand_total_obt_all += $sub_obt; $grand_total_max_all += $sub_max;
                        }
                        $is_sub = (strpos($actual_name, 'A-') === 0 || strpos($actual_name, 'B-') === 0 || strpos($actual_name, 'C-') === 0);
                        $grade = (!$is_empty_subject && $sub_max > 0) ? calculateGrade($sub_obt, $sub_max) : '';
                    ?>
                    <tr>
                        <td style="text-align: left; padding-left: <?php echo $is_sub ? '30px' : '10px'; ?>; <?php echo $is_sub ? 'font-style: italic;' : ''; ?>"><?php echo $actual_name; ?></td>
                        <td><?php echo (isset($m['ut1_obt']) && $m['ut1_obt'] !== '') ? $m['ut1_obt'] : ''; ?></td>
                        <td><?php echo (isset($m['ut2_obt']) && $m['ut2_obt'] !== '') ? $m['ut2_obt'] : ''; ?></td>
                        <td><?php echo (isset($m['hy_obt']) && $m['hy_obt'] !== '') ? $m['hy_obt'] : ''; ?></td>
                        <td><?php echo (isset($m['ut3_obt']) && $m['ut3_obt'] !== '') ? $m['ut3_obt'] : ''; ?></td>
                        <td><?php echo (isset($m['ut4_obt']) && $m['ut4_obt'] !== '') ? $m['ut4_obt'] : ''; ?></td>
                        <td><?php echo (isset($m['annual_obt']) && $m['annual_obt'] !== '') ? $m['annual_obt'] : ''; ?></td>
                        <td><?php echo (!$is_empty_subject) ? $sub_obt : ''; ?></td>
                        <td><?php echo $grade; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin-top: -3px;">
                <tr>
                    <td rowspan="4" width="55%" style="border: 3px solid black; vertical-align: top; padding: 0;">
                        <div style="font-size: 13px; font-weight: 900; padding: 8px; line-height: 1.3;"><?php echo getGradingScaleText(); ?></div>
                        <div style="text-align: center; border-top: 3px solid black; font-weight: 900; background: #f0f0f0; font-size: 16px; padding: 2px;">CO-SCHOOLASTIC AREAS</div>
                        <table style="width: 100%; border-collapse: collapse;">
                            <?php foreach ($CO_SCHOLASTIC_AREAS as $area): $grade = $student['co_scholastic'][$area] ?? ''; ?>
                            <tr><td style="border: none; padding: 4px 8px; font-size: 14px; font-weight: 900;"><?php echo $area; ?></td><td style="border-left: 3px solid black; text-align: center; width: 70px; font-weight: 900; font-size: 16px;"><?php echo $grade; ?></td></tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td style="border: 3px solid black; padding: 8px; font-weight: 900; font-size: 16px;" width="30%">OBTAINED MARKS</td>
                    <td style="border: 3px solid black; padding: 8px; font-weight: 900; font-size: 18px; text-align: center;"><?php echo $has_data ? $grand_total_obt_all : ''; ?></td>
                </tr>
                <tr><td style="border: 3px solid black; padding: 8px; font-weight: 900; font-size: 16px;">TOTAL MARKS</td><td style="border: 3px solid black; padding: 8px; font-weight: 900; font-size: 18px; text-align: center;"><?php echo $has_data ? $grand_total_max_all : ''; ?></td></tr>
                <tr><td style="border: 3px solid black; padding: 8px; font-weight: 900; font-size: 16px;">PERCENTAGE</td><td style="border: 3px solid black; padding: 8px; font-weight: 900; font-size: 18px; text-align: center;"><?php echo ($has_data && $grand_total_max_all > 0) ? number_format(($grand_total_obt_all / $grand_total_max_all) * 100, 2) . '%' : ''; ?></td></tr>
                <tr>
                    <td style="border: 3px solid black; padding: 8px; font-weight: 900; font-size: 16px;">RANK</td>
                    <td style="border: 3px solid black; padding: 8px; font-weight: 900; font-size: 18px; text-align: center;">
                        <?php 
                            if (isset($student['manual_rank']) && $student['manual_rank'] !== '') {
                                echo $student['manual_rank'];
                            } else {
                                echo $has_data ? calculateRank($student['id'], $students_all, $student['class_type']) : '';
                            }
                        ?>
                    </td>
                </tr>
                </table>            <div class="remarks-box" style="margin-top: 25px; border: 3px solid black; border-radius: 20px; padding: 20px; font-size: 18px; font-weight: 900; text-align: left;">
                <p style="margin-bottom: 10px;">CLASS TEACHER REMARKS :- <span style="font-style: italic; border-bottom: 1px dotted black; display: inline-block; min-width: 200px;"><?php echo $student['remarks'] ? $student['remarks'] : '..........................'; ?></span></p>
                <div style="display: flex; justify-content: space-between;">
                    <p>PROMOTED TO CLASS:- <span style="border-bottom: 1px dotted black; display: inline-block; min-width: 150px;"><?php echo $student['promoted_to'] ? $student['promoted_to'] : '..................'; ?></span></p>
                    <p>DATE OF ISSUE:- <span style="border-bottom: 1px dotted black; display: inline-block; min-width: 150px;"><?php echo $student['date_of_issue']; ?></span></p>
                </div>
            </div>
            <div class="signature-row" style="margin-top: 80px; display: flex; justify-content: space-between; font-weight: 900; font-size: 18px; padding: 0 10px;">
                <div style="border-top: 2px solid black; padding-top: 5px; min-width: 150px;">Sign Of Parent</div>
                <div style="border-top: 2px solid black; padding-top: 5px; min-width: 150px;">Sign Of Class Teacher</div>
                <div style="border-top: 2px solid black; padding-top: 5px; min-width: 150px;">Sign Of Principal</div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
function updateSingleScale(id, val) {
    const cards = document.querySelectorAll('.student-card-' + id);
    const wrapper = document.getElementById('wrapper-' + id);
    const valSpan = wrapper.querySelector('.indiv-val');
    valSpan.innerText = Math.round(val * 100) + '%';
    const negativeMargin = (1 - val) * 297;
    cards.forEach(card => { card.style.transform = `scale(${val})`; card.style.marginBottom = `-${negativeMargin}mm`; });
}
function updateAllScales(val) {
    document.getElementById('masterVal').innerText = Math.round(val * 100) + '%';
    const sliders = document.querySelectorAll('.indiv-zoom');
    sliders.forEach(slider => { slider.value = val; const id = slider.getAttribute('oninput').match(/'([^']+)'/)[1]; updateSingleScale(id, val); });
}
window.onload = () => updateAllScales(0.95);
</script>
</body>
</html>

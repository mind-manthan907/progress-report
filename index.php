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
            @page { size: A4; margin: 0; }
            .no-print-header, .indiv-control { display: none !important; }
            body { background: white; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .report-scroll-wrapper { margin: 0; padding: 0; }
            .report-wrapper { margin: 0 !important; padding: 0 !important; page-break-after: always !important; }
            .report-container { 
                margin: 0 auto !important; 
                box-shadow: none !important; 
                border: 1px solid red !important; 
                outline: none !important;
                box-shadow: 0 0 0 3px red, 0 0 0 5px red !important;
                padding: 25px !important;
                min-height: 297mm !important;
                width: 210mm !important;
                position: relative;
                box-sizing: border-box !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                display: flex;
                flex-direction: column;
            }
            .school-name { font-size: 90px !important; }
        }
        .btn-print { background: #ff9800; color: white; border: none; padding: 8px 15px; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .scale-control { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); padding: 5px 15px; border-radius: 20px; font-size: 14px; }
        
        .report-wrapper { position: relative; margin-bottom: 20px; text-align: center; }
        .indiv-control { 
            background: #f0f0f0; padding: 5px 10px; border-radius: 5px; 
            display: inline-flex; align-items: center; gap: 10px; margin-bottom: 5px;
            border: 1px solid #ccc; font-size: 12px; font-weight: bold;
        }
        .report-container { 
            transform-origin: top center; 
            transition: transform 0.2s; 
            margin: 0 auto !important; 
            display: flex; 
            flex-direction: column;
            background: white;
            box-sizing: border-box !important;
            min-height: 297mm;
        }
        .dotted-line { border-bottom: 2px dotted black; }
        .marksheet-page { position: relative; min-height: 297mm !important; }
        .marksheet-page { 
            padding: 14px 14px 10px !important; 
            border: none; 
            outline: none !important; 
            display: flex; 
            flex-direction: column; 
        }
        .marksheet-page .details-header { border: 2.5px solid #000; text-align: center; font-weight: 900; }
        .marksheet-page .details-title { border-bottom: 2.5px solid #000; font-size: 19px; padding: 5px 0; }
        .marksheet-page .details-class { font-size: 16px; padding: 4px 0; }
        .marksheet-page .student-details-table { margin-bottom: 6px !important; border-collapse: collapse; }
        .marksheet-page .student-details-table td { padding: 5px 8px !important; font-size: 13px !important; font-weight: 900; border: 2.5px solid #000; }
        .marksheet-page .marks-table th,
        .marksheet-page .marks-table td { padding: 3px 3px !important; font-size: 11.5px !important; font-weight: 900; border: 2.5px solid #000; }
        .marksheet-page .marks-table th { padding: 3px 2px !important; font-size: 10.5px !important; border: 2.5px solid #000; }
        .marksheet-page .grading-box { 
            border: 2.5px solid #000; 
            padding: 6px; 
            font-size: 11px; 
            font-weight: 900; 
            line-height: 1.25; 
            min-height: 92px;
        }
        .marksheet-page .summary-table td { border: 2.5px solid #000; padding: 6px; font-weight: 900; font-size: 12px; }
        .marksheet-page .co-head { border: 2.5px solid #000; text-align: center; font-weight: 900; font-size: 12px; padding: 5px; }
        .marksheet-page .co-table { border-collapse: collapse; width: 100%; min-height: 120px; }
        .marksheet-page .co-table tr { height: 26px; }
        .marksheet-page .co-table td { border: 2.5px solid #000; padding: 6px 10px; font-weight: 900; font-size: 12px; }
        .marksheet-page .co-table td:first-child { width: 50%; }
        .marksheet-page .co-table td:last-child { width: 50%; text-align: center; }
        .marksheet-page .remarks-box { border: 2.5px solid #000; border-radius: 14px; padding: 14px 12px 18px; font-size: 12px; font-weight: 900; margin-top: auto; }
        .marksheet-page .remarks-row { display: flex; gap: 8px; }
        .marksheet-page .remarks-col { flex: 1; }
        .marksheet-page .sign-row { display: flex; justify-content: space-between; margin-top: 18px; font-size: 12px; }
        .marksheet-page .sign { width: 180px; text-align: center; }
        .marksheet-page .page-fill { flex: 1; display: flex; flex-direction: column; }
        
        /* 4-7 template fill tuning */
        .marksheet-47 .marks-table { table-layout: fixed; }
        .marksheet-47 .marks-table tr { height: 32px; }
        .marksheet-47 .marks-table th { height: 34px; }
        .marksheet-47 .co-table tr { height: 38px; }
        .marksheet-47 .grading-box { min-height: 135px; }
        .marksheet-47 .summary-table td { height: 34px; }
        .marksheet-47 .remarks-box { min-height: 125px; }

        /* 1-3 template fill tuning */
        .marksheet-13 .marks-table { table-layout: fixed; }
        .marksheet-13 .marks-table tr { height: 32px; }
        .marksheet-13 .marks-table th { height: 34px; }
        .marksheet-13 .co-table tr { height: 38px; }
        .marksheet-13 .grading-box { min-height: 135px; }
        .marksheet-13 .summary-table td { height: 34px; }
        .marksheet-13 .remarks-box { min-height: 125px; }

        /* N-U template fill tuning */
        .marksheet-NU .marks-table { table-layout: fixed; }
        .marksheet-NU .marks-table tr { height: 34px; }
        .marksheet-NU .marks-table th { height: 36px; }
        .marksheet-NU .co-table tr { height: 40px; }
        .marksheet-NU .grading-box { min-height: 145px; }
        .marksheet-NU .summary-table td { height: 36px; }
        .marksheet-NU .remarks-box { min-height: 135px; }
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
    
    $calc_grand_max = 0;
    foreach ($subjects_list as $sub_name => $meta) {
        if (is_array($meta) && isset($meta['is_main'])) continue;
        $calc_grand_max += 200; 
    }
?>
    <div class="report-wrapper" id="wrapper-<?php echo $student['id']; ?>">
        <div class="indiv-control">
            <span>Size:</span>
            <input type="range" class="indiv-zoom" min="0.5" max="1.2" step="0.01" value="0.95" oninput="updateSingleScale('<?php echo $student['id']; ?>', this.value)">
            <span class="indiv-val">95%</span>
        </div>

            <!-- PAGE 1: COVER -->
            <div class="report-container page-break student-card-<?php echo $student['id']; ?>">
            <div style="text-align: center; flex: 1; display: flex; flex-direction: column; justify-content: space-between; padding: 10px 0;">
                <div>
                    <div style="position: relative; width: 180px; height: 180px; margin: 0 auto;">
                        <img src="logo.png" alt="J.P. Academy Logo" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <h1 class="school-name" style="margin: 0;">J.P. ACADEMY</h1>
                    <p class="school-address">SUARHA NEAR CHAKAILA KALWARI, DISTT- BASTI. (U.P.)</p>
                </div>
                
                <div class="report-title-box">PROGRESS REPORT<br>ACADEMIC YEAR- <?php echo $student['academic_year'] ?? $school_details['year']; ?></div>
                
                <div style="margin: 10px auto; width: 90%; border: 3px solid #1a237e; overflow: hidden; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.15); border-radius: 15px; flex-grow: 1; min-height: 350px;">
                    <img src="school.jpeg" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    <div style="position: absolute; bottom: 20px; left: 0; right: 0; display: flex; justify-content: center;">
                    </div>
                </div>

                <div class="footer-contact" style="display: flex; justify-content: center; align-items: center; gap: 6px; padding-top: 8px;">
                    <span style="font-size: 24px; color: #1a237e; font-weight: 900; line-height: 1; margin: 0;">For Inquiry:</span>
                    <div style="display: flex; align-items: center;"> 
                        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="30" style="display: block; flex-shrink: 0; margin-right: 4px;">
                        <span style="font-size: 24px; color: #333; font-weight: 900; letter-spacing: 0.3px; font-family: sans-serif; line-height: 1;"><?php echo implode(', ', $school_details['contacts']); ?></span>
                    </div>
                </div>

                <div style="font-size: 12px; color: #999; text-align: right; margin-top: 15px;">
                    Student: <?php echo strtoupper($student['name']); ?> | Roll: <?php echo $student['roll_no']; ?>
                </div>
            </div>
        </div>

        <!-- PAGE 2: MARKSHEET -->
        <div class="report-container marksheet-page marksheet-<?php echo str_replace('-', '', $class_type); ?> student-card-<?php echo $student['id']; ?>">
            <div class="page-fill">
            <div class="details-header">
                <div class="details-title">STUDENTS DETAILS</div>
                <div class="details-class">CLASS :- <?php echo $student['class_display']; ?></div>
            </div>
            
            <table class="student-details-table" style="margin-bottom: 8px;">
                <tr>
                    <td width="18%">ROLL NO</td>
                    <td width="32%"><?php echo $student['roll_no']; ?></td>
                    <td width="18%">SECTION</td>
                    <td width="32%"><?php echo $student['section']; ?></td>
                </tr>
                <tr><td>STUDENT'S NAME</td><td colspan="3"><?php echo strtoupper($student['name']); ?></td></tr>
                <tr><td>FATHER'S NAME</td><td colspan="3"><?php echo strtoupper($student['father_name']); ?></td></tr>
                <tr><td>ADDRESS</td><td colspan="3"><?php echo strtoupper($student['address']); ?></td></tr>
            </table>

            <div style="text-align: center; font-size: 12px; font-weight: 900; border: 2.5px solid black; padding: 3px; margin: 3px 0; background: #fff;">ACHOVENMENT RECORD OF ANNUAL-EXAMINATION- (<?php echo $student['academic_year'] ?? $school_details['year']; ?>)</div>
            
            <table class="marks-table">
                <thead>
                    <tr>
                        <th rowspan="2" width="22%" style="padding: 4px; font-size: 14px;">SUBJECT</th>
                        <th colspan="3" style="padding: 4px; font-size: 14px;">TERM-I (100 MARKS)</th>
                        <th rowspan="2" width="10%" style="padding: 4px; font-size: 13px;">UT-III<br>(20)</th>
                        <th colspan="2" style="padding: 4px; font-size: 14px;">TERM-II (100 MARKS)</th>
                        <th rowspan="2" width="10%" style="padding: 4px; font-size: 13px;">GRAND<br>TOTAL<br>(<?php echo $calc_grand_max; ?>)</th>
                        <th rowspan="2" width="8%" style="padding: 4px; font-size: 14px;">GRADE</th>
                    </tr>
                    <tr>
                        <th width="10%" style="padding: 4px; font-size: 13px;">UNIT<br>TEST-I<br>(20)</th>
                        <th width="10%" style="padding: 4px; font-size: 13px;">UNIT<br>TEST-II<br>(20)</th>
                        <th width="10%" style="padding: 4px; font-size: 13px;">HALF<br>YEARLY<br>(60)</th>
                        <th width="10%" style="padding: 4px; font-size: 13px;">UNIT<br>TEST-IV<br>(20)</th>
                        <th width="10%" style="padding: 4px; font-size: 13px;">ANNUAL<br>EXAM<br>(60)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total_obt_all = 0; $grand_total_max_all = 0; $has_data = false;
                    foreach ($subjects_list as $sub_name => $meta): 
                        $actual_name = is_array($meta) ? $sub_name : $meta;
                        $m = $student['marks'][$actual_name] ?? [];
                        if (is_array($meta) && isset($meta['is_main'])) { echo "<tr><td colspan='9' style='text-align: left; padding: 4px 15px; font-weight: 900; background: #eee; font-size: 15px;'>$actual_name</td></tr>"; continue; }
                        
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
                        $is_sub = (is_array($meta) && isset($meta['parent']));
                        $grade = (!$is_empty_subject && $sub_max > 0) ? calculateGrade($sub_obt, $sub_max) : '';
                    ?>
                    <tr>
                        <td style="text-align: left; padding: 4px; padding-left: <?php echo $is_sub ? '25px' : '10px'; ?>; font-size: 15px; <?php echo $is_sub ? 'font-style: italic;' : ''; ?>"><?php echo $actual_name; ?></td>
                        <td style="padding: 4px; font-size: 15px;"><?php echo (isset($m['ut1_obt']) && $m['ut1_obt'] !== '') ? $m['ut1_obt'] : ''; ?></td>
                        <td style="padding: 4px; font-size: 15px;"><?php echo (isset($m['ut2_obt']) && $m['ut2_obt'] !== '') ? $m['ut2_obt'] : ''; ?></td>
                        <td style="padding: 4px; font-size: 15px;"><?php echo (isset($m['hy_obt']) && $m['hy_obt'] !== '') ? $m['hy_obt'] : ''; ?></td>
                        <td style="padding: 4px; font-size: 15px;"><?php echo (isset($m['ut3_obt']) && $m['ut3_obt'] !== '') ? $m['ut3_obt'] : ''; ?></td>
                        <td style="padding: 4px; font-size: 15px;"><?php echo (isset($m['ut4_obt']) && $m['ut4_obt'] !== '') ? $m['ut4_obt'] : ''; ?></td>
                        <td style="padding: 4px; font-size: 15px;"><?php echo (isset($m['annual_obt']) && $m['annual_obt'] !== '') ? $m['annual_obt'] : ''; ?></td>
                        <td style="padding: 4px; font-size: 15px;"><?php echo (!$is_empty_subject) ? $sub_obt : ''; ?></td>
                        <td style="padding: 4px; font-size: 15px;"><?php echo $grade; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display: flex; gap: 6px; margin-top: 4px; align-items: stretch;">
                <div style="flex: 1.2;">
                    <div class="grading-box">GRADING SCALE : A1=91-100, A2=81-90, B1=71-80, B2=61-70, C1=51-60, C2=41-50, D=33-40, E=32 AND Below (Need Improvement)</div>
                </div>
                <div style="flex: 0.8;">
                    <table class="summary-table" style="width: 100%; border-collapse: collapse;">
                        <tr><td width="60%">OBTAINED MARKS</td><td style="text-align: center;"><?php echo $has_data ? $grand_total_obt_all : ''; ?></td></tr>
                        <tr><td>TOTAL MARKS</td><td style="text-align: center;"><?php echo $has_data ? $grand_total_max_all : ''; ?></td></tr>
                        <tr><td>PERCENTAGE</td><td style="text-align: center;"><?php echo ($has_data && $grand_total_max_all > 0) ? number_format(($grand_total_obt_all / $grand_total_max_all) * 100, 2) . '%' : ''; ?></td></tr>
                        <tr><td>RANK</td><td style="text-align: center;"><?php 
                            if (isset($student['manual_rank']) && $student['manual_rank'] !== '') echo $student['manual_rank'];
                            else echo $has_data ? calculateRank($student['id'], $students_all, $student['class_type']) : '';
                        ?></td></tr>
                    </table>
                </div>
            </div>

            <div class="co-head">CO-SCHOLASTIC AREAS<br>ON A3-POINTS (A, B, C) GRADING SCALE</div>
            <table class="co-table" style="width: 100%; border-collapse: collapse;">
                <?php foreach ($CO_SCHOLASTIC_AREAS as $area): $grade = $student['co_scholastic'][$area] ?? ''; ?>
                <tr>
                    <td><?php echo $area; ?></td>
                    <td style="width: 60px; text-align: center;"><?php echo $grade; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <div class="remarks-box" style="text-align: left;">
                <div>CLASS TEACHER REMARKS :- <?php echo htmlspecialchars($student['remarks'] ?? ''); ?></div>
                <div>PROMOTED TO CLASS:- <?php echo htmlspecialchars($student['promoted_to'] ?? ''); ?></div>
                <div>DATE OF ISSUE:- <?php echo htmlspecialchars($student['date_of_issue'] ?? ''); ?></div>
                <div class="sign-row">
                    <div class="sign">Sign Of Class Teacher</div>
                    <div class="sign">Sign of Principal</div>
                </div>
            </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
function updateSingleScale(id, val) {
    const wrapper = document.getElementById('wrapper-' + id);
    const valSpan = wrapper.querySelector('.indiv-val');
    valSpan.innerText = Math.round(val * 100) + '%';
    
    const cards = document.querySelectorAll('.student-card-' + id);
    const negativeMargin = (1 - val) * 297;
    cards.forEach(card => { 
        card.style.transform = `scale(${val})`; 
        card.style.marginBottom = `-${negativeMargin}mm`; 
    });
}
function updateAllScales(val) {
    document.getElementById('masterVal').innerText = Math.round(val * 100) + '%';
    const sliders = document.querySelectorAll('.indiv-zoom');
    sliders.forEach(slider => { 
        slider.value = val; 
        const match = slider.getAttribute('oninput').match(/'([^']+)'/);
        if (match) updateSingleScale(match[1], val); 
    });
}
window.onload = () => updateAllScales(0.95);
</script>
</body>
</html>

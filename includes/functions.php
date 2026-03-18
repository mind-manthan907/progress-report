<?php
function calculateGrade($marks, $total) {
    if ($total == 0) return 'E';
    $percentage = ($marks / $total) * 100;
    
    if ($percentage >= 91) return 'A1';
    if ($percentage >= 81) return 'A2';
    if ($percentage >= 71) return 'B1';
    if ($percentage >= 61) return 'B2';
    if ($percentage >= 51) return 'C1';
    if ($percentage >= 41) return 'C2';
    if ($percentage >= 33) return 'D';
    return 'E';
}

function getGradingScaleText() {
    return "GRADING SCALE : A1=91-100, A2=81-90, B1=71-80, B2=61-70, C1=51-60, C2=41-50, D=33-40, E=32 AND Below (Need Improvement)";
}

function calculateRank($student_id, $all_students, $class_type) {
    $rank_data = [];
    foreach ($all_students as $s) {
        if ($s['class_type'] == $class_type) {
            $total_obt = 0;
            foreach ($s['marks'] as $m) {
                if (!isset($m['is_main'])) {
                    $total_obt += ($m['ut1_obt'] ?? 0) + ($m['ut2_obt'] ?? 0) + ($m['hy_obt'] ?? 0) + 
                                 ($m['ut3_obt'] ?? 0) + ($m['ut4_obt'] ?? 0) + ($m['annual_obt'] ?? 0);
                }
            }
            $rank_data[$s['id']] = $total_obt;
        }
    }
    arsort($rank_data);
    $rank = 1;
    foreach ($rank_data as $id => $marks) {
        if ($id == $student_id) return $rank;
        $rank++;
    }
    return "-";
}
?>

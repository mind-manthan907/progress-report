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
    // Current student data
    $current_s = null;
    foreach($all_students as $st) if($st['id'] == $student_id) { $current_s = $st; break; }
    if (!$current_s) return "-";
    
    $target_year = $current_s['academic_year'] ?? '';
    $target_class = $current_s['class_display'] ?? '';

    $rank_data = [];
    foreach ($all_students as $s) {
        // Filter by same class, same year
        if (($s['class_display'] ?? '') == $target_class && ($s['academic_year'] ?? '') == $target_year) {
            $total_obt = 0;
            foreach ($s['marks'] as $m_name => $m_data) {
                // Skip if it's a main category (header) or has a parent (sub-subject in N-U)
                // In N-U class, we only count sub-subjects, NOT the parent headers.
                // In 4-7 class, we count all listed subjects.
                $total_obt += ($m_data['ut1_obt'] ?? 0) + ($m_data['ut2_obt'] ?? 0) + ($m_data['hy_obt'] ?? 0) + 
                             ($m_data['ut3_obt'] ?? 0) + ($m_data['ut4_obt'] ?? 0) + ($m_data['annual_obt'] ?? 0);
            }
            $rank_data[$s['id']] = $total_obt;
        }
    }
    arsort($rank_data);
    $rank = 1;
    $prev_marks = -1;
    $display_rank = 1;
    $count = 0;
    foreach ($rank_data as $id => $marks) {
        $count++;
        if ($marks < $prev_marks) {
            $display_rank = $count;
        }
        if ($id == $student_id) return $display_rank;
        $prev_marks = $marks;
    }
    return "-";
}
?>

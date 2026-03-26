<?php
$school_details = [
    'name' => 'J.P. ACADEMY',
    'address' => 'SUARHA NEAR CHAKAILA KALWARI, DISTT- BASTI. (U.P.)',
    'year' => '2024-2025',
    'contacts' => ['8738041627', '9305870675', '7839195611']
];

// GLOBAL MAX MARKS CONFIGURATION (Yahan ek baar badlo, har jagah badal jayega)
$EXAM_CONFIG = [
    'ut1' => ['label' => 'UNIT TEST-I', 'max' => 20],
    'ut2' => ['label' => 'UNIT TEST-II', 'max' => 20],
    'hy'  => ['label' => 'HALF YEARLY', 'max' => 60],
    'ut3' => ['label' => 'UNIT TEST-III', 'max' => 20],
    'ut4' => ['label' => 'UNIT TEST-IV', 'max' => 20],
    'annual' => ['label' => 'ANNUAL EXAM', 'max' => 60],
];

$MASTER_SUBJECTS = [
    '4-7' => ['ENGLISH', 'MATHS', 'SCIENCE', 'S.ST.', 'HINDI', 'COMPUTER', 'G.K.', 'DRAWING'],
    '1-3' => ['ENGLISH', 'MATHS', 'E.V.S', 'CONVERSATION', 'HINDI', 'COMPUTER', 'G.K.', 'DRAWING'],
    'N-U' => [
        '1- ENGLISH' => ['is_main' => true], 
        'A- READING' => ['parent' => '1- ENGLISH'], 
        'B- TRANSCRIPTION' => ['parent' => '1- ENGLISH'], 
        'C- DICTATION' => ['parent' => '1- ENGLISH'],
        '2- HINDI' => ['is_main' => true], 
        'A- READING ' => ['parent' => '2- HINDI'], 
        'B- TRANSCRIPTION' => ['parent' => '2- HINDI'], 
        'C- DICTATION ' => ['parent' => '2- HINDI'],
        '3- MATHS' => ['is_main' => true], 
        'A- WRITTEN' => ['parent' => '3- MATHS'], 
        'B- ORAL' => ['parent' => '3- MATHS'],
        '4- ART' => [], 
        '5- GENERAL KNOWLEDGE' => [], 
        '6- HINDI RHYMES' => [], 
        '7- ENGLISH RHYMES' => []
    ]
];

function getStudents() {
    $path = __DIR__ . '/../students.json';
    if (!file_exists($path)) {
        file_put_contents($path, json_encode([]));
    }
    $json = file_get_contents($path);
    return json_decode($json, true) ?: [];
}

function saveStudents($students) {
    $path = __DIR__ . '/../students.json';
    file_put_contents($path, json_encode(array_values($students), JSON_PRETTY_PRINT));
}

function generateStudentId($students) {
    $id = (int) floor(microtime(true) * 1000);
    $existing = array_map(function($s) { return (string)($s['id'] ?? ''); }, $students);
    while (in_array((string)$id, $existing, true)) { $id++; }
    return $id;
}

function findStudentById($students, $id) {
    foreach ($students as $s) { if ((string)($s['id'] ?? '') === (string)$id) { return $s; } }
    return null;
}

function upsertStudent(&$students, $student) {
    $found = false;
    foreach ($students as $i => $s) {
        if ((string)($s['id'] ?? '') === (string)($student['id'] ?? '')) {
            $students[$i] = $student;
            $found = true;
            break;
        }
    }
    if (!$found) { $students[] = $student; }
}

function deleteStudentById($students, $id) {
    return array_values(array_filter($students, function($s) use ($id) {
        return (string)($s['id'] ?? '') !== (string)$id;
    }));
}

$students = getStudents();
?>

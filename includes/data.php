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
        '1-ENGLISH' => ['is_main' => true], 
        'A- READING' => ['parent' => '1-ENGLISH'], 
        'B- TRANS CRIPTION' => ['parent' => '1-ENGLISH'], 
        'C-DICTATION' => ['parent' => '1-ENGLISH'],
        '2- HINDI' => ['is_main' => true], 
        'A- READING ' => ['parent' => '2- HINDI'], 
        'B- Trans Cription' => ['parent' => '2- HINDI'], 
        'C-DICTATION ' => ['parent' => '2- HINDI'],
        '3-Maths' => ['is_main' => true], 
        'A-WRITTEN' => ['parent' => '3-Maths'], 
        'B-ORAL' => ['parent' => '3-Maths'],
        '4- Art' => [], 
        '5- General Knowledge' => [], 
        '6- Hindi Rhymes' => [], 
        '7- English Rhymes' => []
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

$students = getStudents();
?>

<?php
// 1. Create an indexed array of student marks
$marks = [75, 42, 88, 59, 91];

// 2. Display all marks using a foreach loop
echo "<h3>Student Marks:</h3>";
foreach ($marks as $index => $mark) {
    echo "Student " . ($index + 1) . ": $mark <br>";
}

// 3. Calculate total, average, max, min
$total = 0;
$max = $marks[0];
$min = $marks[0];

foreach ($marks as $mark) {
    $total += $mark;
    if ($mark > $max) $max = $mark;
    if ($mark < $min) $min = $mark;
}

$average = $total / count($marks);

echo "<br><strong>Total Marks:</strong> $total <br>";
echo "<strong>Average Marks:</strong> $average <br>";
echo "<strong>Maximum Marks:</strong> $max <br>";
echo "<strong>Minimum Marks:</strong> $min <br>";

// 4. Count passed and failed students (pass mark ≥ 50)
$passCount = 0;
$failCount = 0;

foreach ($marks as $mark) {
    if ($mark >= 50) {
        $passCount++;
    } else {
        $failCount++;
    }
}

echo "<br><strong>Number of Students Passed:</strong> $passCount <br>";
echo "<strong>Number of Students Failed:</strong> $failCount <br>";

// 5. Create an associative array for a student's details
$student = [
    "name" => "Jim Shahadat",
    "id" => "23-51014-1",
    "cgpa" => 3.75
];

// 6. Display key-value pairs using a loop
echo "<h3>Student Details:</h3>";
foreach ($student as $key => $value) {
    echo ucfirst($key) . ": $value <br>";
}

// 7. User-defined function to calculate average marks
function calculateAverage($array) {
    $sum = 0;
    foreach ($array as $val) {
        $sum += $val;
    }
    return $sum / count($array);
}

// Call the function
$avgFromFunction = calculateAverage($marks);
echo "<br><strong>Average from Function:</strong> $avgFromFunction <br>";

// 8. String operations
$upperName = strtoupper($student['name']); // Convert name to uppercase
$nameLength = strlen($student['name']);    // Find string length
echo "<br><strong>Name in Uppercase:</strong> $upperName <br>";
echo "<strong>Name Length:</strong> $nameLength <br>";

// 9. Use a built-in array function (sort)
sort($marks);
echo "<br><strong>Sorted Marks:</strong> ";
foreach ($marks as $mark) {
    echo $mark . " ";
}

// 10. Type casting example (casting average to integer)
$averageInt = (int)$average;
echo "<br><br><strong>Average (as integer):</strong> $averageInt <br>";

// 11. Use a superglobal variable ($_GET) to take input
// Example URL: script.php?student_name=Bob
$inputName = isset($_GET['student_name']) ? $_GET['student_name'] : "No Name Provided";
echo "<br><strong>Input Student Name (from GET):</strong> $inputName <br>";
?>
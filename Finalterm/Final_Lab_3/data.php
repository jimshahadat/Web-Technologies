<?php
header("Content-Type: application/json");

$data = [
    "name" => "Jim",
    "age" => 24,
    "city" => "Dhaka"
];

echo json_encode($data);
?>
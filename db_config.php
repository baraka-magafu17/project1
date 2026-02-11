<?php
// db_config.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // kama huna password kwenye MySQL, acha hivi
$DB_NAME = 'mycycle'; // hakikisha jina hili ndilo database yako

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");
?>

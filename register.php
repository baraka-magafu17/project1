<?php
include 'db_config.php';
session_start();

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

$exists = $conn->query("SELECT * FROM users WHERE email='$email'");
if($exists->num_rows > 0){
  echo json_encode(["success"=>false,"error"=>"Email already registered"]);
  exit;
}

$sql = "INSERT INTO users (name,email,password) VALUES ('$name','$email','$password')";
if($conn->query($sql)){
  echo json_encode(["success"=>true]);
}else{
  echo json_encode(["success"=>false,"error"=>$conn->error]);
}
?>

<?php
$servername = "localhost";
$dBUsername = "usuario";
$dBPassword = "clave";
$dBName = "marisol";
$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);
if (!$conn) {
	die("Connection failed: ".mysqli_connect_error());
}

if (!$conn) {
	die("Connection failed: ".mysqli_connect_error());
}

// Fecha y hora actuales
$date= date("Y-m-d H:i:s");
// Leemos última actualización de datos
$sql = "SELECT * FROM LED_status;";
$result   = mysqli_query($conn, $sql);
$row  = mysqli_fetch_assoc($result);
// almacenamos ultima actualización de datos	
$last = $row['last'];
// Diferencia
$dateDif = abs(strtotime($date) - strtotime($last));

// mostramos datos fechas
echo $date . "<br>";
echo $last . "<br>";
echo $dateDif . "<br>";

// toggle LED status
if (isset($_POST['toggle_LED'])) {
	$sql = "SELECT * FROM LED_status;";
	$result   = mysqli_query($conn, $sql);
	$row  = mysqli_fetch_assoc($result);
	
	if($row['status'] == 0){
		$update = mysqli_query($conn, "UPDATE LED_status SET status = 1 WHERE id = 1;");		
	}		
	else{
		$update = mysqli_query($conn, "UPDATE LED_status SET status = 0 WHERE id = 1;");		
	}
	$update = mysqli_query($conn, "UPDATE LED_status SET last = '$date';");	
}


//Read the database
if (isset($_POST['check_LED_status'])) {
	//$led_id = $_POST['check_LED_status'];	
	$sql = "SELECT * FROM LED_status;";
	$result   = mysqli_query($conn, $sql);
	$row  = mysqli_fetch_assoc($result);
	if($row['status'] == 0){
		echo "LED_is_off";
	}
	else{
		echo "LED_is_on";
	}	
	$update = mysqli_query($conn, "UPDATE LED_status SET last = '$date';");		
}	

//Update temperature
if (isset($_POST['temp'])) {
	$update = mysqli_query($conn, "UPDATE LED_status SET temp =" . $_POST['temp'] . ";");
	$update = mysqli_query($conn, "UPDATE LED_status SET last = '$date';");	
}

//Update humidity
if (isset($_POST['hum'])) {
	$update = mysqli_query($conn, "UPDATE LED_status SET hum =" . $_POST['hum'] . ";");
	$update = mysqli_query($conn, "UPDATE LED_status SET last = '$date';");		
}

//Update temperatureb
if (isset($_POST['tempb'])) {
	$update = mysqli_query($conn, "UPDATE LED_status SET tempb =" . $_POST['tempb'] . ";");
	$update = mysqli_query($conn, "UPDATE LED_status SET last = '$date';");	
}

//Update humidityb
if (isset($_POST['humb'])) {
	$update = mysqli_query($conn, "UPDATE LED_status SET humb =" . $_POST['humb'] . ";");
	$update = mysqli_query($conn, "UPDATE LED_status SET last = '$date';");	
}

?>

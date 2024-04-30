<?php
$servername = "localhost";
$dBUsername = "usuario";
$dBPassword = "clave";
$dBName = "marisol";

$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);

if (!$conn) {
	die("Connection failed: ".mysqli_connect_error());
}


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
}

$sql = "SELECT * FROM LED_status;";
$result   = mysqli_query($conn, $sql);
$row  = mysqli_fetch_assoc($result);	
?>
<!DOCTYPE html>
<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js" type="text/javascript"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">
	<style>
		.wrapper{
			width: 100%;
			padding-top: 50px;
		}
		.col_m{
			width: 50%;
			float: left;
			min-height: 1px;
		}
		.col_l{
			width: 25%;
			float: left;
			min-height: 1px;
		}	
		#submit_button{
			background-color: #2bbaff; 
			color: #FFF; 
			font-weight: bold; 
			font-size: 40; 
			border-radius: 15px;
			text-align: center;
		}
		.led_img{
			width: 80%;
			margin: auto;
			object-fit: cover;
			object-position: center;  
		}
		
		@media only screen and (max-width: 600px) {
			.col_m {
				width: 100%;
			}
			.col_l {
				width: 0%;
			}		
			.wrapper{
				width: 100%;
				padding-top: 5px;
			}
			.led_img{
				width: 80%;
				margin: auto;
				object-fit: cover;
				object-position: center;  
			}
		}
	
	</style>	
</head>
<body>
	<div class="wrapper" id="refresh">
		<div class="col_l">
		</div>

		<div class="col_m" >
			<div class="led_img">
				<img src="logo_marisma.png" width="100%" height="100%">
			</div>
			<?php if($row['status']==0) {?>
				<h1 style="text-align: center;">La bici está bloqueada &#x1f512;</h1>
			<?php } else { ?>
				<h1 style="text-align: center;">La bici está desbloqueada &#x1f513;</h1>
			<?php } ?>

			<h1 style="text-align: center;">Temperatura ambiente (ºC) : <?php echo $row['temp'];?></h1>
			<h1 style="text-align: center;">Temperatura batería (ºC) : <?php echo $row['tempb'];?></h1>

			<?php if($row['tempb'] > $row['temp'] + 20) {?>
				<h1 style="text-align: center;">Temperatura batería muy alta &#128293;</h1>
				<audio autoplay>
  					<source src="alarma-muy-alta.mp3" type="audio/mpeg">
				</audio>				
			<?php } elseif($row['tempb'] > $row['temp'] + 10) { ?>
				<h1 style="text-align: center;">Temperatura batería alta &#127777;</h1>
				<audio autoplay>
  					<source src="alarma-alta.mp3" type="audio/mpeg">
				</audio>
			<?php } ?>

			<div style="text-align: center;">			
			<script type="text/javascript">
			$(document).ready (function () {
				var updater = setTimeout (function () {
					$('div#refresh').load ('index.php', 'update=true');
				}, 1000);
			});
			</script>
			<br>
			<br>
			<form action="index.php" method="post" id="LED" enctype="multipart/form-data">			
				<input id="submit_button" type="submit" name="toggle_LED" value="Bloquear/Desbloquear" />
			</form>
			</div>
		</div>

		<div class="col_l">
		</div>
	</div>
</body>
</html>

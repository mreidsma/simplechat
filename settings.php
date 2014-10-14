<?php  
session_start();
error_reporting(0);

$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

	$_SESSION['location'] = $actual_url;   
   	
	date_default_timezone_set('America/Detroit');
	$m = NULL; // By default, there are no messages

	// Are you logged in?
    
	// Debug the user login by a force login
	//$_SESSION['username'] = 'reidsmam'; $logged_in = 1; $user_id = 1; // By default, user is logged out

	if(!(isset($_SESSION['username']))) { // No $_SESSION['username'] variable, send to login script
        $logged_in = 0; // By default, user is logged out
		
		// User has not logged in
		header('Location: http://labs.library.gvsu.edu/login');

	}    
	

// Include additional libraries that make this work
require 'inc/config.php';
require 'inc/functions.php';
require 'inc/markdown.php';

// Create database connection
$db = new mysqli($db_host, $db_user, $db_pass, $db_database);
	if ($db->connect_errno) {
   	printf("Connect failed: %s\n", $db->connect_error);
   	exit();
	}

	if(isset($_SESSION['username'])) { // User has logged in 
		
		 if(isset($_POST['submit'])) { // Settings have been updated

		       $notifications = $_POST['notifications'];
			   $theme = $_POST['theme'];

			   // Update account
			   $update = $db->query("UPDATE users SET theme='$theme', notification='$notifications' WHERE user_id='$user_id'");

			   if($update) {
				$m = '<div class="success">Your settings were updated.</div>';
			   } else {
				$m = '<div class="error">Whoops, there was a problem.</div>';
			  }

		  }

		if (isset($_REQUEST['logout'])) {
			$_SESSION = array();
			session_destroy();
			header('Location: index.php');
		}
        
		// Get user data
			$username = $_SESSION['username'];
			// User names are unique, so only need a single row
			// Get all the bits from the user name so you don't have to ask again
			$user_result=$db->query("SELECT * FROM users WHERE username = '$username' LIMIT 1");

			if(($user_result) && ($user_result->num_rows > 0)) { // Query was successful, a user was found 
                 while($row = $user_result->fetch_assoc()) {
					$user_id = $row["user_id"];
					$user_fname = $row["fname"];
					$user_email = $row["email"];
					$user_notifications = $row["notification"]; 
					$user_theme = $row["theme"];
				}     
			
	   	 		// Update the last time folks logged in
				$now = time();
				$db->query("UPDATE users SET last_logged_in = '$now' WHERE user_id = '$user_id'");
				$logged_in = 1;  
	  	 
		  } else {

			// No user found

			echo '<h1>Sorry, you don&#8217;t have access to this system.</h1>';
			die;
         } 

	  }

     

?>

<!DOCTYPE html>
<html lang="en">

	<head>
			<title><?php echo $org_name; ?> Chat Room</title>
			
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			
			<link rel="stylesheet" type="text/css" href="css/styles.css" />
<?php
     	if($user_theme > 0) {
            echo '<link rel="stylesheet" type="text/css" href="css/theme' . $user_theme . '.css" />';
		}
?>
	</head>
	
	<body> 
	  <div id="header">  
		<h1><?php echo $org_name; ?> Chat Room</h1>
		
		<div id="account">
		     <ul class="horizontal-list"> 
			    <li>Hello, <?php echo $user_fname; ?></li>
				<li><a href="settings.php" class="active">Settings</a></li>
				<li><a href="?logout">Log out</a></li>
				
			 </ul>
		</div>
	  </div>
		
		<div id="who">
		 	&nbsp;  
		</div>  
		
		<!-- Let's see some history WOOT WOOT -->
		<div id="settings-screen">
<?php			
			if(isset($m)) {
				echo $m;
			}
?>   		
			<form name="settings" action="" method="post" id="settings-form">
				 <fieldset>
					<legend>User Settings</legend>
					
					<p><input type="checkbox" value="1" name="notifications" id="notifications" <?php if ($user_notifications == 1) { echo 'checked="checked"'; } ?> />&nbsp;<label for="notifications">Email me when someone directs a message at me</label></p>
					
				    <p>
					<label for="theme">Change Your Theme</label>
					

				   <select name="theme" id="theme"> 
					
<?php
               // Theme details are stored in a JSON file in the inc directory. CSS files for themes are in the CSS directory,
			   // named themeX.css, where X is the numerical value of the theme. JS files are stored in the JS directory, named
			   // themeX.js. Both are required for a theme to work. 
			    
			if($user_theme == 0) {
				echo '<option value="0" selected="selected">--- Select One ---</option>';
				
			} else {
				echo '<option value="0">--- Select One ---</option>';
				
			}

				// Get the JSON file and parse it
				$themes = json_decode(file_get_contents('inc/themes.json'));

				foreach($themes as $key => $value) { 
					
					if($value == $user_theme) {
						echo '<option value="' . $value . '" selected="selected">' . $key . '</option>'; 
					} else {
					    echo '<option value="' . $value . '">' . $key . '</option>';  
					}
                 }


?> 			                   
					</select>
					
					<p><input type="submit" value="Update Settings" name="submit" /></p>
				
			</form>
			
			<p><a href="index.php" class="button">Back to Chat</a></p>
	    </div>  
	
	
	</body>
</html>
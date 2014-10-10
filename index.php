<?php 
session_start();
error_reporting(0);

$actual_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

	$_SESSION['location'] = $actual_url;   
   	
	date_default_timezone_set('America/Detroit');
	$m = NULL; // By default, there are no messages

	// Are you logged in?
    
	// Debug the user login by a force login
	//$_SESSION['username'] = 'reidsmam'; $logged_in = 1; // By default, user is logged out

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

		if (isset($_REQUEST['logout'])) {
			$_SESSION = array();
			session_destroy();
			header('Location: index.php');
		}
        
		// Get user data when first logging in
		if(($logged_in != 1) || ($username == NULL)) {  
			$username = $_SESSION['username'];
			// User names are unique, so only need a single row
			// Get all the bits from the user name so you don't have to ask again
			$user_result=$db->query("SELECT * FROM users WHERE username = '$username' LIMIT 1");

			if(($user_result) && ($user_result->num_rows > 0)) { // Query was successful, a user was found 


				while($row = $user_result->fetch_assoc()) {
					$user_id = $row["user_id"];
					$user_fname = $row["fname"];
					$user_email = $row["email"];
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
	
	   // Add a new message
	   if(isset($_POST['message'])) {
		  
			// Watch out for naughty bits
			$message = $db->real_escape_string($_POST['message']);
						
		   	addMessage($user_id, $message);
			
		   // Check to see if it was a message meant for Bruce, and do the Bruce things.
			
			messageTo($message);
		
	   }
			
	  

  }
?>

<!DOCTYPE html>
<html lang="en">

	<head>
			<title><?php echo $org_name; ?> Chat Room</title>
			
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			
			<link rel="stylesheet" type="text/css" href="css/styles.css" />
	</head>
	
	<body> 
	  <div id="header">  
		<h1><?php echo $org_name; ?> Chat Room</h1>
		
		<div id="account">
		     <ul class="horizontal-list"> 
			    <li>Hello, <?php echo $user_fname; ?></li>
				<li><a href="#">Settings</a></li>
				<li><a href="?logout">Log out</a></li>
				
			 </ul>
		</div>
	  </div>
		
		<div id="who">
			<h2>Who Uses This?</h2>  
			
			<p>This is an asynchronous chat room. Here are all the registered users, with the last time they were active.</p>
			
			<ul>
				<li>Bruce: <span class="when">Always</span></li>
<?php
     			   // Ok, list the folks who have logged in recently
                   $folks = $db->query("SELECT fname, last_logged_in FROM users WHERE user_id != '9' ORDER BY last_logged_in DESC");

					if($folks) {  
						
						while($row = $folks->fetch_assoc()) {  
							echo '<li>' . $row['fname'] . ': <span class="when">' . relative_time($row['last_logged_in']) . '</span></li>';
						}
					}

?>   
			 </ul> 
			
			<p><a href="#">Get Help Now</a></p>
		</div>  
		
		<!-- Let's see some history WOOT WOOT -->
		<div id="input-screen">
			<?php echo $m; ?>
	   	 	<ol id="transcript">
<?php
   			// Let's show the messages
			$chitchat = $db->query("(SELECT transcript.message_timestamp, transcript.message_id, transcript.message_text, transcript.message_user, users.fname FROM transcript, users WHERE transcript.message_user = users.user_id ORDER BY message_timestamp DESC) ORDER BY message_id ASC");
			
			//echo $chitchat->numrows();

			if($chitchat) {
				 while($row = $chitchat->fetch_assoc()) { 
				   					 
						echo '<li id="' . $row['message_id'] . '"><div class="speaker">' . $row['fname'] . ':</div> <div class="message">' . Markdown($row['message_text']) . '</div> <div class="when">' . relative_time($row['message_timestamp']) . '</li>';
					}
			}
           


?>
			</ol>
		
			<form name="chitchat" method="post" action="">
				 <fieldset>
					  <legend>Join the Conversation</legend>
					  	<label for="message">Type Your Message:</label><br />
						<textarea name="message" id="message"></textarea><br />
						<input type="submit" name="send message" value="Send Message" />
				 </fieldset>
			</form> 
	    </div>  
	
	
	<script src="js/scripts.js"></script>
	</body>
</html>
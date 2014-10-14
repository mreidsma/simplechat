<?php  

/*
     Function to make times relative, written by Gilbert Pellegrom
	 http://gilbert.pellegrom.me/php-relative-time-function/
*/

function relative_time($date) 
{    
	if($date == 0) {
		return 'Never';
	}
	
	$postfix = ' ago';
	$fallback = 'F Y';
    $diff = time() - $date; 
    if($diff < 60) 
        return 'Just now';
    $diff = round($diff/60);
    if($diff < 60) 
        return $diff . ' minute'. ($diff != 1 ? 's' : '') . $postfix;
    $diff = round($diff/60);
    if($diff < 24) 
        return $diff . ' hour'. ($diff != 1 ? 's' : '') . $postfix;
    $diff = round($diff/24);
    if($diff < 7) 
        return $diff . ' day'. ($diff != 1 ? 's' : '') . $postfix;
    $diff = round($diff/7);
    if($diff < 4) 
        return $diff . ' week'. ($diff != 1 ? 's' : '') . $postfix;
    $diff = round($diff/4);
    if($diff < 12) 
        return $diff . ' month'. ($diff != 1 ? 's' : '') . $postfix;

    return date($fallback, strtotime($date));
}  
 
/*
Function to add a message to the database
*/

function addMessage($user_id, $message) {
	global $db;
	
	$now = time();
	
	$add_message = $db->query("INSERT INTO transcript VALUES ('','$now','$user_id','$message')"); 
	
	if(!$add_message) {
		$m = '<div class="error">There was an error posting your message. It&#8217;s probably Matt&#8217;s fault.</div>';
	}
}

/*
Function to determine whether the message was sent to a particular person
*/

function messageTo($message) {
	
	global $db, $org_name, $user_fname, $user_email, $user_id;
	
	$to = split(" ", $message);
	
	if(strtolower($to[0]) != 'bruce') {
	 
		// Get all first names
		$folks = $db->query("SELECT fname, notification, email FROM users");
	
		if($folks) {  
		
			while($row = $folks->fetch_assoc()) {  
				if($row['fname'] == $to[0]) {  // Message was directed at this person
				
					// Check notification preferences, and send an email if they have turned that function on.
					
					if($row['notification'] == 1) {  // User wants email notifications
						
						$subject = 'New Message in ' . $org_name . ' from ' . $user_fname;
						
						$headers = "From: " . strip_tags($user_email) . "\r\n";
						$headers .= "Reply-To: ". strip_tags($user_email) . "\r\n";
						$headers .= "MIME-Version: 1.0\r\n";
						$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
						
					    $message_body = '<html><body style="font-family: Helvetica, Arial, Verdana, sans-serif; font-size: 14px; color: #333; line-height: 1.5em;">';
						$message_body .= Markdown($message);
						$message_body .= '<p style="font-size:12px; color: #575757;">You have subscribed to email notifications from the ' . $org_name . ' Chat Room. You can turn these off by logging in and adjusting your settings.</p>';
						$message_body .= '</body></html>';
						
						mail($row['email'], $subject, $message_body, $headers);
						
					}
				}
			}
		}         
	} else {  // This was sent to Bruce. Parse for functions. 
		
	    if(strtolower($to[1]) == 'deploy') {  // Deploy code
			
		}     
		
		if((strtolower($to[1]) == 'i') && (strtolower($to[2]) == 'am') && (strtolower($to[3]) == 'working') && (strtolower($to[4]) == 'on')) { // Update the I am working on table
			
		  // First, get rid of the silly commands and just give the text of what Bruce needs
		
		  $split_text = $to[0] . ' ' . $to[1] . ' ' . $to[2] . ' ' . $to[3] . ' ' . $to[4];
		  
		  $goodies = split($split_text, $message);  
			
		 addMessage(9, addProject($user_id, $goodies[1]));  
			
		}       
		
		if((strtolower($to[1]) == 'what') && (strtolower($to[2]) == 'is') && (strtolower($to[3]) == 'everyone') || (strtolower($to[3]) == 'everybody') && (strtolower($to[4]) == 'working') && (strtolower($to[5]) == 'on') || (strtolower($to[5]) == 'on?')) { // Have Bruce tell us what everyone is working on 
			
			addMessage(9, whatYouDoing()); 
			
		}     
		
		if((strtolower($to[0]) == 'i') && (strtolower($to[2]) == 'am') && ((strtolower($to[3]) == 'at') || (strtolower($to[3]) == 'in') || (strtolower($to[3]) == 'downtown'))) { // Update the I am at/in table
			
		}  
		
		if((strtolower($to[1]) == 'where') && (strtolower($to[2]) == 'is') && ((strtolower($to[3]) == 'everyone') || (strtolower($to[3]) == 'everybody'))) { // Have Bruce tell us where everyone is
			
			addMessage(9, whereYouAt());
			
		}   
		
		if(strtolower($to[1]) == 'hello') {  // Bruce say Hello back 
			
			$reply = 'Well hello to you, too, ' . $user_fname;
			
			addMessage(9, $reply);
			
		}	
	} 
}


/*
Function to deploy code to the server from Github
*/

function deploy($server, $repo) { 
	
	// Choose a random number between 1 and 7
	$rand = rand(1,7);
	
	set_time_limit(0);
	ignore_user_abort(true);
	$deploy = shell_exec('cd ' . $server . '' . $repo . '; git pull origin master'); 
	
	if($deploy) { 
		$message = '<h2>Success!</h2>
					<p><img src="/img/success' . $rand . '.gif" alt="Success" /></p>
					<pre>' . trim($deploy) . '</pre>';
	} else {  
		$message = '<h2>Nope</h2>
					<p><img src="/img/error' . $rand . '.gif" alt="Error" /></p>
					<p>Whoops, there was a problem.</p>';
	}   
	
	echo $message;
	
} 

/*
Function to write to the Where Am I table
*/

function addLocation($user_id, $location) {
	
	global $db;
	
	$now = time();
	
}   

/*
Function to read from the Where Am I table
*/

function whereYouAt() {
	
	global $db;
	
	$now = time();
	
}

/*
Function to write to the What am I doing table
*/

function addProject($user_id, $project) {
	
	global $db;
	
	$now = time();
	
	$exists = $db->query("SELECT * FROM projects WHERE user_id = '$user_id'");
	
	if($exists->num_rows > 0) { // Update the old record
	   $add_project = $db->query("UPDATE projects SET projects='$project', timestamp='$now' WHERE user_id='$user_id'"); 
	} else { // Add it for the first time
	   $add_project = $db->query("INSERT INTO projects VALUES ('$user_id','$project','$now')");  
	} 
 
	if($add_project) {
	   $reply = 'Okay, ' . firstName($user_id) . ', you are ' . $project . '&#8212;Got it.'; 
	} else {
	   $reply = 'Whoops, there was a problem with that.'; 
	}
	
	 return $reply;

}  

/*
Function to read from the What are you working on table
*/

function whatYouDoing() {
	
	global $db;
	
	$now = time(); 
	
	$what = $db->query("SELECT projects.projects, projects.timestamp, users.fname 
						FROM projects, users 
						WHERE users.user_id = projects.user_id
						ORDER BY users.fname ASC");
						
	if($what) { 
		
		$reply = "Here is what everyone is up to:\n\n";
		
		 while($row = $what->fetch_assoc()) {  
				$reply .= '* **' . $row['fname'] . '** is ' . $row['projects'] . ' (' . relative_time($row['timestamp']) . ')' . "\n";
			}  
			
		  return $reply;
		
	}  else {
		
		  return 'Whoops, there was a problem with that.';
	}
	
} 

function firstName($user_id) {
	
	global $db;
	
	$name_query = $db->query("SELECT fname FROM users WHERE user_id='$user_id' LIMIT 1");
	          
	 while($row = $name_query->fetch_assoc()) { 
		print_r($row);
		 return $row['fname'];
	 }
}

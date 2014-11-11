<?php
session_start();
error_reporting(0);

function checkLogin(){
    return isset($_SESSION['username']);
}
require 'inc/config.php';
require 'inc/functions.php';
require 'inc/markdown.php';

if (isset($_GET['getMessages'])) {
    if (checkLogin()){
        $time = isset($_GET['since']) ? $_GET['since'] : '0';
        $messages = $db->query(
            "(SELECT transcript.message_timestamp, transcript.message_id, ".
                "transcript.message_text, transcript.message_user, users.fname ".
                "FROM transcript, users ".
                "WHERE transcript.message_user = users.user_id ".
                "AND transcript.message_timestamp > $time ".
                "ORDER BY message_timestamp DESC ".
                "LIMIT 50) ".
            "ORDER BY message_id ASC");

        $chat = [];

        if ($messages) {
            while ($row = $messages->fetch_assoc()) {
                $row['messageText'] = Markdown($row['message_text']);
                $row['when'] = relative_time($row['message_timestamp']);
                $row['fname'] = $row['fname'];
                $chat[] = $row;
            }
        }
        echo json_encode($chat);
    } else {
        notLoggedIn();
    }
}
if (isset($_GET['addMessage'])){
    if (checkLogin()){
        $user_id = $_SESSION['userid'];
        $message = $db->real_escape_string($_GET['message']);
        addMessage($user_id, $message);
        messageTo($message);
        $return['success'] = true;
        echo json_encode($return);
    } else {
        notLoggedIn();
    }
}
function notLoggedIn(){
    $error = <<<JSON
    {
    "messageText": "You are not logged in. Please click <a href='http://labs.library.gvsu.edu/login'>here</a> to log in.",
    "when": "Now",
    "fname": "<span style='color:red;'>Error</span>"
}
JSON;
    echo $error;

}
?>
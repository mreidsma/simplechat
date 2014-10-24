<?php
session_start();
error_reporting(E_ALL);
require 'inc/config.php';
require 'inc/functions.php';
require 'inc/markdown.php';
if (isset($_GET['getMessages'])) {
    $time = isset($_GET['since']) ? $_GET['since'] : '0';
    $messages = $db->query("(SELECT transcript.message_timestamp, transcript.message_id, transcript.message_text, transcript.message_user, users.fname FROM transcript, users WHERE transcript.message_user = users.user_id AND transcript.message_timestamp > $time ORDER BY message_timestamp DESC LIMIT 50) ORDER BY message_id ASC");
    $chat = [];

    if ($messages) {
        while ($row = $messages->fetch_assoc()) {
            $row['messageText'] = Markdown($row['message_text']);
            $row['when'] = relative_time($row['message_timestamp']);
            $chat[] = $row;
        }
    }
    echo json_encode($chat);
}
if (isset($_GET['addMessage'])){
    $user_id = $_SESSION['userid'];
    $message = $db->real_escape_string($_GET['message']);
    addMessage($user_id, $message);
    messageTo($message);
    $return = [];
    $return['success'] = true;
    echo json_encode($return);
}
?>
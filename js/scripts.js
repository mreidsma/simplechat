// Scroll the transcript to the bottom
var objDiv = document.getElementById("transcript");
objDiv.scrollTop = objDiv.scrollHeight;

function getMessages(){
    var time = jQuery("#transcript li").last().data('timestamp');
    time = time ? time : '0';
    console.log(time);
    jQuery.ajax('messages.php',{
        success: addMessageToTranscript,
        data: {since: time,
            getMessages: true}
    });
}
function addMessageToTranscript(messages){
    messages = JSON.parse(messages);
    if (messages == null) {return;}
    for (var i = 0; i < messages.length; i++){
        var message = messages[i];
        var html =
            "<li id='" + message.message_id + "' data-timestamp='" + message.message_timestamp + "'>" +
            "<div class='speaker'>" + message.fname + "</div>" +
            "<div class='message'>" + message.message_text + "</div>" +
            "<div class='when'>" + message.when + "</div>" +
            "</li>";

        jQuery("#transcript").append(html);
    }
}

function submitMessage(){
    var message = jQuery("#message").val();
    jQuery.ajax('messages.php', {
        data: {addMessage: true,
            message : message}
    });
    jQuery("#message").val('').focus();
    return false;
}
jQuery(document).ready(function(e){
    getMessages();
    setInterval(getMessages,2000);
    jQuery('form').submit(submitMessage);
});

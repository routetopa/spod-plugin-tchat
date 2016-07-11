$(".sentiment-button").live("click", function()
{
    var id = $(this).attr('id');
    switch($(this).attr('icon')){
        case "thumbs-up-down":
            $(this).attr('icon', 'thumb-up');
            $(this).attr('sentiment', '2');
            break;
        case "thumb-up":
            $(this).attr('icon', 'thumb-down');
            $(this).attr('sentiment', '3');
            break;
        case "thumb-down":
            $(this).attr('icon', 'thumbs-up-down');
            $(this).attr('sentiment', '1');
            break;
    }
});


$(document).ready(function(){

    var socket = io("http://" + window.location.hostname +":3000");
    socket.on('realtime_message', function(rawData) {
        switch(rawData.operation){
            case "commentAdded":
                var comment = JSON.parse(rawData.comment);
                if(comment.userId != TCHAT.currentUserId)
                {
                    $("#new_message_" + rawData.parent).css('color', '#000000');
                    $("#new_message_" + rawData.parent).addClass("newMessagesArrived");
                    $("#comment_container_" + rawData.parent).addClass("emphasizedComment");

                    var current_bundle = $("#numbers_of_new_messages_" + rawData.parent);
                    var num_of_messages = parseInt(current_bundle.html()) + 1;
                    current_bundle.html(num_of_messages);
                }
                break;
        }
    });

    /*$(".new_message_icon").click(function(){
        $("#new_message_" + $(this).attr('commentId')).css('color', 'transparent');
        $("#new_message_" + $(this).attr('commentId')).removeClass("newMessagesArrived");
        $("#comment_container_" + $(this).attr('commentId')).removeClass("emphasizedComment");
    });*/
});
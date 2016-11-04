window.tchatCommentCmps = {items : {}};
window.tchatCommentListCmps = {items : {}};
window.tchatAttachmentCmps = {};
window.tchatCommentsParams = {};
window.tchatCommentsListParams = {};
window.tchatAttachmentParams = {};

window.tchatCommentCmps.refreshCommentsBehavior = function(){
    setTimeout( function() {
        for(var k in window.tchatCommentsParams){
            window.tchatCommentCmps.items[k] = new OwComments(window.tchatCommentsParams[k]);
        }
        for(k in window.tchatCommentsListParams){
            window.tchatCommentListCmps.items[k] = new SpodtchatCommentsList(window.tchatCommentsListParams[k]);
            window.tchatCommentListCmps.items[k].init();
        }
        for(k in window.tchatAttachmentParams){
            window.tchatAttachmentCmps[k] = new SPODFileAttachment(window.tchatAttachmentParams[k]);
        }

    }, 1000);
};

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

    $(".sentiment-button").live("click", function()
    {
        var id = $(this).attr('id');
        switch($(this).attr('icon')){
            case "face":
                $(this).attr('icon', 'social:mood');
                $(this).attr('sentiment', '2');
                break;
            case "social:mood":
                $(this).attr('icon', 'social:mood-bad');
                $(this).attr('sentiment', '3');
                break;
            case "social:mood-bad":
                $(this).attr('icon', 'face');
                $(this).attr('sentiment', '1');
                break;
        }
    });

    $(document.body).on('click', '.ow_miniic_comment', function(e){
        $(e.target).parent().parent().next().toggle('fade', {direction: 'top'}, 500);
        $(e.target).parent().parent().next().css('display');
    });

    $(document.body).on('click', '.show_datalet', function(e){
        //var datalet_placeholder = $(e.currentTarget).parent().find(".datalet_placeholder");
        var datalet_placeholder = $(e.currentTarget).parent().parent().find('.datalet_placeholder');

        datalet_placeholder.toggle('fade',
            {direction: 'top'},
            function(){
                if(datalet_placeholder.css('display') == 'none')
                    $(e.currentTarget).css('background', '#2196F3');
                else
                    $(e.currentTarget).css('background', '#5B646A');

                //resize the datalet when is opened
                var datalet = $(datalet_placeholder.children()[1])[0];
                if(datalet.refresh != undefined)
                    datalet.refresh();
                else
                    datalet.behavior.presentData();
            },
            500);
    });

    /*$(".new_message_icon").click(function(){
        $("#new_message_" + $(this).attr('commentId')).css('color', 'transparent');
        $("#new_message_" + $(this).attr('commentId')).removeClass("newMessagesArrived");
        $("#comment_container_" + $(this).attr('commentId')).removeClass("emphasizedComment");
    });*/
    window.tchatCommentCmps.refreshCommentsBehavior();
});

ODE.addOdeOnComment = function()
{
    var ta = $('.ow_comments_input textarea');
    $.each(ta, function(idx, obj) {
        if ( $(obj).attr('data-preview-added') ) {
            return;
        } else {
            $(obj).attr('data-preview-added', true);
        }
        var id = obj.id;

        // Add ODE on Comment
        var odeElem = $(obj).parent().find('.ow_attachments').first().prepend($('<a title="'+ODE.internationalization["add_datalet_"+ODE.user_language]+'" href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'datalet_blue_rect.svg) no-repeat center;" data-id="' + id + '"></a>'));
        odeElem = odeElem.children().first();
        odeElem.click(function (e) {
            ODE.pluginPreview = 'tchat';
            ODE.commentTarget = e.target;
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {width:'90%', height:'90vh', iconClass:'ow_ic_lens', title:''});
        });

        // Add PRIVATE_ROOM on Comment
        if(ODE.is_private_room_active)
        {
            var prElem = $(obj).parent().find('.ow_attachments').first().prepend($('<a title="'+ODE.internationalization["open_my_space_"+ODE.user_language]+'" href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'myspace_blue_rect.svg) no-repeat center;" data-id="' + id + '"></a>'));
            prElem = prElem.children().first();
            prElem.click(function (e) {
                ODE.pluginPreview = 'tchat';
                ODE.commentTarget = e.target;
                $('.ow_submit_auto_click').show();
                parent.document.getElementById('share_from_private_room').dispatchEvent(new Event('animated-button-container-controllet_open-window'));
            });
        }
    });
};
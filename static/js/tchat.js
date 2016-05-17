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
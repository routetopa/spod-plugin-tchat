var SpodtchatCommentsList = function( params ){
    this.$context = $('#' + params.contextId);
    $.extend(this, params, owCommentListCmps.staticData);
    this.$loader = $('.ow_comment_list_loader', this.$context);
    //to reload component when receive a sync
    this.$relaodComponent = $('#new_message_icon_' + params.contextId , this.$context);
}

SpodtchatCommentsList.prototype = {
    init: function(){
        var self = this;
        $('.ow_comments_item', this.$context).hover(function(){$('.cnx_action', this).show();$('.ow_comments_date_hover', this).hide();}, function(){$('.cnx_action', this).hide();$('.ow_comments_date_hover', this).show();});
        this.$loader.one('click',
            function(){
                self.$loader.addClass('ow_preloader');
                $('a', self.$loader).hide();
                self.initialCount += self.loadMoreCount;
                self.reload();
            }
        );

        this.$relaodComponent.live('click',
            function(){
                self.reload();
            }
        );

//        OW.bind('base.comments_list_update',
//            function(data){
//                if( data.entityType == self.entityType && data.entityId == self.entityId && data.id != self.cid ){
//                    self.reload();
//                }
//            }
//        );

        OW.trigger('base.comments_list_init', {entityType: this.entityType, entityId: this.entityId}, this);

        OW.bind('base.comment_add', function(data){ if( data.entityType == self.entityType && data.entityId == self.entityId ) self.initialCount++ });

        if( this.pagesCount > 0 )
        {
            for( var i = 1; i <= this.pagesCount; i++ )
            {
                $('a.page-'+i, self.$context).bind( 'click', {i:i},
                    function(event){
                        self.reload(event.data.i);
                    }
                );
            }
        }

        $.each(this.actionArray.comments,
            function(i,o){
                $('#'+i).click(
                    function(){
                        if( confirm(self.delConfirmMsg) )
                        {
                            $(this).closest('div.ow_comments_item').slideUp(300, function(){
                                $(this).remove();
                                OW.trigger('base.comment_delete', {}, this);
                            });
                            $.ajax({
                                type: 'POST',
                                url: self.delUrl,
                                data: {
                                    cid:self.cid,
                                    commentCountOnPage:self.commentCountOnPage,
                                    ownerId:self.ownerId,
                                    pluginKey:self.pluginKey,
                                    displayType:self.displayType,
                                    entityType:self.entityType,
                                    entityId:self.entityId,
                                    initialCount:self.initialCount,
                                    page:self.page,
                                    commentId:o
                                },
                                dataType: 'json',
                                success : function(data){
                                    if(data.error){
                                        OW.error(data.error);
                                        return;
                                    }

                                    self.$context.replaceWith(data.commentList);
                                    OW.addScript(data.onloadScript);

                                    var eventParams = {
                                        entityType: self.entityType,
                                        entityId: self.entityId,
                                        commentCount: data.commentCount
                                    };

                                    OW.trigger('base.comment_delete', eventParams, this);
                                }
                            });
                        }
                    }
                );
            }
        );

        $.each(this.actionArray.users,
            function(i,o){
                $('#'+i).click(
                    function(){
                        OW.Users.deleteUser(o);
                    }
                );
            }
        );

        //ISISLab CODE - Resport abuse from comment management
        $.each(this.actionArray.abuses,
            function(i,o){
                $('#'+i).click(
                    function(){
                        try{
                            var form_content = $("#report-abuse-confirm").children();
                            $("#abuseMessage").html("Il seguente commento risulta inappropriato :\n \"" +  o.message + "\"");
                            $("#commentId").val(o.id);

                            window.report_abuse_floatbox = new OW_FloatBox({
                                $title: 'Report abuse',
                                $contents: form_content,
                                icon_class: "ow_ic_delete",
                                width: 450
                            });
                        }catch(err){
                            alert(err.message);
                        }
                    }
                );
            }
        );

        $.each(this.actionArray.remove_abuses,
            function(i,o){
                $('#'+i).click(
                    function(){
                        try{
                            var form_content = $("#remove-abuse-confirm").children();
                            $("#commentId").val(o.id);

                            window.remove_abuse_floatbox = new OW_FloatBox({
                                $title: 'Remove abuse',
                                $contents: form_content,
                                icon_class: "ow_ic_delete",
                                width: 450
                            });
                        }catch(err){
                            alert(err.message);
                        }
                    }
                );
            }
        );

        //point out abuse comment
        var abuseCommentId = window.location.href.split("#")[1];
        if(abuseCommentId && !$.jStorage.get('abuseCommentFound')){
            self.findPageForComment(abuseCommentId, 1, this.pages.length - 1);
        }

        //ISISLab CODE - end report abuse management

        for( i = 0; i < this.commentIds.length; i++ )
        {
            if( $('#att'+this.commentIds[i]).length > 0 )
            {
                $('.attachment_delete',$('#att'+this.commentIds[i])).bind( 'click', {i:i},
                    function(e){

                        $('#att'+self.commentIds[e.data.i]).slideUp(300, function(){$(this).remove();});

                        $.ajax({
                            type: 'POST',
                            url: self.delAtchUrl,
                            data: {
                                cid:self.cid,
                                commentCountOnPage:self.commentCountOnPage,
                                ownerId:self.ownerId,
                                pluginKey:self.pluginKey,
                                displayType:self.displayType,
                                entityType:self.entityType,
                                entityId:self.entityId,
                                page:self.page,
                                initialCount:self.initialCount,
                                loadMoreCount:self.loadMoreCount,
                                commentId:self.commentIds[e.data.i]
                            },
                            dataType: 'json'
                        });
                    }
                );
            }
        }
    },

    reload:function( page ){
        var self = this;
        $.ajax({
            type: 'POST',
            url: self.respondUrl,
            data: {
                cid:self.cid,
                commentCountOnPage:self.commentCountOnPage,
                ownerId:self.ownerId,
                pluginKey:self.pluginKey,
                displayType:self.displayType,
                entityType:self.entityType,
                entityId:self.entityId,
                initialCount:self.initialCount,
                loadMoreCount:self.loadMoreCount,
                page:page
            },
            dataType: 'json',
            success : function(data){
                if(data.error){
                    OW.error(data.error);
                    return;
                }
                self.$loader.removeClass('ow_preloader');
                $('a', self.$loader).hide();
                self.$context.replaceWith(data.commentList);
                OW.addScript(data.onloadScript);
                $("#ccount_" + data.entityId).html(data.commentCount);
                $("#new_message_" + data.entityId).css('color', 'transparent');
                $("#new_message_" + data.entityId).removeClass("newMessagesArrived");
                $("#comment_container_" + data.entityId).removeClass("emphasizedComment");

                window.tchatCommentCmps.refreshCommentsBehavior();

            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                OW.error('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    },

    findPageForComment:function (commentId, currentPage, maxPage){
        var self = this;
        $.ajax({
            type: 'POST',
            url: self.respondUrl,
            data: {
                cid:self.cid,
                commentCountOnPage:self.commentCountOnPage,
                ownerId:self.ownerId,
                pluginKey:self.pluginKey,
                displayType:self.displayType,
                entityType:self.entityType,
                entityId:self.entityId,
                page: currentPage,
                initialCount:self.initialCount,
                loadMoreCount:self.loadMoreCount,
                commentId:commentId
            },
            dataType: 'json',
            success : function(data){
                if(data.error){
                    OW.error(data.error);
                    return;
                }
                if(data.commentList.indexOf(commentId) == -1)
                {
                    if(currentPage <= maxPage ){
                        self.findPageForComment(commentId,currentPage + 1, maxPage);
                    }else{
                        return;
                    }
                }else{
                    $.jStorage.set('abuseCommentFound', true);
                    self.$context.replaceWith(data.commentList);
                    OW.addScript(data.onloadScript);
                    window.location.hash=commentId;
                }

            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                OW.error('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    }
};

SPODTCHAT = {};
SPODTCHAT.commentSendMessage = function(message, context)
{
    var self = context;

    //1 neutral - 2 up - 3 down
    var sentiment = $("#comment_sentiment_"+self.entityId).attr('sentiment');

    var dataToSend = {
        entityType: self.entityType,
        entityId: self.entityId,
        displayType: self.displayType,
        pluginKey: self.pluginKey,
        ownerId: self.ownerId,
        cid: self.uid,
        attchUid: self.attchUid,
        commentCountOnPage: self.commentCountOnPage,
        commentText: message,
        initialCount: self.initialCount,
        datalet: ODE.dataletParameters,
        plugin: ODE.pluginPreview,
        sentiment: (typeof sentiment === 'undefined') ? '' : sentiment
    };

    if( self.attachmentInfo ){
        dataToSend.attachmentInfo = JSON.stringify(self.attachmentInfo);
    }
    else if( self.oembedInfo ){
        dataToSend.oembedInfo = JSON.stringify(self.oembedInfo);
    }

    $.ajax({
        type: 'post',
        //url: self.addUrl,
        url: window.owCommentListCmps.staticData.addUrl,
        data: dataToSend,
        dataType: 'JSON',
        success: function(data){
            self.repaintCommentsList(data);
            //OW.trigger('base.photo_attachment_uid_update', {uid:self.attchUid, newUid:data.newAttachUid});
            OW.trigger('base.file_attachment', {uid:self.attchUid, newUid:data.newAttachUid});
            self.eventParams.commentCount = data.commentCount;
            OW.trigger('base.comment_added', self.eventParams);
            self.attchUid = data.newAttachUid;

            self.$formWrapper.removeClass('ow_preloader');
            self.$commentsInputCont.show();

            /* ODE */
            // Remove ic_ok icon from comment field
            $("#" + $(ODE.commentTarget).attr("data-id") + '_placeholder').remove();
            ODE.commentTarget = null;
            ODE.reset();
            /* ODE */

            $('.ow_file_attachment_preview').html("");

            window.tchatCommentCmps.refreshCommentsBehavior();
            //setTimeout(function(){new Function(data.onloadScript)();}, 1000);

        },
        error: function( XMLHttpRequest, textStatus, errorThrown ){
            OW.error(textStatus);
        },
        complete: function(){

        }
    });

    self.$textarea.val('').keyup().trigger('input.autosize');
}

OwComments.prototype.initTextarea = function()
{
    OW.bind('base.update_attachment',
        function(data){
            if( data.uid == self.attchUid ){
                self.attachmentInfo = data;
                self.$textarea.focus();
                self.submitHandler = self.realSubmitHandler;
                OW.trigger('base.comment_attachment_added', self.eventParams);
            }
        }
    );

    /* ODE */
    ODE.reset();
    ODE.addOdeOnComment();
    /* ODE */

    var self = this;
    this.realSubmitHandler = function(){

        self.initialCount++;

        //self.sendMessage(self.$textarea.val());
        SPODTCHAT.commentSendMessage(self.$textarea.val(), self);

        self.attachmentInfo = false;
        self.oembedInfo = false;
        self.$hiddenBtnCont.hide();
        if( this.mediaAllowed ){
            OWLinkObserver.getObserver(self.textAreaId).resetObserver();
        }
        self.$attchCont.empty();
        OW.trigger('base.photo_attachment_reset', {pluginKey:self.pluginKey, uid:self.attchUid});
        OW.trigger('base.comment_add', self.eventParams);

        self.$formWrapper.addClass('ow_preloader');
        self.$commentsInputCont.hide();
    };

    this.submitHandler = this.realSubmitHandler;

    this.$textarea
        .bind('keypress',
        function(e){
            if( e.which === 13 && !e.shiftKey ){
                e.stopImmediatePropagation();
                var textBody = $(this).val();

                if ( $.trim(textBody) == '' && !self.attachmentInfo && !self.oembedInfo ){
                    OW.error(self.labels.emptyCommentMsg);
                    return false;
                }

                self.submitHandler();
                return false;
            }
        }
    )
        .one('focus', function(){$(this).removeClass('invitation').val('').autosize({callback:function(data){OW.trigger('base.comment_textarea_resize', self.eventParams);}});});

    this.$hiddenBtnCont.unbind('click').click(function(){self.submitHandler();});

    if( this.mediaAllowed ){
        OWLinkObserver.observeInput(this.textAreaId, function( link ){
            if( !self.attachmentInfo ){
                self.$attchCont.html('<div class="ow_preloader" style="height: 30px;"></div>');
                this.requestResult( function( r ){
                    self.$attchCont.html(r);
                    self.$hiddenBtnCont.show();

                    OW.trigger('base.comment_attach_media', {})
                });
                this.onResult = function( r ){
                    self.oembedInfo = r;
                    if( $.isEmptyObject(r) ){
                        self.$hiddenBtnCont.hide();
                    }
                };
            }
        });
    }
};
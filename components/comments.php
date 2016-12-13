<?php

class SPODTCHAT_CMP_Comments extends BASE_CMP_Comments
{
    public function __construct( SPODTCHAT_CLASS_CommentsParams $params)
    {
        OW_Component::__construct();
        $this->params = $params;
        $this->batchData = $params->getBatchData();
        $this->staticData = empty($this->batchData['_static']) ? array() : $this->batchData['_static'];
        $this->batchData = isset($this->batchData[$params->getEntityType()][$params->getEntityId()]) ? $this->batchData[$params->getEntityType()][$params->getEntityId()] : array();

        srand(time());
        $this->id = $params->getEntityType() . $params->getEntityId() . rand(1, 10000);
        $this->cmpContextId = "comments-$this->id";
        $this->assign('cmpContext', $this->cmpContextId);
        $this->assign('wrapInBox', $params->getWrapInBox());
        $this->assign('topList', in_array($params->getDisplayType(), array(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST, BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)));
        $this->assign('bottomList', $params->getDisplayType() == BASE_CommentsParams::DISPLAY_TYPE_WITH_PAGING);
        $this->assign('mini', $params->getDisplayType() == BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI);

        $this->isAuthorized = OW::getUser()->isAuthorized($params->getPluginKey(), 'add_comment') && $params->getAddComment();

        if ( !$this->isAuthorized )
        {
            $errorMessage = $params->getErrorMessage();

            if ( empty($errorMessage) )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus($params->getPluginKey(), 'add_comment');
                $errorMessage = OW::getUser()->isAuthenticated() ? $status['msg'] : OW::getLanguage()->text('base', 'comments_add_login_message');
            }

            $this->assign('authErrorMessage', $errorMessage);
        }

        $this->initForm();
    }

    public function initForm()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodtchat')->getStaticJsUrl() . 'vendor/livequery-1.1.1/jquery.livequery.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodtchat')->getStaticJsUrl() . 'tchat.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodtchat')->getStaticJsUrl() . 'commentsList.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodtchat')->getStaticJsUrl() . 'spod_attachments.js');
        //OW::getDocument()->addOnloadScript("alert(\"".UTIL_Url::selfUrl()."\");");

        $jsParams = array(
            'entityType'     => $this->params->getEntityType(),
            'entityId'       => $this->params->getEntityId(),
            'pluginKey'      => $this->params->getPluginKey(),
            'contextId'      => $this->cmpContextId,
            'userAuthorized' => $this->isAuthorized,
            'customId'       => $this->params->getCustomId(),
        );

        if ( $this->isAuthorized )
        {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.autosize.js');
            $taId = 'cta' . $this->id;
            $attchId = 'attch' . $this->id;
            $attchUid = BOL_CommentService::getInstance()->generateAttachmentUid($this->params->getEntityType(), $this->params->getEntityId());

            $jsParams['ownerId'] = $this->params->getOwnerId();
            $jsParams['cCount'] = isset($this->batchData['countOnPage']) ? $this->batchData['countOnPage'] : $this->params->getCommentCountOnPage();
            $jsParams['initialCount'] = $this->params->getInitialCommentsCount();
            $jsParams['loadMoreCount'] = $this->params->getLoadMoreCount();
            $jsParams['countOnPage'] = $this->params->getCommentCountOnPage();
            $jsParams['uid'] = $this->id;
            $jsParams['addUrl'] = OW::getRouter()->urlFor('SPODTCHAT_CTRL_Comments', 'addComment');
            $jsParams['displayType'] = $this->params->getDisplayType();
            $jsParams['textAreaId'] = $taId;
            $jsParams['attchId'] = $attchId;
            $jsParams['attchUid'] = $attchUid;
            $jsParams['enableSubmit'] = true;
            $jsParams['numberOfNestedLevel'] = $this->params->getNumberOfNestedLevel();
            $jsParams['commentEntityType']   = $this->params->getCommentEntityType();
            $jsParams['commentEntityId']     = $this->params->getCommentEntityId();
            $jsParams['mediaAllowed'] = BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed();
            $jsParams['labels'] = array(
                'emptyCommentMsg' => OW::getLanguage()->text('base', 'empty_comment_error_msg'),
                'disabledSubmit' => OW::getLanguage()->text('base', 'submit_disabled_error_msg'),
                'attachmentLoading' => OW::getLanguage()->text('base', 'submit_attachment_not_loaded'),
            );

            if ( !empty($this->staticData['currentUserInfo']) )
            {
                $userInfoToAssign = $this->staticData['currentUserInfo'];
            }
            else
            {
                $currentUserInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));
                $userInfoToAssign = $currentUserInfo[OW::getUser()->getId()];
            }

            $buttonContId = 'bCcont' . $this->id;

            if ( BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed() )
            {
                //$this->addComponent('img_attch', new BASE_CLASS_Attachment($this->params->getPluginKey(), $attchUid, $buttonContId));
                $this->addComponent('file_attch', new SPODTCHAT_CLASS_FileAttachment($this->params->getPluginKey(), $attchUid, $buttonContId));
            }

            $this->assign('buttonContId', $buttonContId);
            $this->assign('currentUserInfo', $userInfoToAssign);
            $this->assign('formCmp', true);
            $this->assign('taId', $taId);
            $this->assign('attchId', $attchId);
            $this->assign('commentId', $this->params->getEntityId());
            $this->assign('topList', true);
            $this->assign('bottomList', false);

            $this->assign("temp_attach_uid", $attchUid);
            $this->assign('theme_image_url', OW::getThemeManager()->getInstance()->getThemeImagesUrl());
        }

        OW::getDocument()->addOnloadScript("$('#". $taId ."').livequery( function(){
                                              window.tchatComments['" . $this->params->getEntityId() ."'] = new OwComments(". json_encode($jsParams) .");
                                              $('#". $taId ."').expire();
                                           });");

        $this->assign('displayType', $this->params->getDisplayType());

        // add comment list cmp
        $this->addComponent('commentList', new SPODTCHAT_CMP_CommentsList($this->params, $this->id));

        $js = UTIL_JsGenerator::composeJsString('
                TCHAT                               = {};
                TCHAT.currentUserId                 = {$current_user_id}
                TCHAT.ajax_tchat_get_comment_list   = {$ajax_tchat_get_comment_list}
            ', array(
            'current_user_id'             => OW::getUser()->getId(),
            'ajax_tchat_get_comment_list' => OW::getRouter()->urlFor('SPODTCHAT_CTRL_Ajax', 'getCommentList'),
            'comment_params'              => $jsParams
        ));
        OW::getDocument()->addOnloadScript($js);
    }
}
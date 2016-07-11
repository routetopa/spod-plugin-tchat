<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

class SPODTCHAT_CMP_Comments extends BASE_CMP_Comments
{
    public static $NUMBER_OF_NESTED_LEVEL = 3;
    public static $COMMENT_ENTITY_TYPE  = COCREATION_BOL_Service::COMMENT_ENTITY_TYPE;//default value
    public static $COMMENT_ENTITY_ID    = 1;//default value

    public function __construct( BASE_CommentsParams $params, $nested_level, $entity_type, $entity_id)
    {
        $this::$NUMBER_OF_NESTED_LEVEL = $nested_level;
        $this::$COMMENT_ENTITY_TYPE    = $entity_type;
        $this::$COMMENT_ENTITY_ID      = $entity_id;
        parent::__construct($params);
    }

    public function initForm()
    {
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
                $this->addComponent('attch', new BASE_CLASS_Attachment($this->params->getPluginKey(), $attchUid, $buttonContId));
            }

            $this->assign('buttonContId', $buttonContId);
            $this->assign('currentUserInfo', $userInfoToAssign);
            $this->assign('formCmp', true);
            $this->assign('taId', $taId);
            $this->assign('attchId', $attchId);
            $this->assign('commentId', $this->params->getEntityId());
        }

        OW::getDocument()->addOnloadScript("new OwComments(" . json_encode($jsParams) . ");");

        $this->assign('displayType', $this->params->getDisplayType());

        // add comment list cmp
        $this->addComponent('commentList', new SPODTCHAT_CMP_CommentsList($this->params, $this->id));

        $js = UTIL_JsGenerator::composeJsString('
                TCHAT                             = {};
                TCHAT.currentUserId               = {$current_user_id}
                TCHAT.ajax_tchat_get_comment_list = {$ajax_tchat_get_comment_list}
                TCHAT.commentParams               = {$comment_params}
            ', array(
            'current_user_id'             => OW::getUser()->getId(),
            'ajax_tchat_get_comment_list' => OW::getRouter()->urlFor('SPODTCHAT_CTRL_Ajax', 'getCommentListRendered'),
            'comment_params'              => $jsParams
        ));
        OW::getDocument()->addOnloadScript($js);
    }
}

?>
<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

class SPODTCHAT_CMP_CommentsList extends BASE_CMP_CommentsList
{
	protected $actionArr = array('comments' => array(), 'users' => array(), 'abuses' => array(), 'remove_abuses' => array());

	protected function init()
    {
        if ( $this->commentCount === 0 && $this->params->getShowEmptyList() )
        {
            $this->assign('noComments', true);
        }

        $countToLoad = 0;

        if ( $this->commentCount === 0 )
        {
            $commentList = array();
        }
        else if ( in_array($this->params->getDisplayType(), array(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST, BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)) )
        {	
            $commentList = empty($this->batchData['commentsList']) ? $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), 1, $this->params->getInitialCommentsCount()) : $this->batchData['commentsList'];
            $commentList = array_reverse($commentList);
            $countToLoad = $this->commentCount - $this->params->getInitialCommentsCount();
            $this->assign('countToLoad', $countToLoad);
        }
        else
        {
            $commentList = $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), $this->page, $this->params->getCommentCountOnPage());
        }

        OW::getEventManager()->trigger(new OW_Event('base.comment_list_prepare_data', array('list' => $commentList, 'entityType' => $this->params->getEntityType(), 'entityId' => $this->params->getEntityId())));
        OW::getEventManager()->bind('base.comment_item_process', array($this, 'itemHandler'));
        $this->assign('comments', $this->processList($commentList));

        $pages = false;

        if ( $this->params->getDisplayType() === BASE_CommentsParams::DISPLAY_TYPE_WITH_PAGING )
        {
            $pagesCount = $this->commentService->findCommentPageCount($this->params->getEntityType(), $this->params->getEntityId(), $this->params->getCommentCountOnPage());

            if ( $pagesCount > 1 )
            {
                $pages = $this->getPages($this->page, $pagesCount, 8);
                $this->assign('pages', $pages);
            }
        }
        else
        {
            $pagesCount = 0;
        }

        $this->assign('loadMoreLabel', OW::getLanguage()->text('base', 'comment_load_more_label'));

        static $dataInit = false;

        if ( !$dataInit )
        {
            $staticDataArray = array(
                'respondUrl'      => OW::getRouter()->urlFor('SPODTCHAT_CTRL_Ajax', 'getCommentList'),//when page button is being pressed
                'delUrl'          => OW::getRouter()->urlFor('SPODTCHAT_CTRL_Ajax', 'deleteComment'),
                'addUrl'          => OW::getRouter()->urlFor('SPODTCHAT_CTRL_Ajax', 'addComment'),
                'delAtchUrl'      => OW::getRouter()->urlFor('SPODTCHAT_CTRL_Attachment', 'deleteCommentAtatchment'),
                'delConfirmMsg'   => OW::getLanguage()->text('base', 'comment_delete_confirm_message'),
                'preloaderImgUrl' => OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'ajax_preloader_button.gif'
            );
            OW::getDocument()->addOnloadScript("window.owCommentListCmps.staticData=" . json_encode($staticDataArray) . ";");
            $dataInit = true;
        }

        $jsParams = json_encode(
            array(
                'totalCount'         => $this->commentCount,
                'contextId'          => $this->cmpContextId,
                'displayType'        => $this->params->getDisplayType(),
                'entityType'         => $this->params->getEntityType(),
                'entityId'           => $this->params->getEntityId(),
                'pagesCount'         => $pagesCount,
                'initialCount'       => $this->params->getInitialCommentsCount(),
                'loadMoreCount'      => $this->params->getLoadMoreCount(),
                'commentIds'         => $this->commentIdList,
                'pages'              => $pages,
                'pluginKey'          => $this->params->getPluginKey(),
                'ownerId'            => $this->params->getOwnerId(),
                'commentCountOnPage' => $this->params->getCommentCountOnPage(),
                'cid'                => $this->id,
                'actionArray'        => $this->actionArr,
                'countToLoad'        => $countToLoad
            )
        );

        OW::getDocument()->addOnloadScript("window.tchatCommentsListParams['" . $this->id ."'] =  " . $jsParams . ";");

        $this->assign('components_url', SPODPR_COMPONENTS_URL);
        $this->assign('cid', $this->params->getEntityId());
    }
	
	
	public function itemHandler( BASE_CLASS_EventProcessCommentItem $e )
    {
        $language = OW::getLanguage();

        $deleteButton = false;
        $cAction = null;
        $value = $e->getItem();

        if ( /*$this->isOwnerAuthorized ||*/ $this->isModerator || (int) OW::getUser()->getId() === (int) $value->getUserId() )
        {
            $deleteButton = true;
        }

        if ( $this->isBaseModerator || $deleteButton ) {
            $cAction = new BASE_CMP_ContextAction();
            $parentAction = new BASE_ContextAction();
            $parentAction->setKey('parent');
            $parentAction->setClass('ow_comments_context');
            $cAction->addAction($parentAction);

            if ($deleteButton) {
                $delAction = new BASE_ContextAction();
                $delAction->setLabel($language->text('base', 'contex_action_comment_delete_label'));
                $delAction->setKey('udel');
                $delAction->setParentKey($parentAction->getKey());
                $delId = 'del-' . $value->getId();
                $delAction->setId($delId);
                $this->actionArr['comments'][$delId] = $value->getId();
                $cAction->addAction($delAction);
            }

            if ($this->isBaseModerator && $value->getUserId() != OW::getUser()->getId()) {
                $modAction = new BASE_ContextAction();
                $modAction->setLabel($language->text('base', 'contex_action_user_delete_label'));
                $modAction->setKey('cdel');
                $modAction->setParentKey($parentAction->getKey());
                $delId = 'udel-' . $value->getId();
                $modAction->setId($delId);
                $this->actionArr['users'][$delId] = $value->getUserId();
                $cAction->addAction($modAction);
            }
        }

        if ( $this->params->getCommentPreviewMaxCharCount() > 0 && mb_strlen($value->getMessage()) > $this->params->getCommentPreviewMaxCharCount() )
        {
            $e->setDataProp('previewMaxChar', $this->params->getCommentPreviewMaxCharCount());
        }

        $e->setDataProp('cnxAction', empty($cAction) ? '' : $cAction->render());
    }

    protected function getEntityLevel($id){

        $comment = BOL_CommentService::getInstance()->findComment($id);
        $level = 0;
        while($comment)
        {
            $entity = BOL_CommentEntityDao::getInstance()->findById($comment->getCommentEntityId());
            $comment = BOL_CommentService::getInstance()->findComment($entity->entityId);
            $level++;
        }
        return $level - 1;

    }

    protected function processList( $commentList )
    {

        /* @var $value BOL_Comment */
        foreach ( $commentList as $value )
        {
            $this->userIdList[] = $value->getUserId();
            $this->commentIdList[] = $value->getId();
        }

        $userAvatarArrayList = empty($this->staticData['avatars']) ? $this->avatarService->getDataForUserAvatars($this->userIdList) : $this->staticData['avatars'];


        foreach ( $commentList as $value )
        {
            /*Add nasted level*/
            if(!isset($this->params->level)) $this->params->level = $this->getEntityLevel($value->getId());

            if($this->params->level <= SPODTCHAT_CMP_Comments::$NUMBER_OF_NESTED_LEVEL) {
                //nasted comment
                $commentsParams = new BASE_CommentsParams('spodtchat', SPODTCHAT_CMP_Comments::$COMMENT_ENTITY_TYPE);
                $commentsParams->setEntityId($value->getId());
                $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI);
                $commentsParams->setCommentCountOnPage(5);
                $commentsParams->setOwnerId((OW::getUser()->getId()));
                $commentsParams->setAddComment(TRUE);
                $commentsParams->setWrapInBox(false);
                $commentsParams->setShowEmptyList(false);
                $commentsParams->level = $this->params->level + 1;

                $datalet = ODE_BOL_Service::getInstance()->getDataletByPostIdWhereArray($value->getId(), array("comment", "public-room", "tchat"));
                $this->addComponent('nestedComments' . $value->getId(), new SPODTCHAT_CMP_Comments($commentsParams, SPODTCHAT_CLASS_Consts::$NUMBER_OF_NESTED_LEVEL, SPODTCHAT_CMP_Comments::$COMMENT_ENTITY_TYPE, SPODTCHAT_CMP_Comments::$COMMENT_ENTITY_ID));

                $this->assign('commentSentiment' . $value->getId(), SPODTCHAT_BOL_Service::getInstance()->getCommentSentiment($value->getId())->sentiment);
                $this->assign('commentsCount' . $value->getId(), BOL_CommentService::getInstance()->findCommentCount(SPODTCHAT_CMP_Comments::$COMMENT_ENTITY_TYPE, $value->getId()));
                $this->assign('commentsLevel' . $value->getId(), $this->params->level);
                $this->assign('levelsLimit', SPODTCHAT_CLASS_Consts::$NUMBER_OF_NESTED_LEVEL);
            }

            /*End adding nasted level*/

            $cmItemArray = array(
                'displayName' => $userAvatarArrayList[$value->getUserId()]['title'],
                'avatarUrl'   => $userAvatarArrayList[$value->getUserId()]['src'],
                'profileUrl'  => $userAvatarArrayList[$value->getUserId()]['url'], 
                'content'     => $value->getMessage(),
                'date'        => UTIL_DateTime::formatDate($value->getCreateStamp()),
                'userId'      => $value->getUserId(),
                'commentId'   => $value->getId(),
                'datalet'     => !empty($datalet),
                'avatar'      => $userAvatarArrayList[$value->getUserId()]
            );

            $contentAdd = '';

            if ( $value->getAttachment() !== null )
            {
                $tempCmp = new SPODTCHAT_CMP_TchatOembedAttachment((array) json_decode($value->getAttachment()), $this->isOwnerAuthorized);
                $contentAdd .= '<div class="ow_attachment ow_small" id="att' . $value->getId() . '">' . $tempCmp->render() . '</div>';
            }

            $cmItemArray['content_add'] = $contentAdd;

            $event = new BASE_CLASS_EventProcessCommentItem('base.comment_item_process', $value, $cmItemArray);
            OW::getEventManager()->trigger($event);
            $arrayToAssign[] = $event->getDataArr();

        }

        return (isset($arrayToAssign)) ? $arrayToAssign : array();
    }
}

?>
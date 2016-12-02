<?php

class SPODTCHAT_CTRL_Test extends OW_ActionController
{
    public function index(array $params)
    {
        //comment and rate
        $commentsParams = new BASE_CommentsParams('cocreation', COCREATION_BOL_Service::ROOM_ENTITY_TYPE);
        $commentsParams->setEntityId(21);
        $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_PAGING);
        $commentsParams->setCommentCountOnPage(5);
        $commentsParams->setOwnerId((OW::getUser()->getId()));
        $commentsParams->setAddComment(true);
        $commentsParams->setWrapInBox(false);
        $commentsParams->setShowEmptyList(false);
        $commentsParams->setCommentPreviewMaxCharCount(5000);

        $commentsParams->level  = 0;
        $commentsParams->nodeId = 0;
        SPODTCHAT_CLASS_Consts::$NUMBER_OF_NESTED_LEVEL = 2;

        /* ODE */
        /* if (OW::getPluginManager()->isPluginActive('spodpr'))
             $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn', array('datalet', 'link')));*/
        /* ODE */

        $commentCmp = new SPODTCHAT_CMP_Comments($commentsParams, 1, COCREATION_BOL_Service::COMMENT_ENTITY_TYPE, 21);
        $this->addComponent('comments', $commentCmp);
    }
}
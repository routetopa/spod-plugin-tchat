<?php
class SPODTCHAT_CLASS_CommentsParams
{
    /**
     * @deprecated since version 1.6.1
     */
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_FULL_LIST = 1;

    /**
     * @deprecated since version 1.6.1
     */
    const DISPLAY_TYPE_TOP_FORM_WITH_PAGING = 2;

    /**
     * @deprecated since version 1.6.1
     */
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST = 3;

    /**
     * @deprecated since version 1.6.1
     */
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST_AND_MINI_IPC = 4;
    const DISPLAY_TYPE_WITH_PAGING = 10;
    const DISPLAY_TYPE_WITH_LOAD_LIST = 20;
    const DISPLAY_TYPE_WITH_LOAD_LIST_MINI = 30;

    private $pluginKey;
    private $entityType;
    private $entityId;
    private $ownerId;
    private $displayType;
    private $commentCountOnPage;
    private $addComment;
    private $wrapInBox;
    private $batchData;
    private $errorMessage;
    private $initialCommentsCount;
    private $loadMoreCount;
    private $showEmptyList;
    private $customId;
    private $commentPreviewMaxCharCount;
    private $numberOfNestedLevel;
    private $commentEntityType;
    private $commentEntityId;

    /**
     * Constructor.
     *
     * @param string $pluginKey
     * @param string $entityType
     */
    public function __construct( $pluginKey, $entityType )
    {
        $this->pluginKey = trim($pluginKey);
        $this->entityType = trim($entityType);
        $this->entityId = 1;
        $this->displayType = self::DISPLAY_TYPE_WITH_LOAD_LIST;
        $this->addComment = true;
        $this->wrapInBox = true;
        $this->initialCommentsCount = 10;
        $this->loadMoreCount = 10;
        $this->commentCountOnPage = 10;
        $this->showEmptyList = true;
        $this->commentPreviewMaxCharCount = 200;
    }

    public function getBaseCommentParamsObject(){
        $baseParams = new BASE_CommentsParams($this->pluginKey, $this->entityType);
        $baseParams->setEntityId($this->entityId);
        $baseParams->setOwnerId($this->ownerId);
        $baseParams->setDisplayType($this->displayType);
        $baseParams->setCommentCountOnPage($this->commentCountOnPage);
        $baseParams->setAddComment($this->addComment);
        $baseParams->setWrapInBox($this->wrapInBox);
        $baseParams->setBatchData($this->batchData);
        $baseParams->setErrorMessage($this->errorMessage);
        $baseParams->setInitialCommentsCount($this->initialCommentsCount);
        $baseParams->setLoadMoreCount($this->loadMoreCount);
        $baseParams->setShowEmptyList($this->showEmptyList);
        $baseParams->setCustomId($this->customId);
        $baseParams->setCommentPreviewMaxCharCount($this->commentPreviewMaxCharCount);
        return $baseParams;
    }

    /**
     * @return string
     */
    public function getPluginKey()
    {
        return $this->pluginKey;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     *
     * @param integer $entityId
     * @return BASE_CommentsParams
     */
    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param integer $ownerId
     * @return BASE_CommentsParams
     */
    public function setOwnerId( $ownerId )
    {
        $this->ownerId = (int) $ownerId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getDisplayType()
    {
        return $this->displayType;
    }

    /**
     * @param integer $displayType
     * @return BASE_CommentsParams
     */
    public function setDisplayType( $displayType )
    {
        if ( in_array($displayType, array(self::DISPLAY_TYPE_WITH_PAGING, self::DISPLAY_TYPE_WITH_LOAD_LIST, self::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)) )
        {
            $this->displayType = (int) $displayType;
            return $this;
        }

        switch ( $displayType )
        {
            case self::DISPLAY_TYPE_BOTTOM_FORM_WITH_FULL_LIST:
            case self::DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST:
                $this->displayType = self::DISPLAY_TYPE_WITH_LOAD_LIST;
                break;

            case self::DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST_AND_MINI_IPC:
                $this->displayType = self::DISPLAY_TYPE_WITH_LOAD_LIST_MINI;
                break;

            case self::DISPLAY_TYPE_TOP_FORM_WITH_PAGING:
                $this->displayType = self::DISPLAY_TYPE_WITH_PAGING;
                break;

            default:
                $this->displayType = self::DISPLAY_TYPE_WITH_LOAD_LIST;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getCommentCountOnPage()
    {
        return $this->commentCountOnPage;
    }

    public function getNumberOfNestedLevel(){
        return $this->numberOfNestedLevel;
    }

    public function getCommentEntityType(){
        return $this->commentEntityType;
    }

    public function getCommentEntityId(){
        return $this->commentEntityId;
    }
    /**
     * @param integer $commentCountOnPage
     * @return BASE_CommentsParams
     */
    public function setCommentCountOnPage( $commentCountOnPage )
    {
        $this->commentCountOnPage = (int) $commentCountOnPage;
        return $this;
    }

    public function getAddComment()
    {
        return $this->addComment;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setErrorMessage( $errorMessage )
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function setAddComment( $addComment )
    {
        $this->addComment = (bool) $addComment;
        return $this;
    }

    public function getWrapInBox()
    {
        return $this->wrapInBox;
    }

    public function setWrapInBox( $wrapInBox )
    {
        $this->wrapInBox = (bool) $wrapInBox;
        return $this;
    }

    public function getBatchData()
    {
        return $this->batchData;
    }

    public function setBatchData( array $data )
    {
        $this->batchData = $data;
        return $this;
    }

    public function getInitialCommentsCount()
    {
        return $this->initialCommentsCount;
    }

    public function setInitialCommentsCount( $initialCommentsCount )
    {
        $this->initialCommentsCount = (int) $initialCommentsCount;
        return $this;
    }

    public function getLoadMoreCount()
    {
        return $this->loadMoreCount;
    }

    public function setLoadMoreCount( $loadMoreCount )
    {
        $this->loadMoreCount = (int) $loadMoreCount;
        return $this;
    }

    public function getShowEmptyList()
    {
        return $this->showEmptyList;
    }

    public function setShowEmptyList( $showEmptyList )
    {
        $this->showEmptyList = (bool) $showEmptyList;
        return $this;
    }

    public function getCustomId()
    {
        return $this->customId;
    }

    public function setCustomId( $customId )
    {
        $this->customId = $customId;
    }

    public function getCommentPreviewMaxCharCount()
    {
        return (int) $this->commentPreviewMaxCharCount;
    }

    public function setCommentPreviewMaxCharCount( $commentPreviewMaxCharCount )
    {
        $this->commentPreviewMaxCharCount = (int) $commentPreviewMaxCharCount;
    }

    public function setNumberOfNestedLevel($nestedLevel){
        $this->numberOfNestedLevel = (int) $nestedLevel;
    }

    public function setCommentEntityType($entityType){
        $this->commentEntityType = $entityType;
    }

    public function setCommentEntityId($entityId){
        $this->commentEntityId = $entityId;
    }
}
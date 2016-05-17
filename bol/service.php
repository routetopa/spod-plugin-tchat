<?php

class SPODTCHAT_BOL_Service
{
    const ENTITY_TYPE = 'tchat_topic_entity';
    const ENTITY_COMMENT_TYPE = 'tchat_topic_comment_entity';

    /**
     * Singleton instance.
     *
     * @var SPODTCHAT_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SPODTCHAT_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    // READER

    public function getCommentSentiment($commentId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('commentId', $commentId);
        return SPODTCHAT_BOL_TchatCommentSentimentDao::getInstance()->findObjectByExample($example);
    }

    // WRITER

    public function addCommentSentiment($commentId, $sentiment)
    {
        $sent = new SPODTCHAT_BOL_TchatCommentSentiment();
        $sent->commentId = $commentId;
        $sent->sentiment = $sentiment;

        SPODTCHAT_BOL_TchatCommentSentimentDao::getInstance()->save($sent);
    }

}
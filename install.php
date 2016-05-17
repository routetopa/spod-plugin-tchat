<?php

$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_tchat_comment_sentiment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sentiment` smallint,
  `commentId` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;';

OW::getDbo()->query($sql);

$authorization = OW::getAuthorization();
$groupName = 'spodtchat';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'add_comment');
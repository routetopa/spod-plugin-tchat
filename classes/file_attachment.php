<?php

class SPODTCHAT_CLASS_FileAttachment extends OW_Component
{
    private $uid;
    private $inputSelector;
    private $showPreview;
    private $pluginKey;
    private $multiple;

    public function __construct( $pluginKey, $uid )
    {
        parent::__construct();
        $this->uid = $uid;
        $this->showPreview = true;
        $this->pluginKey = $pluginKey;
        $this->multiple = true;
    }

    public function getMultiple()
    {
        return $this->multiple;
    }

    public function setMultiple( $multiple )
    {
        $this->multiple = (bool) $multiple;
    }

    public function getInputSelector()
    {
        return $this->inputSelector;
    }

    public function setInputSelector( $inputSelector )
    {
        $this->inputSelector = trim($inputSelector);
    }

    public function getShowPreview()
    {
        return $this->showPreview;
    }

    public function setShowPreview( $showPreview )
    {
        $this->showPreview = (bool) $showPreview;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $items = BOL_AttachmentService::getInstance()->getFilesByBundleName($this->pluginKey, $this->uid);
        $itemsArr = array();

        foreach ( $items as $item )
        {
            $itemsArr[] = array('name' => $item['dto']->getOrigFileName(), 'size' => $item['dto']->getSize(), 'dbId' => $item['dto']->getId());
        }

        $params = array(
            'uid' => $this->uid,
            'submitUrl' => OW::getRouter()->urlFor('SPODTCHAT_CTRL_Attachment', 'addFile'),
            'deleteUrl' => OW::getRouter()->urlFor('SPODTCHAT_CTRL_Ajax', 'deleteFile'),
            'showPreview' => $this->showPreview,
            'selector' => $this->inputSelector,
            'pluginKey' => $this->pluginKey,
            'multiple' => $this->multiple,
            'lItems' => $itemsArr
        );
        //OW::getDocument()->addOnloadScript("window.tchatAttachmentParams['" . $this->uid . "'] = " . json_encode($params) . ";");
        OW::getDocument()->addOnloadScript("$('#". $this->uid ."').livequery( function(e){
                                                    window.tchatAttachment['". $this->uid ."'] = new SPODFileAttachment('". json_encode($params) ."');
                                           });");



        $this->assign('data', array('uid' => $this->uid, 'showPreview' => $this->showPreview, 'selector' => $this->inputSelector));
        $this->assign('static_attach_image_url', OW_ThemeManager::getInstance()->getThemeImagesUrl());
    }
}
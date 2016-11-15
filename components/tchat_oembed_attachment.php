<?php

class SPODTCHAT_CMP_TchatOembedAttachment extends OW_Component
{
    protected $uniqId, $oembed;
    private $FILE_EXT_ICONS_POSITION = array(
    'doc'  => '-510px -340px',
    'docx' => '-398px -60px',
    'pdf'  => '-6px -284px',
    'txt'  => '-174px -284px',
    'xls'  => '-6px -228px',
    'ppt'  => '-118px -284px',
    'pptx' => '-342px -60px',
    'dat'  => '-62px -116px',
    'rar'  => '-398px -116px',
    'zip'  => '-342px -284p');
    
    public function __construct( array $oembed, $delete = false )
    {
        parent::__construct();

        $this->oembed = $oembed;

        $this->assign('delete', $delete);
        $this->uniqId = uniqid("oe-");
        $this->assign("uniqId", $this->uniqId);
    }

    public function setDeleteBtnClass( $class )
    {
        $this->assign('deleteClass', $class);
    }

    public function setContainerClass( $class )
    {
        $this->assign('containerClass', $class);
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        
        $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($this->oembed["html"], "autoplay", 1);
        $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($code, "play", 1);
        
        $js->addScript('$(".ow_oembed_video_cover", "#" + {$uniqId}).click(function() { '
                . '$(".two_column", "#" + {$uniqId}).addClass("ow_video_playing"); '
                . '$(".attachment_left", "#" + {$uniqId}).html({$embed});'
                . 'OW.trigger("base.comment_video_play", {});'
                . 'return false; });', array(
            "uniqId" => $this->uniqId,
            "embed" => $code
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public function render()
    {
        if ( $this->oembed["type"] == "video" && !empty($this->oembed["html"]) )
        {
            $this->initJs();
        }
        $name = explode('/', $this->oembed['href']);
        $name = $name[count($name) - 1];
        $name = explode('_', $name);
        array_shift($name);
        $name = join("_",$name);
        $ext  = explode('.', $name)[1];

        $this->assign('data', $this->oembed);
        $this->assign('static_file_icons_image_url', OW_PluginManager::getInstance()->getPlugin('spodtchat')->getStaticUrl() . 'images/file_icons_vs_2_full.png');

        $this->assign('icon_position', $this->FILE_EXT_ICONS_POSITION[$ext] != null ? $this->FILE_EXT_ICONS_POSITION[$ext] : $this->FILE_EXT_ICONS_POSITION['dat']);
        $this->assign('file_name', $name);


        return parent::render();
    }
}
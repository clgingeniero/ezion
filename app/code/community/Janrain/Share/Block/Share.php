<?php

class Janrain_Share_Block_Share extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface {

    public function rpx_social_icons($onclick) {
        $social_pub = Mage::getStoreConfig('share/vars/socialpub');
        $social_providers = array_filter(explode(',', $social_pub));
        if (is_array($social_providers)) {
            $rpx_social_icons = '';
            
            foreach ($social_providers as $val) {
                $rpx_social_icons .= '<span class="janrain-provider-icon-16 janrain-provider-icon-'.$val.'" rel="'.$val.'" onclick="'.$onclick.'"></span>';
            }
            $buttons = '<span class="rpx_social_icons">' . $rpx_social_icons . '</span>';
            return $buttons;
        }
        return false;
    }

    /**
     * Adds a link to open the Share dialog
     *
     * @return string
     */
    protected function _toHtml() {
        $link = '';
        $share_url   = $this->getShareUrl()   ? "'{$this->getShareUrl()}'"   : 'document.location.href';
        $share_title = $this->getShareTitle() ? "'{$this->getShareTitle()}'" : 'document.title';
        $share_desc  = $this->getShareDesc()  ? "'{$this->getShareDesc()}'"  : "document.getElementsByName('description')[0].getAttribute('content')";
        $share_img   = $this->getShareImg()   ? "'{$this->getShareImg()}'"   : "document.getElementById('image').getAttribute('src')";
        $share_img   = $share_img             ? $share_img                   : 'null';
        $button_text = $this->getButtonText() ? $this->getButtonText()       : 'Share';
        $onclick     = "try { setShare($share_url, $share_title, $share_desc, $share_img, this.getAttribute('rel')); } catch (e) { setShare($share_url, $share_title, $share_desc, null, this.getAttribute('rel')) } ";

        if ($icons = $this->rpx_social_icons($onclick)) {
            $link .= '<div class="janrain-share-container"><span class="janrain-share-text">' . $button_text . '</span>'.$icons.'</div>';
        }

        return $link;
    }

    protected function _prepareLayout() {
        if ($this->getLayout()->getBlock('janrain_share_share') == false) {
            $block = $this->getLayout()
            ->createBlock('core/template', 'janrain_share_share')
            ->setData('message', $this->getShareText())
            ->setTemplate('janrain/share/share.phtml');
            $this->getLayout()->getBlock('before_body_end')->insert($block);
        }

        parent::_prepareLayout();
    }

}

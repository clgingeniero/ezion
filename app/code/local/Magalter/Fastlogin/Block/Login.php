<?php

class Magalter_Fastlogin_Block_Login extends Mage_Core_Block_Template {

    private $_enabled = 'fastlogin/configuration/enable';
    private $_ajaxAllowed = 'fastlogin/configuration/enableajax';
    private $_hideForm = 'fastlogin/configuration/hideform';
    private $_headerForm = 'fastlogin/configuration/headerform';
    private $_cartSideBar = 'fastlogin/configuration/cartsidebar';
    private $_horizontalPos = 'fastlogin/configuration/horizontal';
    private $_verticalPos = 'fastlogin/configuration/vertical';

    protected function _toHtml() {

        if ($this->checkConfig($this->_enabled)) {

            if (isset($this->_data['position']) || $this->checkConfig($this->_headerForm)) {

                if ($this->checkConfig($this->_hideForm)) {
                    $this->setData('hideForm', '1');
                }
                if ($this->checkConfig($this->_ajaxAllowed)) {
                    $this->setData('ajaxEnabled', '1');
                }
                if ($this->checkConfig($this->_cartSideBar)) {
                    $this->setData('cartSideBar', '1');
                }

                /* Horizontal and vertical corrections */
                if ($hp = $this->checkConfig($this->_horizontalPos, true)) {
                    if ((int) $hp) {
                        $this->setData('horizontal_correction', $hp);
                    }
                }
                if ($vp = $this->checkConfig($this->_verticalPos, true)) {
                    if ((int) $vp) {
                        $this->setData('vertical_correction', $vp);
                    }
                }

                $this->setTemplate('magalter_fastlogin/headerlogin.phtml');

                return parent::_toHtml();
            }
        }
    }
    
    protected function _prepareLayout() {        
       if($layout = $this->getLayout()) {
           if(Mage::getSingleton('customer/session')->getCustomer()->getId()) {           
               $topLinksBlock = $layout->getBlock('top.links');    
               if(is_object($topLinksBlock)) { $topLinksBlock->removeLinkByUrl(Mage::getUrl('customer/account/create')); }    
           }         
       }           
    }
    

    private function checkConfig($configPath, $value = false) {

        if ($value) {
            return Mage::getStoreConfig($configPath);
        }

        if (Mage::getStoreConfig($configPath)) {
            return true;
        }

        return false;
    }

}

?>
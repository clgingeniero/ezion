<?php

class Janrain_Capture_Model_Session extends Mage_Core_Model_Session_Abstract {

    public function __construct() {
        $namespace = 'capture';
        $namespace .= '_' . (Mage::app()->getStore()->getWebsite()->getCode());

        $this->init($namespace);
        Mage::dispatchEvent('capture_session_init', array('capture_session' => $this));
    }

}
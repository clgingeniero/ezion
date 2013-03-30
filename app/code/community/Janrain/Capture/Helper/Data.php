<?php

class Janrain_Capture_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Returns whether the Enabled config variable is set to true
     *
     * @return bool
     */
    public function isCaptureEnabled() {
        if (strlen(Mage::getStoreConfig('capture/main/clientid')) > 1
            && strlen(Mage::getStoreConfig('capture/main/clientsecret')) > 1
            && strlen(Mage::getStoreConfig('capture/main/captureaddr')) > 1)
            return true;

        return false;
    }

    /**
     * Returns the first name from Capture user profile
     * 
     * @param $name
     *   The dot-delimited string of an attribute name to retrieve
     * @param $profile
     *   The result returned by load_user_entity
     *
     * @return string
     */
    public function fetchAttribute($name) {
        $capture_session = Mage::getSingleton('capture/session');
        $profile = $capture_session->getProfile();
        if (strpos($name, '.')) {
            $names = explode('.', $name);
            $value = $profile;
            foreach ($names as &$n) {
                if (isset($value[$n]))
                    $value = $value[$n];
                else
                    return false;
            }
            return $value;
        }
        else {
            return isset($profile[$name]) ? $profile[$name] : false;
        }
    }

    public function captureUiAddress() {
        return Mage::getStoreConfig('capture/optional/captureuiaddr') ? Mage::getStoreConfig('capture/optional/captureuiaddr') : Mage::getStoreConfig('capture/main/captureaddr');
    }

}
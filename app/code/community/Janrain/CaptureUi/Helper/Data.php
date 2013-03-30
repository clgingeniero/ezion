<?php

class Janrain_CaptureUi_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Returns the URL to the Signin screen
     *
     * @return string
     */
    public function signinUrl($method='') {
        $url = 'https://'
            . Mage::helper('capture')->captureUiAddress()
            . '/oauth/signin' .Mage::getStoreConfig('capture/optional/signinextension'). $method . '?response_type=code&flags=stay_in_window&recover_password_callback=CAPTURE.closeRecoverPassword&client_id='
            . Mage::getStoreConfig('capture/main/clientid') . '&redirect_uri='
            . urlencode(Mage::getUrl('capture/api/authenticate', array('_nosid' => true))) . '&xd_receiver='
            . urlencode(Mage::getUrl('capture/api/xdcomm', array('_nosid' => true)));
        return $url;
    }

    /**
     * Returns the URL to the Profile handler
     *
     * @return string
     */
    public function profileUrl($method = '', $callback = '') {
        $args = array('method' => $method, 'callback' => $callback);
        return Mage::getUrl('capture/api/profile', $args);
    }

    /**
     * Returns the Logout URL
     *
     * @return string
     */
    public function logoutUrl() {
        if (Mage::getStoreConfig('capture/optional/sso'))
            $url = 'javascript:CAPTURE.logout()';
        else
            $url = Mage::helper('customer')->getLogoutUrl();
        return $url;
    }

    /**
     * Returns the url of skin directory containing scripts and styles
     *
     * @return string
     */
    public function _baseSkin() {
        return Mage::getBaseUrl('skin') . 'frontend/janrain/captureui';
    }

}

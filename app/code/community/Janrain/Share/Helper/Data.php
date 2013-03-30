<?php

class Janrain_Share_Helper_Data extends Mage_Core_Helper_Abstract {

    private $providers = array(
        'Facebook' => 'facebook',
        'Google' => 'google',
        'LinkedIn' => 'linkedin',
        'MySpace' => 'myspace',
        'Twitter' => 'twitter',
        'Windows Live' => 'live_id',
        'Yahoo!' => 'yahoo',
        'AOL' => 'aol',
        'Blogger' => 'blogger',
        'Flickr' => 'flickr',
        'Hyves' => 'hyves',
        'Livejournal' => 'livejournal',
        'MyOpenID' => 'myopenid',
        'Netlog' => 'netlog',
        'OpenID' => 'openid',
        'Verisign' => 'verisign',
        'Wordpress' => 'wordpress',
        'PayPal' => 'paypal'
    );

    /**
     * Returns whether the Enabled config variable is set to true
     *
     * @return bool
     */
    public function isShareEnabled() {
        if (Mage::getStoreConfig('share/options/enable') == '1')
            return true;

        return false;
    }

    /**
     * Returns the url of skin directory containing scripts and styles
     *
     * @return string
     */
    public function _baseSkin() {
        return Mage::getBaseUrl('skin') . "frontend/janrain";
    }
	
    public function rpxRealmName() {
        $realm = Mage::getStoreConfig('share/vars/realm');
        $realm = str_replace(".rpxnow.com", "", $realm);
        return $realm;
	}

}

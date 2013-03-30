<?php

class Janrain_Capture_Helper_Apicall extends Mage_Core_Helper_Abstract {

    public function new_access_token($auth_code, $from_sso=false, $origin=false) {
        $command = "oauth/token";
        if ($from_sso && $origin)
            $redirect_uri = Mage::getUrl('capture/api/authenticate', array('_nosid' => true, 'from_sso' => 'true'))
                . (strpos(Mage::getUrl('capture/api/authenticate', array('_nosid' => true, 'from_sso' => 'true')), '?') ? '&' : '?')
                . 'origin=' . $origin;
        else
            $redirect_uri = Mage::getUrl('capture/api/authenticate', array('_nosid' => true));

        $arg_array = array('code' => $auth_code,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code',
            'client_id' => Mage::getStoreConfig('capture/main/clientid'),
            'client_secret' => Mage::getStoreConfig('capture/main/clientsecret'));

        $json_data = $this->api_call($command, $arg_array);
        if (!isset($json_data['stat']) || $json_data['stat'] != 'ok') {
            $error = (isset($json_data['error_description']) && $json_data['error_description']) ? $json_data['error_description'] : Mage::helper('capture')->__('Invalid Server Response');
            Mage::throwException($error);
        }
        else {
            $this->update_capture_session($json_data);
        }
    }

    public function load_user_entity($can_refresh = true) {
        $session = Mage::getSingleton('capture/session');
        if (!$session->getAccessToken())
            Mage::throwException(Mage::helper('capture')->__('No Access Token Found.'));

        $user_entity = NULL;
        $need_to_refresh = false;

        // Check if we need to refresh the access token
        if (time() >= $session->getExpirationTime())
            $need_to_refresh = true;
        else {
            $user_entity = $this->get_entity($session->getAccessToken());
            if (isset($user_entity['code']) && $user_entity['code'] == '414')
                $need_to_refresh = true;
        }

        // If necessary, refresh the access token and try to fetch the entity again.
        if ($need_to_refresh) {
            if ($can_refresh) {
                $this->refresh_access_token($session->getRefreshToken());
                $this->load_user_entity(false);
                return;
            }
        }

        $session->setProfile(isset($user_entity['result']) ? $user_entity['result'] : false);
    }

    private function get_entity($access_token) {
        return $this->api_call("entity", NULL, $access_token);
    }

    public function refresh_access_token($refresh_token) {
        $command = "oauth/token";
        $arg_array = $post_array = array('refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
            'client_id' => Mage::getStoreConfig('capture/main/clientid'),
            'client_secret' => Mage::getStoreConfig('capture/main/clientsecret'));

        $json_data = $this->api_call($command, $arg_array);

        $this->update_capture_session($json_data);
    }

    private function api_call($command, $arg_array = NULL, $access_token = NULL) {
        $url = 'https://' . Mage::getStoreConfig('capture/main/captureaddr') . '/' . $command;
        $http = new Varien_Http_Client($url, array('useragent' => 'Janrain-Capture-Magento'));
        $method = 'GET';

        $http->setHeaders(array("Accept-encoding" => "identity"));

        if (isset($access_token))
            $http->setHeaders("Authorization: OAuth $access_token");
        if (isset($arg_array) && is_array($arg_array)) {
            $method = 'POST';
            $http->setParameterPost($arg_array);
        }
        $result = $http->request($method);
        $json_data = Zend_Json::decode($result->getBody(), true);
        return $json_data;
    }

    private function update_capture_session($json_data) {
        $password_recover = (isset($json_data['transaction_state']['capture']['password_recover'])
            && $json_data['transaction_state']['capture']['password_recover'] == true) ? true : false;

        $session = Mage::getSingleton('capture/session');
        $session->setExpirationTime(time() + $json_data['expires_in'])
            ->setAccessToken($json_data['access_token'])
            ->setRefreshToken($json_data['refresh_token'])
            ->setPasswordRecover($password_recover);

        if (isset($json_data['transaction_state']['capture']['action']))
            $session->setAction($json_data['transaction_state']['capture']['action']);
    }

    public function clear_capture_session() {
        Mage::getSingleton('capture/session')
            ->setExpirationTime(null)
            ->setAccessToken(null)
            ->setRefreshToken(null);
    }
    
    public function get_engage_key() {
        $command = "settings/get";
        $arg_array = array('key' => 'rpx_key',
                'client_id' => Mage::getStoreConfig('capture/main/clientid'),
                'client_secret' => Mage::getStoreConfig('capture/main/clientsecret'));
        $json = $this->api_call($command, $arg_array);

        return $json['result'];
    }

}

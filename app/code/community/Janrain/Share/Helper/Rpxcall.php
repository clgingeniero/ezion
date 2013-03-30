<?php

class Janrain_Share_Helper_Rpxcall extends Mage_Core_Helper_Abstract {

    public function getShareApiKey() {
        return Mage::helper('capture/apicall')->get_engage_key();
    }

    public function rpxLookupSave() {
        try {
            $lookup_rp = $this->rpxLookupRpCall();

            Mage::getModel('core/config')
                ->saveConfig('share/vars/realm', $lookup_rp->realm)
                ->saveConfig('share/vars/adminurl', $lookup_rp->adminUrl)
                ->saveConfig('share/vars/socialpub', $lookup_rp->shareProviders);
            Mage::getConfig()->reinit();

            return true;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addWarning('Could not retrieve account info. Please try again');
        }

        return false;
    }

    public function rpxLookupRpCall() {
        $version = Mage::getConfig()->getModuleConfig("Janrain_Share")->version;

        $postParams = array();
        $postParams["apiKey"] = $this->getShareApiKey();
        $postParams["pluginName"] = "magento";
        $postParams["pluginVersion"] = $version;

        $result = "rpxLookupRpCall: no result";
        try {
            $result = $this->rpxPost("lookup_rp", $postParams);
        } catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }

        return $result;
    }

    private function rpxPost($method, $postParams) {

        $rpxbase = "https://rpxnow.com";

        if ($method == "auth_info") {
            $method_fragment = "api/v2/auth_info";
        }
        elseif ($method == "activity") {
            $method_fragment = "api/v2/activity";
        }
        elseif ($method == "lookup_rp") {
            $method_fragment = "plugin/lookup_rp";
        }
        else {
            throw Mage::exception('Mage_Core', "method [$method] not understood");
        }

        $url = "$rpxbase/$method_fragment";
        $method = 'POST';
        $postParams["format"] = 'json';

        return $this->rpxCall($url, $method, $postParams);
    }

    private function rpxCall($url, $method = 'GET', $postParams = null) {

        $result = "rpxCallUrl: no result yet";

        try {

            $http = new Varien_Http_Client($url);
            $http->setHeaders(array("Accept-encoding" => "identity"));
            if ($method == 'POST')
                $http->setParameterPost($postParams);
            $response = $http->request($method);

            $body = $response->getBody();

            try {
                $result = json_decode($body);
            } catch (Exception $e) {
                throw Mage::exception('Mage_Core', $e);
            }

            if ($result) {
                return $result;
            }
            else {
                throw Mage::exception('Mage_Core', "something went wrong");
            }
        } catch (Exception $e) {
            throw Mage::exception('Mage_Core', $e);
        }
    }

}

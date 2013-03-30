<?php

class Janrain_Share_Model_Observer {

    public function onConfigSave($observer) {
        if (Mage::getStoreConfig('share/vars/apikey') != Mage::getStoreConfig('share/options/apikey') || strlen(Mage::getStoreConfig('share/vars/appid')) < 1) {
            Mage::helper('share/rpxcall')->rpxLookupSave();
        }
    }

}
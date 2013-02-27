<?php

class Magalter_Config_Helper_Data extends Mage_Core_Helper_Abstract {
   
    public function getInstalledMagalterExtesions() {
        /* Get all installed Magalter extensions */
        $extensions = Mage::getConfig()->getNode('modules')->children();
        $installedMagalterExtensions = array();
        foreach ($extensions as $extensionCode => $extensionDescription) {
            if (strstr($extensionCode, 'Magalter_') === false) {
                continue;
            }
            $installedMagalterExtensions[$extensionCode] = $extensionDescription;
        }
        return $installedMagalterExtensions;
    }

    public function getInstalledFreeMagalter() {
        foreach ($this->getInstalledMagalterExtesions() as $code => $data) {
            if ($data->type == 'free') {
                if ($data->descend('active')->asArray() == 'true') {
                    if (!Mage::getStoreConfig("advanced/modules_disable_output/{$code}")) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getKeyWord($delimiter, $data) {

        if (!(strlen(Mage::getBaseUrl()) % $delimiter)) {
            return $data[0];
        }

        return $data[1];
    }
 
}
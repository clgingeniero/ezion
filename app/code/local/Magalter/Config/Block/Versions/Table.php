<?php

class Magalter_Config_Block_Versions_Table extends Mage_Core_Block_Template
{
     public function getExtensionsInfo()
     {
         $storeInfo = Mage::getModel('magalterconfig/storage')->load('magalterconfig_magalter_response', 'code');
         $extensionsInfo = array();
         if ($storeInfo->getId())
         {
             $decodedStoreInfo = Zend_Json::decode($storeInfo->getData('value'));
             if (isset($decodedStoreInfo['extensions']))
             {
                 $extensionsInfo = $decodedStoreInfo['extensions'];
             }
         }
         return $extensionsInfo;
     }
     
     public function getExtensionVersion($code)
     {
        $installedMagalterExtensions = Mage::helper('magalterconfig')->getInstalledMagalterExtesions();
        return isset($installedMagalterExtensions[$code]) ? $installedMagalterExtensions[$code]->version : NULL;
     }
}
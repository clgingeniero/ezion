<?php

class Magalter_Config_Model_Observer
{
    const SYNC_URL = "http://www.magalter.com/service/requests/index";
    const DAYS_BETWEEN_UPDATES = 5;

    public function connectToWebservice()
    {        
        if(!Mage::getSingleton('admin/session')->isLoggedIn()) { 
          return;
        }
        
        $nextUpdateTime = Mage::getModel('magalterconfig/storage')
                ->load('magalterconfig_next_update_time', 'code')
                ->getData('value');
         
        /* Check if update time comes */
        if ($nextUpdateTime && $nextUpdateTime > Mage::getModel('core/date')->gmtTimestamp())
            return;
        
        /* Check if table exists */
        $resource = Mage::getSingleton('core/resource');        
        if($resource->getConnection('core_read')->showTableStatus($resource->getTableName('magalterconfig/storage')) === false) 
            return;
        
        /* Get all installed Magalter extensions versions */
        $extensions = Mage::getConfig()->getNode('modules')->children();
        $installedExtensionsVersions = array();
        foreach ($extensions as $extensionName => $extensionDescription) {
            if (strstr($extensionName, 'Magalter_') === false) {
                continue;
            }
            $installedExtensionsVersions[] = $extensionName . '-' . $extensionDescription->version;
        }
        
        if (!sizeof($installedExtensionsVersions))
            return;
        
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        /* Request params building */
        $params = 'website=' . $baseUrl . '&extensions=' . implode(',', $installedExtensionsVersions);

        try {
            $ch = curl_init();
            /* curl post request with 5 seconds timeout, string will be returned */
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, self::SYNC_URL);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($ch); //execute and get the results
            curl_close($ch);
            
            $oneDay = 60 * 60 * 24;            
            /* Result can be false because of exception or if curl_exec returns false */
            if ($result) {                
                /* Check if JSON is correct. If not, exception by Zend_Json will be thrown */
                $decoded = Zend_Json::decode($result);   
                if(Mage::getStoreConfig('magalterconfig/configuration/subscribe')) {                                   
                    if (is_array($decoded) && isset($decoded['news']) && is_array($decoded['news'])) {
                        $update = array();
                        foreach ($decoded['news'] as $news) {
                            if (!is_array($news) || !isset($news['url'])) {
                                continue;
                            }
                            $news['date_added'] = gmdate('Y-m-d H:i:s');
                            $news['url'] = base64_decode($news['url']);
                            // make sure the order of news is correct
                            array_unshift($update, $news);
                        }
                        if (!empty($update)) {     
                            Mage::getModel('adminnotification/inbox')->parse($update);
                        }
                    }
                }
              
                Mage::getModel('magalterconfig/storage')
                        ->load('magalterconfig_magalter_response', 'code')
                        ->setData('code', 'magalterconfig_magalter_response')
                        ->setData('value', $result)
                        ->save();

                /* If all is fine then next update is needed to be processed in 7 days */
                Mage::getModel('magalterconfig/storage')
                        ->load('magalterconfig_next_update_time', 'code')
                        ->setData('code', 'magalterconfig_next_update_time')
                        ->setData('value', Mage::getModel('core/date')->gmtTimestamp() + self::DAYS_BETWEEN_UPDATES * $oneDay)
                        ->save();
            } else {
                throw new Exception('Incorrect result');
            }
        } catch (Exception $ex) {
            /* If exception found then set next update day to the next day, except 7 days as usual */
            Mage::getModel('magalterconfig/storage')
                    ->load('magalterconfig_next_update_time', 'code')
                    ->setData('code', 'magalterconfig_next_update_time')
                    ->setData('value', Mage::getModel('core/date')->gmtTimestamp() + $oneDay)
                    ->save();
        }
    }

    public function prepareConfiguration($observer)
    {         
        if (Mage::helper('magalterconfig')->getInstalledFreeMagalter()) {
            if (Mage::app()->getRequest()->getModuleName() == 'contacts') {                
                $keyWord = Mage::helper('magalterconfig')->getKeyWord(4, array('Magento customization', 'Magento extensions'));                
                $layout = $observer->getLayout();
                if ($layout->getBlock('footer')) {
                    $layout
                        ->getBlock('footer')
                        ->setCacheLifetime(null)
                        ->insert($layout->createBlock(Mage::getBlockSingleton('magalterconfig/prepare')->setLinksIdentity(Mage::helper('magalterconfig')->__($keyWord)), 'magalter_config_prepare'), '', true);
                }
            }
            if (Mage::app()->getRequest()->getControllerName() == 'seo_sitemap') {                
                $keyWord = Mage::helper('magalterconfig')->getKeyWord(2, array('Magento plugins', 'Magento modules'));
                $layout = $observer->getLayout();
                if ($layout->getBlock('footer')) {
                    $layout
                        ->getBlock('footer')
                        ->setCacheLifetime(null)
                        ->insert($layout->createBlock(Mage::getBlockSingleton('magalterconfig/prepare')->setLinksIdentity(Mage::helper('magalterconfig')->__($keyWord)), 'magalter_config_prepare'), '', true);
                }
            }
        }
    }

}
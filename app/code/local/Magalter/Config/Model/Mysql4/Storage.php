<?php

class Magalter_Config_Model_Mysql4_Storage extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('magalterconfig/storage', 'id');
    }
}

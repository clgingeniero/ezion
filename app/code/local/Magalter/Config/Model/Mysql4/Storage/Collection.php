<?php

class Magalter_Config_Model_Mysql4_Storage_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('magalterconfig/storage');
    }
}
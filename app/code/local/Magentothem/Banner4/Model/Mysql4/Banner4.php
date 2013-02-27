<?php

class Magentothem_Banner4_Model_Mysql4_Banner4 extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the banner4_id refers to the key field in your database table.
        $this->_init('banner4/banner4', 'banner4_id');
    }
}
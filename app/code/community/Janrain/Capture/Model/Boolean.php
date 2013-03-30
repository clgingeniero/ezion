<?php

class Janrain_Capture_Model_Boolean {

    public function toOptionArray() {
        return array(
            array('value' => 'true', 'label' => Mage::helper('capture')->__('True')),
            array('value' => 'false', 'label'=> Mage::helper('capture')->__('False'))
        );
    }
}
 
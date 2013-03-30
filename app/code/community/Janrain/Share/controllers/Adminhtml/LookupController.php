<?php

class Janrain_Share_Adminhtml_LookupController extends Mage_Adminhtml_Controller_action {

    public function rpAction() {
        if (Mage::helper('share/rpxcall')->rpxLookupSave())
            Mage::getSingleton('core/session')->addSuccess('Share account data successfully retrieved');
        else
            Mage::getSingleton('core/session')->addError('Share account data could not be updated');
        $this->_redirect('adminhtml/system_config/edit/section/share');
    }

}

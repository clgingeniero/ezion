<?php


class Magalter_Fastlogin_Block_LogoutLink extends Mage_Core_Block_Template
{

     protected function _toHtml() {
	 
	 
		$this->setTemplate('magalter_fastlogin/logoutlink.phtml');
		return parent::_toHtml();
	 
	 
	 }

        


}
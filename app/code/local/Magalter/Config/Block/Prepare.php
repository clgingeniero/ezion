<?php

class Magalter_Config_Block_Prepare extends Mage_Core_Block_Template
{
      protected function _toHtml() {                    
          return "<ul><li class = 'last' title='{$this->getLinksIdentity()}'><a href = 'http://www.magalter.com'>{$this->getLinksIdentity()}</a></li></ul>";
      }
}
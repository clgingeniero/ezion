<?php

class Magalter_Config_Block_Versions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
     public function render(Varien_Data_Form_Element_Abstract $element) 
     {
         $html = $this->_getHeaderHtml($element);
         $html .= $this->getLayout()->createBlock('magalterconfig/versions_table')->setTemplate('magalter_config/versions/table.phtml')->toHtml();
         $html .= $this->_getFooterHtml($element);
         return $html;
     }
}
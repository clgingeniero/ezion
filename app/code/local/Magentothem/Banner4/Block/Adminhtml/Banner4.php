<?php
/*------------------------------------------------------------------------
# Websites: http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner4_Block_Adminhtml_Banner4 extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_banner4';
    $this->_blockGroup = 'banner4';
    $this->_headerText = Mage::helper('banner4')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('banner4')->__('Add Item');
    parent::__construct();
  }
}
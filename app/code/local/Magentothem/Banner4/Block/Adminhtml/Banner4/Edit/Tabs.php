<?php
/*------------------------------------------------------------------------
# Websites: http://www.plazathemes.com
-------------------------------------------------------------------------*/ 
class Magentothem_Banner4_Block_Adminhtml_Banner4_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('banner4_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('banner4')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('banner4')->__('Item Information'),
          'title'     => Mage::helper('banner4')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('banner4/adminhtml_banner4_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
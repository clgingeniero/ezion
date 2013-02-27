<?php
/*------------------------------------------------------------------------
# Websites: http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner4_Block_Adminhtml_Banner4_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'banner4';
        $this->_controller = 'adminhtml_banner4';
        
        $this->_updateButton('save', 'label', Mage::helper('banner4')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('banner4')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('banner4_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'banner4_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'banner4_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('banner4_data') && Mage::registry('banner4_data')->getId() ) {
            return Mage::helper('banner4')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('banner4_data')->getTitle()));
        } else {
            return Mage::helper('banner4')->__('Add Item');
        }
    }
}
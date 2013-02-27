<?php
/*------------------------------------------------------------------------
# Websites: http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner4_Model_Config_Mode
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'fadeZoom', 'label'=>Mage::helper('adminhtml')->__('FadeZoom')),
            array('value'=>'scrollHorz', 'label'=>Mage::helper('adminhtml')->__('ScrollHorz')),
            array('value'=>'scrollVert', 'label'=>Mage::helper('adminhtml')->__('ScrollVert')),
            array('value'=>'shuffle', 'label'=>Mage::helper('adminhtml')->__('Shuffle')),
            array('value'=>'slideX', 'label'=>Mage::helper('adminhtml')->__('SlideX')),
            array('value'=>'slideY', 'label'=>Mage::helper('adminhtml')->__('SlideY')),
            array('value'=>'toss', 'label'=>Mage::helper('adminhtml')->__('Toss'))
        );
    }

}

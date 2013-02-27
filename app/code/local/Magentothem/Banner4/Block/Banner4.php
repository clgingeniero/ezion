<?php
/*------------------------------------------------------------------------
# Websites: http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Banner4_Block_Banner4 extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getBanner4()     
     { 
        if (!$this->hasData('banner4')) {
            $this->setData('banner4', Mage::registry('banner4'));
        }
        return $this->getData('banner4');
        
    }
	public function getDataBanner4()
    {
    	$resource = Mage::getSingleton('core/resource');
		$read= $resource->getConnection('core_read');
		$slideTable = $resource->getTableName('banner4');	
		$select = $read->select()
		   ->from($slideTable,array('banner4_id','title','link','description','image','status'))
		   ->where('status=?',1);
		$slide = $read->fetchAll($select);	
		return 	$slide;			
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('banner4');
		if (isset($config['banner4_config']) ) {
			$value = $config['banner4_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}
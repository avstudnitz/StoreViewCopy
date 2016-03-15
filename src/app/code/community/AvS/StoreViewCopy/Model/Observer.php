<?php
/**
 * General Observer
 *
 * @category   AvS
 * @package    AvS_StoreViewCopy
 * @author     Andreas von Studnitz <avs@avs-webentwicklung.de>
 */
class AvS_StoreViewCopy_Model_Observer
{

	/**
	 * add Massaction Option to Productgrid
	 *
	 * @param $observer Varien_Event
	 */
	public function addMassactionToProductGrid($observer)
	{
		$block = $observer->getBlock();
		if($block->getNameInLayout() === 'product.grid' && $block instanceof Mage_Adminhtml_Block_Widget_Grid){

            $block->getMassactionBlock()->addItem('copy_storeview', array(
                 'label'=> Mage::helper('storeviewcopy')->__('Copy StoreView Attributes'),
                 'url'  => $block->getUrl('*/storeviewcopy/form'),
            ));
		}
	}
}

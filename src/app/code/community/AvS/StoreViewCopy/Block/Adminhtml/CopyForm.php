<?php
/**
 * Admin Products Grid Block
 *
 * @category   AvS
 * @package    AvS_StoreViewCopy
 * @author     Andreas von Studnitz <avs@avs-webentwicklung.de>
 */
class AvS_StoreViewCopy_Block_Adminhtml_CopyForm extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('storeviewcopy/form.phtml');
        $this->setTitle('Copy StoreView Attributes');
    }

    protected function _prepareLayout()
    {
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Copy'),
                    'onclick'   => 'storeviewcopyForm.submit()',
                    'class'     => 'save',
                ))
        );
        return parent::_prepareLayout();
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    public function getStores() {

        return Mage::getModel('core/store')->getCollection()->setOrder('website_id', 'ASC')->setOrder('name', 'ASC');
    }

    public function getProductIds() {

        return Mage::app()->getRequest()->getParam('product');
    }

    public function getProducts() {
        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addAttributeToSelect('name');
        $products->addAttributeToFilter('entity_id', array('in' => $this->getProductIds()));

        return $products;
    }

    public function getFullStoreName($store) {

        $name = array();
        $name[] = $store->getWebsite()->getName();
        $name[] = $store->getGroup()->getName();
        $name[] = $store->getName();

        return implode(' &raquo; ', $name);
    }
}

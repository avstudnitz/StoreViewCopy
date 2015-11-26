<?php

/**
 * Controller for copying Store View Attributes for Products
 *
 * @category   AvS
 * @package    AvS_StoreViewCopy
 * @author     Andreas von Studnitz <avs@avs-webentwicklung.de>
 */
class AvS_StoreViewCopy_StoreviewcopyController extends Mage_Adminhtml_Controller_Action {

    /**
     * Basic action: reset form
     */
    public function formAction() {
        
        if (!is_array($this->getRequest()->getParam('product')) || sizeof($this->getRequest()->getParam('product')) == 0)  {

            $this->_getSession()->addError($this->__('Please select products.'));
            $this->_redirectReferer();
            return;
        }

        $this->loadLayout()
                ->_setActiveMenu('catalog/product')
                ->_addBreadcrumb(Mage::helper('storeviewcopy')->__('Copy StoreView Attributes'), Mage::helper('storeviewcopy')->__('Copy StoreView Attributes'))
                ->_addContent($this->getLayout()->createBlock('storeviewcopy/adminhtml_copyForm'))
                ->renderLayout();
    }

    /**
     * Basic action: reset save action
     */
    public function saveAction() {

        if ($this->getRequest()->isPost()) {

            $targetStores = array();
            foreach(Mage::getModel('core/store')->getCollection() as $store) {

                if ($this->getRequest()->getParam('target_store_' . $store->getId()) == 1) {
                    $targetStores[] = $store->getId();
                }
            }

            if (!$this->getRequest()->getParam('source_store') || empty($targetStores)) {

                $this->_getSession()->addError($this->__('Please select source and target store view.'));
            } elseif (in_array($this->getRequest()->getParam('source_store'), $targetStores)) {

                $this->_getSession()->addError($this->__('Source and Target StoreView must be different.'));
            } elseif (!$this->getRequest()->getParam('products')) {

                $this->_getSession()->addError($this->__('Please select products.'));
            } else {

                foreach(explode(',', $this->getRequest()->getParam('products')) as $productId) {

                    foreach($targetStores as $targetStoreId) {

                        try {

                            $numberCopiedAttributes = $this->_copyAttributes($productId, $this->getRequest()->getParam('source_store'), $targetStoreId);
                            $this->_getSession()->addSuccess($this->__('%s attributes of product %s have been copied.', $numberCopiedAttributes, $productId));

                        } catch(Exception $e) {

                            $this->_getSession()->addError($this->__('An error occurred with product %s: %s', $productId, $e->getMessage()));
                        }
                    }
                }

            }
        }

        $this->_redirect('adminhtml/catalog_product');
    }

    protected function _copyAttributes($productId, $sourceStoreId, $targetStoreId) {

        $updatedAttributes = 0;

        $sourceProduct = Mage::getModel('catalog/product')->setStoreId($sourceStoreId)->load($productId);
        $targetProduct = Mage::getModel('catalog/product')->setStoreId($targetStoreId)->load($productId);
        $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($sourceProduct->getAttributeSetId());
        foreach($this->_getAttributesFromSet($attributeSet) as $attribute) {

            if ($this->_updateAttribute($sourceProduct, $targetProduct, $attribute->getAttributeCode())) {

                $updatedAttributes++;
            }
        }

        return $updatedAttributes;
    }
   
    protected function _getAttributesFromSet($attributeSet) {

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('is_global', array('in' => array(0, 2)))
            ->setAttributeSetFilter($attributeSet->getId())
            ->load();

        return $attributes;
    }

    protected function _updateAttribute($sourceProduct, $targetProduct, $attributeCode) {

        if ($sourceProduct->getData($attributeCode) != $targetProduct->getData($attributeCode)) {

            //echo $attributeCode . ': ' . $sourceProduct->getData($attributeCode) . ' : ' . $targetProduct->getData($attributeCode) . '<br />';
            $targetProduct->setData($attributeCode, $sourceProduct->getData($attributeCode));
            $targetProduct->getResource()->saveAttribute($targetProduct, $attributeCode);
            return true;
        }

        return false;
    }
}

<?php
class Lotusbreath_OneStepCheckout_Block_Checkout_Links extends  Mage_Checkout_Block_Links {

    public function addCheckoutLink()
    {

        if (!Mage::getStoreConfig('lotusbreath_onestepcheckout/general/enabled')) {
            return parent::addCheckoutLink();
        }


        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Lotusbreath_OneStepCheckout')) {
            $text = $this->__('Checkout');
            $parentBlock->addLink(
                $text, 'lotusbreath_onestepcheckout', $text,
                true, array('_secure' => true), 60, null,
                'class="top-link-checkout"'
            );
        }
        return $this;
    }
}
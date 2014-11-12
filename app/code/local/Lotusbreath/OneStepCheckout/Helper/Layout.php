<?php
class Lotusbreath_OneStepCheckout_Helper_Layout extends Mage_Checkout_Helper_Data{

    public function switchTemplate(){
        $layout = Mage::getStoreConfig('lotusbreath_onestepcheckout/layout/layout');

        switch ($layout){
            case '2cols':
                return 'lotusbreath/onestepcheckout/onepage.phtml';
                break;
            case '3cols':
                return 'lotusbreath/onestepcheckout/onepage_3columns.phtml';
                break;

            default:
                return 'lotusbreath/onestepcheckout/onepage.phtml';
                break;
        }
    }
}
<?php
/*
 * @dev by Lotusbreath team : http://www.lotusbreath.com
 */
class Lotusbreath_OneStepCheckout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage {

    public function initCheckout()
    {
        $checkout = $this->getCheckout();
        $customerSession = $this->getCustomerSession();

        /**
         * Reset multishipping flag before any manipulations with quote address
         * addAddress method for quote object related on this flag
         */
        if ($this->getQuote()->getIsMultiShipping()) {
            $this->getQuote()->setIsMultiShipping(false);
            $this->getQuote()->save();
        }

        /*
        * want to load the correct customer information by assigning to address
        * instead of just loading from sales/quote_address
        */
        $customer = $customerSession->getCustomer();
        if ($customer) {
            $this->getQuote()->assignCustomer($customer);
        }
        return $this;
    }

    public function saveOnlyOneShippingMethod(){
        $result = null;
        $this->getQuote()->getShippingAddress()->save()->collectShippingRates();
        $groupRates = $this->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
        if(count($groupRates) == 1){
            $_sole = count($groupRates) == 1;
            $_rates = $groupRates[key($groupRates)];
            $_sole = $_sole && count($_rates) == 1;
            if ($_sole){
                $result = $this->saveShippingMethod(reset($_rates)->getCode());
                if (!$result){
                    Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                        array('request' => Mage::app()->getRequest(),
                            'quote' => $this->getQuote()));
                }

                $this->getQuote()->collectTotals()->save();
            }


        }

        return $result;
    }

    public  function customerEmailExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }
}
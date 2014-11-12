<?php
/**
 * @lotusbreath luuvantrung@gmail.com
 * Class Mage_Checkout_Block_Onepage_Shipping_Method_Available
 *
 */
class Lotusbreath_OneStepCheckout_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function getShippingRates()
    {

        if (empty($this->_rates)) {
            $countryCode = Mage::getStoreConfig('general/country/default');
            if ($countryCode && !$this->getAddress()->getCountryId())
                $this->getAddress()->setCountryId($countryCode);
            $isUpdateShippingRates = true;
            $theAddress = $this->getAddress();

            if (Mage::getStoreConfig('lotusbreath_onestepcheckout/general/loadshippingrateswhenfillall')){
                $relatedLocationFields = Mage::getStoreConfig("lotusbreath_onestepcheckout/general/location_fields");

                if ($relatedLocationFields){
                    $relatedLocationFields = explode(',',$relatedLocationFields);
                }


                foreach ($relatedLocationFields as $localeField){
                     if ($localeField){
                         if ($localeField == 'region_id' && !Mage::getStoreConfig('general/region/display_all')){
                            continue;
                         }
                         $value = $theAddress->getData($localeField);
                         //echo $localeField . '-' . $value . '|';
                         if (empty($value)){
                             $isUpdateShippingRates = false;
                         }
                     }

                }
            }

            $groups = false;
            if ($isUpdateShippingRates){
                $this->getAddress()->save()->setCollectShippingRates(true);
                $this->getAddress()->collectShippingRates()->save();
                $groups = $this->getAddress()->getGroupedAllShippingRates();

                if(count($groups) == 1){
                    $_sole = count($groups) == 1;
                    $_rates = $groups[key($groups)];
                    $_sole = $_sole && count($_rates) == 1;
                    if ($_sole)

                        $result = Mage::getSingleton('lotusbreath_onestepcheckout/type_onepage')->saveShippingMethod(reset($_rates)->getCode());

                        if (!$result){
                            Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                                array('request' => Mage::app()->getRequest(),
                                    'quote' => $this->getQuote()));
                        }
                        $this->getQuote()->collectTotals()->save();


                }

                //echo count($groups);exit;
            }


            /*
            if (!empty($groups)) {
                $ratesFilter = new Varien_Filter_Object_Grid();
                $ratesFilter->addFilter(Mage::app()->getStore()->getPriceFilter(), 'price');

                foreach ($groups as $code => $groupItems) {
                    $groups[$code] = $ratesFilter->filter($groupItems);
                }
            }
            */
            return $this->_rates = $groups;
        }else{
            echo 1;
        }

        return $this->_rates;
    }

}
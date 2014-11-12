<?php
class Lotusbreath_OneStepCheckout_Model_Adminhtml_System_Config_Source_Locationfields {
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('None choose') ),
            array('value' => 'country_id', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('Country') ),
            array('value' => 'postcode', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('Post code/Zip code') ),
            array('value' => 'region_id', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('Region') ),
            array('value' => 'city', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('City') ),
        );
    }
}

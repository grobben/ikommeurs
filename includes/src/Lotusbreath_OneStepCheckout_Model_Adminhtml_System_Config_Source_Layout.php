<?php
class Lotusbreath_OneStepCheckout_Model_Adminhtml_System_Config_Source_Layout {
    public function toOptionArray()
    {
        return array(
            array('value' => '2cols', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('2 Columns') ),
            array('value' => '3cols', 'label' => Mage::helper('lotusbreath_onestepcheckout')->__('3 Columns') ),
        );
    }
}

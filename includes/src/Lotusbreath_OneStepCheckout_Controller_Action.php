<?php
/*
 * @dev by Lotusbreath team : http://www.lotusbreath.com
 */
class Lotusbreath_OneStepCheckout_Controller_Action extends Mage_Core_Controller_Front_Action {

    public function preDispatch() {
        if(!Mage::getStoreConfig('lotusbreath_onestepcheckout/general/enabled')){
            $this->_redirect(Mage::getUrl('checkout/onepage/index'));
        }

        /**
         * Disable some event for optimization
         */
        if(!Mage::getStoreConfig('lotusbreath_onestepcheckout/speedoptimizer/disablerssobserver')){
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'sales_order_save_after');
            if ($eventConfig->observers->notifystock->class == 'rss/observer')
                $eventConfig->observers->notifystock->type = 'disabled';
            if ($eventConfig->observers->ordernew->class == 'rss/observer')
                $eventConfig->observers->ordernew->type = 'disabled';
        }

        if(!Mage::getStoreConfig('lotusbreath_onestepcheckout/speedoptimizer/disablevisitorlog')){
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'controller_action_predispatch');
            $eventConfig->observers->log->type = 'disabled';
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'controller_action_postdispatch');
            $eventConfig->observers->log->type = 'disabled';
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'sales_quote_save_after');
            $eventConfig->observers->log->type = 'disabled';
            $eventConfig = Mage::getConfig()->getEventConfig('frontend', 'checkout_quote_destroy');
            $eventConfig->observers->log->type = 'disabled';
        }
        parent::preDispatch();
        if (!$this->getRequest()->getParam('allow_gift_messages')){
            $this->getRequest()->setParam('giftmessage', false);
        }
        return $this;
        
    }

}
?>

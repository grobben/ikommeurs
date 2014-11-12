<?php
/*
 * @dev by Lotusbreath team : http://www.lotusbreath.com
 */
class Lotusbreath_OneStepCheckout_Model_Observer {

    const CONFIG_ENABLE_MODULE = 'lotusbreath_onestepcheckout/general/enabled';

    public function addHistoryComment($data)
    {
        if(Mage::getStoreConfig(self::CONFIG_ENABLE_MODULE)){
            if(Mage::getStoreConfig('lotusbreath_onestepcheckout/general/allowsubscribe')){
                $comment	= Mage::getSingleton('customer/session')->getOrderCustomerComment();
                $comment	= trim($comment);
                if (!empty($comment))


                    if(!empty($data['order'])){
                        $order = $data['order'];
                        $order->addStatusHistoryComment($comment)->setIsVisibleOnFront(true)->setIsCustomerNotified(false);
                        $order->setCustomerComment($comment);
                        $order->setCustomerNoteNotify(true);
                        $order->setCustomerNote($comment);
                    }

            }
        }
        return $this;
    }
    public function redirectToOnestepcheckout($observer){
        $isRedirectAfterAddToCart = Mage::getStoreConfig('lotusbreath_onestepcheckout/general/redirect_to_afteraddtocart');
        if ($isRedirectAfterAddToCart){
            $url = Mage::getUrl('lotusbreath_onestepcheckout');
            $observer->getEvent()->getResponse()->setRedirect($url);
            Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
        }

    }
}
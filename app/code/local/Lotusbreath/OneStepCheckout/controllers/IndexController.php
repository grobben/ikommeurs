<?php
/**
 * Lotusbreath -  One step checkout controller
 * Class Lotusbreath_OneStepCheckout_IndexController
 */
class Lotusbreath_OneStepCheckout_IndexController extends Lotusbreath_OneStepCheckout_Controller_Action
{

    protected $_isLoadedLayout = false;
    protected $_isRequireUpdateQuote = false;
    protected function getOnepage()
    {
        return Mage::getSingleton('lotusbreath_onestepcheckout/type_onepage');
    }

    public function indexAction()
    {

        Mage::dispatchEvent('controller_action_predispatch_onestepcheckout_index_index',
            array('request' => $this->getRequest(),
                'quote' => $this->getOnepage()->getQuote()));

        if (!Mage::getStoreConfig('lotusbreath_onestepcheckout/general/enabled')) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));

        $this->getOnepage()->initCheckout();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($customerAddressId = $quote->getCustomerId()) {
            $defaultShippingA = $quote->getCustomer()->getPrimaryShippingAddress();
            if ($defaultShippingA
                && $countryCode = $defaultShippingA->getCountryId()
            )
                $quote->getShippingAddress()->setCountryId($countryCode)->save();
            $saveShippingMethodResult = $this->getOnepage()->saveOnlyOneShippingMethod();

        } else {
            //clear country
            $countryCode = Mage::getStoreConfig('lotusbreath_onestepcheckout/general/defaultcountry');
            if ($countryCode) {
                $this->getOnepage()->getQuote()->getShippingAddress()->setCountryId($countryCode)->save();
                $this->getOnepage()->getQuote()->getBillingAddress()->setCountryId($countryCode)->save();
                //$saveShippingMethodResult = $this->getOnepage()->saveOnlyOneShippingMethod();
            }

        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }


    public function saveStepAction()
    {
        $step = $this->getRequest()->getParam('step', '');
        $updateItems = array();
        $htmlUpdates = array();
        switch ($step) {
            case 'shipping_method':
                $saveShippingMethodResult = $this->_saveShippingMethod();
                $this->_savePayment();
                $htmlUpdates['review_partial'] = $this->getReviewHtml();
                $htmlUpdates['payment_partial'] = $this->getPaymentHtml();
                $updateItems[] = 'review_partial';
                $updateItems[] = 'payment_partial';
                break;

            case 'payment_method':
                $savePaymentMethod = $this->_savePayment();
                $htmlUpdates['review_partial'] = $this->getReviewHtml();
                $updateItems[] = 'review_partial';
                break;

            case 'update_location_billing' :
                $this->_savePayment();
                $this->_updateBillingAddress();

                $htmlUpdates['payment_partial'] = $this->getPaymentHtml();
                $updateItems[] = 'payment_partial';

                $htmlUpdates['review_partial'] = $this->getReviewHtml();
                $updateItems[] = 'review_partial';
                break;
            case  'update_location':
                $this->_saveShippingMethod();
                $this->_updateShippingAddress();

                $htmlUpdates['shipping_partial'] = $this->getShippingMehodHtml();
                $updateItems[] = 'shipping_partial';

                $htmlUpdates['review_partial'] = $this->getReviewHtml();
                $updateItems[] = 'review_partial';

                break;

            case 'update_location_billing_shipping':
                $this->_saveShippingMethod();
                $this->_updateBillingAddress();
                $this->_updateShippingAddress();

                $htmlUpdates['shipping_partial'] = $this->getShippingMehodHtml();
                $updateItems[] = 'shipping_partial';

                $htmlUpdates['payment_partial'] = $this->getPaymentHtml();
                $updateItems[] = 'payment_partial';

                $htmlUpdates['review_partial'] = $this->getReviewHtml();
                $updateItems[] = 'review_partial';

                break;
            default :
                return;
        }
        $return = array(

            'update_items' => $updateItems,
            'htmlUpdates' => $htmlUpdates
        );
        echo json_encode($return);
    }

    public function savePostAction()
    {
        $helper = Mage::helper('checkout');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $updateItems = array("review_partial");
        if (!Mage::helper('customer')->isLoggedIn()) {
            if ($helper->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
            }
            if (!empty($_POST["billing"]["create_new_account"]) || !$helper->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
            }
        }

        //}
        $saveBillingResult = $this->_saveBillingAddress();
        $usingCase = isset($_POST["billing"]['use_for_shipping']) ? (int)$_POST["billing"]['use_for_shipping'] : 0;
        $saveShippingResult = null;
        $saveShippingMethodResult = null;
        if (!$usingCase)
            $saveShippingResult = $this->_saveShippingAddress();
        //$_shippingRateGroups =
        $saveOrderReady = true;
        if (!$this->getRequest()->getPost('shipping_method', '')) {
            $saveOrderReady = false;
        }
        if (empty($saveShippingResult['error'])) {
            if (!$this->getRequest()->getPost('shipping_method', '')) {
                $saveShippingMethodResult = $this->getOnepage()->saveOnlyOneShippingMethod();
            } else {
                $saveShippingMethodResult = $this->_saveShippingMethod();
            }
        }
        //if (!empty($saveShippingMethodResult['error'])){
        $updateItems[] = "shipping_partial";
        //}
        $updateItems[] = "review_partial";
        $saveOrderResult = null;
        $savePaymentResult = null;

        if (!isset($saveBillingResult['error'])
            && !isset($saveShippingMethodResult['error'])
            && !isset($savePaymentResult['error'])
        ) {
            $savePaymentResult = $this->_savePayment();

            if (!empty($savePaymentResult['redirect'])){
                //do not save order
                if ($data = $this->getRequest()->getPost('payment', false)) {
                    $this->getOnepage()->getQuote()->getPayment()->importData($data);
                }
                $this->_updateQuote();
            }else{
                if (!isset($savePaymentResult['error']) && $saveOrderReady){
                    $this->_updateQuote();
                    $saveOrderResult = $this->_saveOrder();
                }
            }



        }
        $this->loadLayout('lotusbreath_onestepcheckout_index_index');
        $return = array(
            'results' => array(
                'billing' => $saveBillingResult,
                'shipping_method' => $saveShippingMethodResult,
                'payment' => $savePaymentResult,
                'save_order' => $saveOrderResult
            ),
            //'update_items' => array('shipping_partial', 'payment_partial', 'review_partial' ),
            'update_items' => $updateItems,
            'htmlUpdates' => array(
                'shipping_partial' => $this->getShippingMehodHtml(),
                'payment_partial' => $this->getPaymentHtml(),
                'review_partial' => $this->getReviewHtml(),
            )
        );
        echo json_encode($return);
    }


    protected function _updateBillingAddress()
    {
        $billingAddressId = $this->getRequest()->getPost('billing_address_id', null);
        $data = $this->getRequest()->getPost('billing', null);
        $isUseForShipping = !empty($data['use_for_shipping']) ? $data['use_for_shipping'] : null;
        //for billing
        if ($billingAddressId) {
            $this->getOnepage()->saveBilling(array('use_for_shipping' => $isUseForShipping), $billingAddressId);
        } else {
            $locationInfo = $this->getLocaleData($data);
            if ($locationInfo) {
                $this->getOnepage()->getQuote()->getBillingAddress()->addData($locationInfo)->save();
            }
        }
        $this->_savePayment();
    }

    protected function _updateShippingAddress()
    {
        $billingAddressId = $this->getRequest()->getPost('billing_address_id', null);
        $data = $this->getRequest()->getPost('billing', null);
        $isUseForShipping = !empty($data['use_for_shipping']) ? $data['use_for_shipping'] : null;
        $shippingAddressId = $isUseForShipping ? $billingAddressId : ($this->getRequest()->getPost('shipping_address_id', null));
        if ($shippingAddressId) {
            $this->getOnepage()->saveShipping(array('same_as_billing' => $isUseForShipping), $shippingAddressId);
            $this->getOnepage()->getQuote()->getShippingAddress()->save()->setCollectShippingRates(true);

        } else {

            if (empty($data['use_for_shipping'])) {
                $data = $this->getRequest()->getPost('shipping', null);
                $locationInfo = $this->getLocaleData($data);
                if ($locationInfo) {
                    $this->getOnepage()->getQuote()->getShippingAddress()->addData($locationInfo)->save()
                        ->setCollectShippingRates(true);
                }
            } else {
                $data = $this->getRequest()->getPost('billing', null);
                $locationInfo = $this->getLocaleData($data);

                if ($locationInfo) {
                    $this->getOnepage()->getQuote()->getShippingAddress()->addData($locationInfo)
                        ->save()->setCollectShippingRates(true);
                    //$saveShippingMethodResult = $this->getOnepage()->saveOnlyOneShippingMethod();
                    //$this->getOnepage()->getQuote()->collectTotals()->save();
                }
            }
        }
        $saveShippingMethodResult = $this->getOnepage()->saveOnlyOneShippingMethod();
        $this->_requireUpdateQuote();
        return $saveShippingMethodResult;
    }


    protected function getLocaleData($data)
    {
        $locationInfo = array();
        if ($data) {
            $locationInfo['country_id'] = !empty($data['country_id']) ? $data['country_id'] : null;
            $locationInfo['postcode'] = !empty($data['postcode']) ? $data['postcode'] : null;
            $locationInfo['region'] = !empty($data['region']) ? $data['region'] : null;;
            $locationInfo['region_id'] = !empty($data['region_id']) ? $data['region_id'] : null;
            $locationInfo['city'] = !empty($data['city']) ? $data['city'] : null;
        }
        return $locationInfo;

    }


    protected function _loadLayout(){
        $this->_updateQuote();
        if (!$this->_isLoadedLayout){
            $this->loadLayout('lotusbreath_onestepcheckout_index_index');
            $this->_isLoadedLayout = true;
        }
    }

    protected function getReviewHtml()
    {
        $this->getOnepage()->getQuote()->getTotalsCollectedFlag(false);
        $this->_requireUpdateQuote();
        $this->_loadLayout();
        if ($reviewBlock = $this->getLayout()->getBlock('checkout.onepage.review')) {
            return $reviewBlock->toHtml();
        }
        return null;
    }

    protected function getShippingMehodHtml()
    {
        $this->_loadLayout();
        if ($shippingMethodBlock = $this->getLayout()->getBlock('checkout.onepage.shipping_method')) {
            return $shippingMethodBlock->toHtml();
        }
        return null;
    }

    protected function getPaymentHtml()
    {
        $this->_loadLayout();
        if ($paymentMethodBlock = $this->getLayout()->getBlock('checkout.onepage.payment')) {
            return $paymentMethodBlock->toHtml();
        }
        return null;
    }




    /**
     * Save billing Address
     * @return mixed
     */
    protected function _saveBillingAddress()
    {
        $request = $this->getRequest();
        $billingData = $request->getPost('billing');
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
        if (isset($billingData['email'])) {
            $billingData['email'] = trim($billingData['email']);
        }
        $result = $this->getOnepage()->saveBilling($billingData, $customerAddressId);
        return $result;
    }

    protected function _saveShippingAddress()
    {
        $data = $this->getRequest()->getPost('shipping', array());
        if (Mage::getStoreConfig('lotusbreath_onestepcheckout/shippingaddress/useshortshippingaddress')) {
            $billingData = $this->getRequest()->getPost('billing', array());
            $billingAddressId = $this->getRequest()->getPost('billing_address_id', null);
            if ($billingAddressId) {
                $billingAddress = $this->getOnepage()->getQuote()->getBillingAddress();
                $relatedFields = array('firstname', 'lastname', 'company');
                $billingData = array();
                foreach ($relatedFields as $field) {
                    $billingData[$field] = $billingAddress->getData($field);
                }
            }
            $data = array_merge($billingData, $data);
        }

        if ($data) {
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
            return $result;
        }
        return null;


    }

    /**
     * save payment
     * @return mixed
     */
    protected function _savePayment()
    {
        $data = $this->getRequest()->getPost('payment');

        try {
            $result = $this->getOnepage()->savePayment($data);
            $this->_requireUpdateQuote();
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = 1;
            $result['message'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = 1;
            $result['message'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = 1;
            $result['message'] = $this->__('Unable to set Payment Method.');
        }
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }
        return $result;
    }

    /**
     * Save shipping method
     * @return mixed
     */
    protected function _saveShippingMethod($data = null)
    {
        if (!$data)
            $data = $this->getRequest()->getPost('shipping_method', '');


        if (empty($data)) {
            $result = $this->getOnepage()->saveOnlyOneShippingMethod();
        } else {
            $result = $this->getOnepage()->saveShippingMethod($data);
        }


        /*
        $result will have erro data if shipping method is empty
        */

        if (!$result) {

            Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                array('request' => $this->getRequest(),
                    'quote' => $this->getOnepage()->getQuote()));

        }

        $this->_requireUpdateQuote();
        return $result;
    }

    /**
     * Save order
     * @return mixed
     */
    protected function _saveOrder()
    {
        try {

            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }


            //save comment
            if (Mage::getStoreConfig('lotusbreath_onestepcheckout/general/allowcomment')) {
                Mage::getSingleton('customer/session')->setOrderCustomerComment($this->getRequest()->getPost('order_comment'));
            }
            $this->_subscribeNewsletter();

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;

        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            $result['success'] = false;
            $result['error'] = true;
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

        } catch (Exception $e) {
            Mage::logException($e);
            echo $e->getMessage();
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }
        return $result;
        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Login action
     *
     */
    public function loginAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $errorMessages = array();
        $success = false;
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');

            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                    $success = true;
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    //$session->addError($message);
                    $session->setUsername($login['username']);
                    $errorMessages[] = $message;
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $errorMessages[] = $this->__('Login and password are required.');
                //$session->addError($this->__('Login and password are required.'));
            }
        }
        echo json_encode(array(
            'success' => $success,
            'messages' => $errorMessages
        ));

    }


    public function applyCouponAction()
    {

        $this->_savePayment();
        $this->_saveShippingMethod();
        $saveCouponResult = array();
        $quote = $this->getOnepage()->getQuote();
        $couponCode = (string)$this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $quote->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $saveCouponResult['success'] = false;
            $saveCouponResult['message'] = Mage::helper('onestepcheckout')->__('Coupon code is required');
        }
        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode(strlen($couponCode) ? $couponCode : '');
            $this->_requireUpdateQuote();
            //->collectTotals()
            //->save();

            if (strlen($couponCode)) {
                if ($couponCode == $quote->getCouponCode()) {

                    $saveCouponResult['success'] = true;
                    $saveCouponResult['message'] = Mage::helper('checkout/cart')->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                } else {
                    $saveCouponResult['success'] = false;
                    $saveCouponResult['message'] = Mage::helper('checkout/cart')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                }
            } else {
                $saveCouponResult['success'] = true;
                $saveCouponResult['message'] = Mage::helper('checkout/cart')->__('Coupon code was canceled.');
            }
        } catch (Mage_Core_Exception $e) {
            //$this->_getSession()->addError($e->getMessage());
            $saveCouponResult['success'] = false;
            $saveCouponResult['message'] = $e->getMessage();

        } catch (Exception $e) {
            $saveCouponResult['success'] = false;
            $saveCouponResult['message'] = Mage::helper('checkout/cart')->__('Cannot apply the coupon code.');
            Mage::logException($e);
        }

        $return = array(
            'results' => $saveCouponResult,
            //'update_items' => array('shipping_partial', 'payment_partial', 'review_partial' ),
            'update_items' => array('review_partial', 'payment_partial', 'shipping_partial'),
            'htmlUpdates' => array(
                'shipping_partial' => $this->getShippingMehodHtml(),
                'review_partial' => $this->getReviewHtml(),
                'payment_partial' => $this->getPaymentHtml(),
            )
        );
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($return));
    }

    public function updateCartAction()
    {
        $this->_saveShippingMethod();
        $this->_savePayment();

        $checkoutSession = Mage::getSingleton('checkout/session');
        $cartData = $this->getRequest()->getParam('cart');
        if (is_array($cartData)) {
            $filter = new Zend_Filter_LocalizedToNormalized(
                array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );
            foreach ($cartData as $index => $data) {
                if (isset($data['qty'])) {
                    $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                }
            }
            $cart = Mage::getSingleton('checkout/cart');
            $cartData = $cart->suggestItemsQty($cartData);
            $cart->updateItems($cartData)
                ->save();
        }
        $checkoutSession->setCartWasUpdated(true);
        $this->_requireUpdateQuote();
        $return = array(
            'results' => true,
            'update_items' => array('review_partial', 'shipping_partial', 'payment_partial'),
            'htmlUpdates' => array(
                'review_partial' => $this->getReviewHtml(),
                'shipping_partial' => $this->getShippingMehodHtml(),
                'payment_partial' => $this->getPaymentHtml(),
            )
        );
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($return));

    }

    public function clearCartItemAction()
    {
        $id = (int)$this->getRequest()->getPost('id');
        if ($id) {
            $cart = Mage::getSingleton('checkout/cart');
            $checkoutSession = Mage::getSingleton('checkout/session');
            try {
                $cart->removeItem($id)
                    ->save();
                $checkoutSession->setCartWasUpdated(true);
                $this->_requireUpdateQuote();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }

        }

        if ($cart && $cart->getQuote()->getItemsCount() == 0) {
            $return = array(
                'results' => false,
                'cart_is_empty' => true,
            );
        } else {
            $return = array(
                'results' => true,
                'update_items' => array('review_partial', 'payment_partial', 'shipping_partial'),
                'htmlUpdates' => array(
                    'review_partial' => $this->getReviewHtml(),
                    'payment_partial' => $this->getPaymentHtml(),
                    'shipping_partial' => $this->getShippingMehodHtml()
                )
            );
        }

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($return));
    }

    protected function _subscribeNewsletter()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('newsletter')) {
            $customerSession = Mage::getSingleton('customer/session');

            if ($customerSession->isLoggedIn())
                $email = $customerSession->getCustomer()->getEmail();
            else {
                $bill_data = $this->getRequest()->getPost('billing');
                $email = $bill_data['email'];
            }

            try {
                if (!$customerSession->isLoggedIn() && Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1)
                    Mage::throwException($this->__('Sorry, subscription for guests is not allowed. Please <a href="%s">register</a>.', Mage::getUrl('customer/account/create/')));

                $ownerId = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email)->getId();

                if ($ownerId !== null && $ownerId != $customerSession->getId())
                    Mage::throwException($this->__('Sorry, you are trying to subscribe email assigned to another user.'));

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
            } catch (Mage_Core_Exception $e) {
            }
            catch (Exception $e) {
            }
        }
    }

    public function checkExistsEmailAction()
    {
        $email = $this->getRequest()->getParam('email', null);
        $response = array('success' => true, 'message' => '');
        if ($email) {
            if ($this->getOnepage()->customerEmailExists($email, Mage::app()->getWebsite()->getId())) {
                $response = array('success' => false, 'message' => '');
            } else {

            }
        }
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($response));

    }

    protected function _requireUpdateQuote()
    {
        $this->_isRequireUpdateQuote = true;
    }

    protected function _updateQuote(){
        if ($this->_isRequireUpdateQuote)
            $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
    }
}
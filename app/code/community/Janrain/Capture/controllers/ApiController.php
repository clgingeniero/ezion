<?php

require_once ("Mage/Customer/controllers/AccountController.php");

class Janrain_Capture_ApiController extends Mage_Customer_AccountController {

    protected $postAuth = 'postAuthRedirect';

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     *
     * This is a clone of the one in Mage_Customer_AccountController
     * with two added action names to the preg_match regex to prevent
     * redirects back to customer/account/login when using Engage
     * authentication links. Rather than calling parent::preDispatch()
     * we explicitly call Mage_Core_Controller_Front_Action to prevent the
     * original preg_match test from breaking our auth process.
     *
     */
    public function preDispatch() {

        Mage_Core_Controller_Front_Action::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        if (!preg_match('/^(tokenExpired|profile|recoverPassword|resendVerification|xdcomm|refresh|authenticate)/i', $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        }
        else {
            $this->_getSession()->setNoReferer(true);
        }
    }

    public function indexAction() {
        $this->_redirect('customer/account/index');
    }

    /**
     * Capture Callback
     */
    public function authenticateAction() {
        $session = $this->_getSession();

        // Redirect if user is already authenticated
        if ($session->isLoggedIn()) {
            Mage::getSingleton('core/session')->addNotice(Mage::helper('capture')->__('You are already signed in.'));
            $this->postAuthRedirect();
            return;
        }

        $code = $this->getRequest()->getParam('code');
        $main = $this->getRequest()->getParam('main');
        $from_sso = $this->getRequest()->getParam('from_sso');
        $origin = $this->getRequest()->getParam('origin');

        if (!$code && !$main)
            Mage::throwException(Mage::helper('capture')->__('Authentication code not received. Please try again.'));

        if ($code) {
            if ($from_sso && $origin)
                Mage::helper('capture/apicall')->new_access_token($code, $from_sso, urlencode($origin));
            else
                Mage::helper('capture/apicall')->new_access_token($code);
        }
        elseif ($main == 'window') {
            $this->postAuth = 'postBeforeAuthRedirect';
        }

        Mage::helper('capture/apicall')->load_user_entity();
        $capture_session = Mage::getSingleton('capture/session');
        $profile = $capture_session->getProfile();
        if ($profile == false) {
            Mage::getSingleton('core/session')->addError(Mage::helper('capture')->__('Could not load user profile. Please try again.'));
            $this->postDoRedirect();
            return;
        }

        if (($capture_session->getAction() == 'finish_third_party'
            || $capture_session->getAction() == 'legacy_register')
            && Mage::getStoreConfig('capture/fieldoptions/verification') == 'true'
            && $profile['emailVerified'] == null) {
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('capture')->__('A verification link has been sent to %s. Please check your email.', Mage::helper('capture')->fetchAttribute(Mage::getStoreConfig('capture/fieldoptions/email'))));
            $this->postDoRedirect();
            return;
        }
        elseif (Mage::getStoreConfig('capture/fieldoptions/verification') == 'true'
            && $profile['emailVerified'] == null) {
            $resend_link = 'https://'
                . Mage::helper('capture')->captureUiAddress()
                . '/oauth/resend_verification_email?access_token='
                . Mage::getSingleton('capture/session')->getAccessToken() . '&redirect_uri='
                . urlencode(Mage::getUrl('capture/api/resendVerification', array('_nosid' => true)));
            Mage::getSingleton('core/session')->addNotice(Mage::helper('capture')->__('Your email address has not yet been verified. Please check your email and try again. <a href="%s">Click Here</a> to have this email resent.', $resend_link));
            $this->postDoRedirect();
            return;
        }

        $customer = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('capture_uuid', $profile['uuid'])->getFirstItem();

        if ($customer->getId()) {
            
            //set user data
            $this->setUserData($customer);
            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
            $this->postDoRedirect();
        } else {
            $customer = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter(
                    'email', Mage::helper('capture')->fetchAttribute(Mage::getStoreConfig('capture/fieldoptions/email')))
                ->getFirstItem();

            if ($customer->getId()) {
                if (strlen($customer->getCaptureUuid()) > 1) {
                    Mage::getSingleton('core/session')->addError("Email address is associated with another account.");
                    $this->postDoRedirect();
                }
                else {
                    
                    // set user data
                    $this->setUserData($customer);
                    $customer->setCaptureUuid($profile['uuid']);
                    $customer->save();

                    Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                    $this->postDoRedirect();
                }
            } else {
                if ($from_sso)
                  $this->postAuth = 'postBeforeAuthRedirect';

                $errors = array();

                $customer = Mage::getModel('customer/customer')->setId(null);
                $customer->getGroupId();

                //set user data
                $this->setUserData($customer);
                        
                Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                $password = md5($profile['uuid'] . date("U"));
                $customer->setPassword($password);
                $customer->setConfirmation($password);

                $customer->setCaptureUuid($profile['uuid']);

                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($customerErrors, $errors);
                }

                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                    $customer->save();

                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail('confirmation', $session->getBeforeAuthUrl());
                        Mage::getSingleton('core/session')->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                        $this->postDoRedirect();
                        return;
                    }
                    else {
                        $session->setCustomerAsLoggedIn($customer);
                        $url = $this->_welcomeCustomer($customer);
                        $this->postDoRedirect();
                        return;
                    }
                }
                else {
                    if ($capture_session->getPasswordRecover() === true) {
                        $capture_session->setCollectMore(true);
                        $this->postDoRedirect();
                        return;
                    } else {
                        $this->_redirectUrl('https://'
                            . Mage::helper('capture')->captureUiAddress()
                            . '/oauth/'
                            . Mage::getStoreConfig('capture/optional/requiredfields')
                            . '?flags=stay_in_window&access_token='
                            . Mage::getSingleton('capture/session')->getAccessToken()
                            . '&callback=CAPTURE.completeAuth&xd_receiver='
                            . urlencode(Mage::getUrl('capture/api/xdcomm', array('_nosid' => true))));
                    }
                    return;
                }
            }
        }

        $this->postDoRedirect();
    }

    public function syncAction() {
        $session = $this->_getSession();

        // Redirect if user is not authenticated
        if (!$session->isLoggedIn()) {
            $this->postDoRedirect();
            return;
        }

        Mage::helper('capture/apicall')->load_user_entity();
        $capture_session = Mage::getSingleton('capture/session');
        $profile = $capture_session->getProfile();
        if ($profile == false) {
            Mage::getSingleton('core/session')->addError('Could not load user profile. Please try again.');
            $this->_redirect('capture/account/index');
            return;
        }

        $customer = $session->getCustomer();
        
        // set user data
        $this->setUserData($customer);
        Mage::getSingleton('core/session')->addSuccess("Profile data successfully updated.");
        
        $this->_redirect('customer/account/index');
    }
    
    public function profileAction() {
        $method = $this->getRequest()->getParam('method');
        $callback = $this->getRequest()->getParam('callback');
        if (!$callback)
            $callback = 'CAPTURE.closeProfile';
        $capture_session = Mage::getSingleton('capture/session');
        if (time() >= $capture_session->getExpirationTime()) {
            Mage::helper('capture/apicall')->refresh_access_token($capture_session->getRefreshToken());
        }
        $this->_redirectUrl('https://' . Mage::helper('capture')->captureUiAddress() . '/oauth/profile' . $method . '?flags=stay_in_window&access_token=' . $capture_session->getAccessToken() . '&callback=' . $callback . '&xd_receiver=' . urlencode(Mage::getUrl('capture/api/xdcomm', array('_nosid' => true))));
    }

    public function refreshAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('capture/refresh')->toHtml());
    }

    public function xdcommAction() {
        $this->getResponse()->setBody($this->getLayout()->createBlock('capture/xdcomm')->toHtml());
    }

    public function resendVerificationAction() {
        $session = $this->_getSession();
        Mage::getSingleton('core/session')->addSuccess(Mage::helper('capture')->__('A verification email has been resent to %s.', Mage::helper('capture')->fetchAttribute(Mage::getStoreConfig('capture/fieldoptions/email'))));
        $url = $session->getBeforeAuthUrl(true);
        if ($url)
            $this->_redirectUrl($session->getBeforeAuthUrl(true));
        else
            $this->_redirect('');
    }
    
    public function recoverPasswordAction() {
        $session = $this->_getSession();
        Mage::getSingleton('core/session')->addSuccess(Mage::helper('capture')->__('An email has been sent to the address you provided with instructions to reset your password.'));
        $url = $session->getBeforeAuthUrl(true);
        if ($url)
            $this->_redirectUrl($session->getBeforeAuthUrl(true));
        else
            $this->_redirect('');
    }
    
    public function tokenExpiredAction() {
        $this->_getSession()->logout()
            ->setBeforeAuthUrl(Mage::getUrl());

        $this->_redirect('');
    }

    protected function postAuthRedirect() {
        $this->_redirect('capture/api/refresh');
        return;
    }

    protected function postBeforeAuthRedirect() {
        $session = $this->_getSession();
        $url = $session->getBeforeAuthUrl(true);
        if ($url)
            $this->_redirectUrl($session->getBeforeAuthUrl(true));
        else
            $this->_redirect('');
    }

    protected function postDoRedirect() {
        call_user_func(array($this, $this->postAuth));
    }
    
    private function setUserData($customer)
    {
        // setup customer required fields
        if ($firstname = Mage::helper('capture')->fetchAttribute(Mage::getStoreConfig('capture/fieldoptions/firstname')))
            $customer->setFirstname($firstname);
        if ($lastname = Mage::helper('capture')->fetchAttribute(Mage::getStoreConfig('capture/fieldoptions/lastname')))
            $customer->setLastname($lastname);
        if ($email = Mage::helper('capture')->fetchAttribute(Mage::getStoreConfig('capture/fieldoptions/email')))
            $customer->setEmail($email);
        
        $customer->save();
        
        // setup default billing/shipping address
        $p = 'primaryAddress.';
        $address1  = Mage::helper('capture')->fetchAttribute($p.'address1');
        $address2  = Mage::helper('capture')->fetchAttribute($p.'address2');
        $city      = Mage::helper('capture')->fetchAttribute($p.'city');
        $plus4     = Mage::helper('capture')->fetchAttribute($p.'zipPlus4');
        $zip       = Mage::helper('capture')->fetchAttribute($p.'zip');
        $postcode  = $plus4 ? $plus4 : $zip;
        $phone     = Mage::helper('capture')->fetchAttribute($p.'phone');
        $region_id = '';
        $region    = '';
        $country   = '';
        
        if($state = Mage::helper('capture')->fetchAttribute($p.'stateAbbreviation'))
        {
            $country     = 'US';
            $regionModel = Mage::getModel('directory/region')->loadByCode($state, $country);
            $region_id   = $regionModel->getId();
            $region      = $regionModel->getName();
        }
        
        $customer_address = array (
                'firstname'  => $firstname,
                'lastname'   => $lastname,
                'street'     => array (
                         '0' => $address1,
                         '1' => $address2,
                ),
                'city'       => $city,
                'region_id'  => $region_id,
                'region'     => $region,
                'postcode'   => $postcode,
                'country_id' => $country,
                'telephone'  => $phone,
        );
        
        $customerAddress = Mage::getModel('customer/address');
        $customerAddress ->setData($customer_address)
        ->setCustomerId($customer->getId())
        ->setSaveInAddressBook('1');

        // check if address exists
        $match=false;
        foreach ($customer->getAddresses() as $addy) {
            if($addy->format('html')==$customerAddress->format('html')) { $match=true; }
        }
        
        if(count($customer->getAddresses())==0){$match=false;}
        
        // write a new address if it doesn't exist
        if(!$match) {
            $customerAddress->save();
            Mage::getSingleton('checkout/session')->getQuote()->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($customerAddress));
        }
    }

}

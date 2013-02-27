<?php


class Magalter_Fastlogin_AccountController extends Mage_Core_Controller_Front_Action
{

    public function loginPostAction() {
	
	$this->loadLayout();
	$session = Mage::getSingleton('customer/session');	
	$request = $this->getRequest();  	
	$userName = $request->getParam('userEmail');
	$userPassword = $request->getParam('userPassword');
		  
        try {
            
           $session->login($userName, $userPassword);       
	   $jsonData = $this->getTopLinksJson();
           echo "{".$jsonData."}";			 
			   
            } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:                        
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($userName));
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:  break;
                    }                   
                } catch (Exception $e) { }
	    $session->setUsername($userName);		
	    echo $message;	  
	}	 
	
	private function getTopLinksJson() {
	
		$cartLink = $this->getTopLinksText(
                    Mage::helper('checkout/cart')->getSummaryCount(),
                    $this->__("My cart"));
							
		if(Mage::helper('wishlist')->isAllow()) {			 
                    $wishlistLink =  $this->getTopLinksText(
			Mage::helper('wishlist')->getItemCount(),
			$this->__("My wishlist")
                     );              
                }
		else {
                    $wishlistLink = $this->__("My wishlist");                  
                }
		 
		$logOutLink = $this->getLayout()->createBlock('fastlogin/logoutLink')->toHtml();
		$welcome = $this->__('Welcome, %s!', htmlspecialchars(Mage::getSingleton('customer/session')->getCustomer()->getName()));
		
		$sideBar = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
		 
                    $json = $this->jsonEncode('cartLink',$cartLink,$sep = '');
                    $json .= $this->jsonEncode('wishlistLink',$wishlistLink);
                    $json .= $this->jsonEncode('logoutLink',$logOutLink);
                    $json .= $this->jsonEncode('welcome',$welcome);
                    $json .= $this->jsonEncode('action','redirect');
                    $json .= $this->jsonEncode('sideBar',$sideBar);
		
		return $json;
	 
	}
	
	 
	private function jsonEncode($key,$data,$sep=',') {           
           return "$sep\"$key\":".Zend_Json_Encoder::encode($data);	
	}
	
	private function getTopLinksText($count,$position) {	
		if( $count == 1 ) {
                $text = $this->__("$position (%s item)", $count);
            } elseif( $count > 0 ) {
                $text = $this->__("$position (%s items)", $count);
            } else {
                $text = $this->__("$position");
            }			
            return $text;
	}
 
}
<?php
class ADM_JustADemo_Model_Observer extends Mage_Admin_Model_Observer
{
    public function actionPreDispatchAdmin($observer)
    {
        $session = Mage::getSingleton('admin/session');
        /** @var $session Mage_Admin_Model_Session */
        $request = Mage::app()->getRequest();
        $user = $session->getUser();

        $requestedActionName = $request->getActionName();
        $openActions = array(
                'forgotpassword',
                'resetpassword',
                'resetpasswordpost',
                'logout',
                'refresh' // captcha refresh
        );
        if (in_array($requestedActionName, $openActions)) {
            $request->setDispatched(true);
        } else {
            if($user) {
                $user->reload();
            }
            if (!$user || !$user->getId()) {
                if ($request->getPost('login')) {
                    $postLogin  = $request->getPost('login');
                    $username   = isset($postLogin['username']) ? $postLogin['username'] : '';
                    $password   = isset($postLogin['password']) ? $postLogin['password'] : '';

                    try {
                        /** @var $user Mage_Admin_Model_User */
                        $user = Mage::getModel('admin/user');
                        $user->loadByUsername($username);
                        if ($user->getId()) {
                            $session->renewSession();

                            if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                                Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                            }
                            $session->setIsFirstPageAfterLogin(true);
                            $session->setUser($user);
                            $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());

                            $requestUri = $this->_getRequestUri($request);
                            if ($requestUri) {
                                Mage::dispatchEvent('admin_session_user_login_success', array('user' => $user));
                                header('Location: ' . $requestUri);
                                exit;
                            }
                        } else {
                            Mage::throwException(Mage::helper('adminhtml')->__('Invalid User Name or Password.'));
                        }
                    } catch (Mage_Core_Exception $e) {
                        Mage::dispatchEvent('admin_session_user_login_failed',
                                array('user_name' => $username, 'exception' => $e));
                        if ($request && !$request->getParam('messageSent')) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            $request->setParam('messageSent', true);
                        }
                    }


                }
                if (!$request->getParam('forwarded')) {
                    if ($request->getParam('isIframe')) {
                        $request->setParam('forwarded', true)
                        ->setControllerName('index')
                        ->setActionName('deniedIframe')
                        ->setDispatched(false);
                    } elseif($request->getParam('isAjax')) {
                        $request->setParam('forwarded', true)
                        ->setControllerName('index')
                        ->setActionName('deniedJson')
                        ->setDispatched(false);
                    } else {
                        $request->setParam('forwarded', true)
                        ->setRouteName('adminhtml')
                        ->setControllerName('index')
                        ->setActionName('login')
                        ->setDispatched(false);
                    }
                    return false;
                }
            }
        }

        $session->refreshAcl();
    }

    /**
     * Custom REQUEST_URI logic
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return string|null
     */
    protected function _getRequestUri($request = null)
    {
        if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
            return Mage::getSingleton('adminhtml/url')->getUrl('*/*/*', array('_current' => true));
        } elseif ($request) {
            return $request->getRequestUri();
        } else {
            return null;
        }
    }



    public function actionPreDispatch($observer)
    {
        $session = Mage::getSingleton('customer/session');

        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        $login = Mage::app()->getRequest()->getPost('login');
        if (!empty($login['username'])) {
            $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

            if ($customer->loadByEmail($login['username'])) {
                $session->setCustomerAsLoggedIn($customer);
                $session->renewSession();
                return true;
            }
        }
    }

}
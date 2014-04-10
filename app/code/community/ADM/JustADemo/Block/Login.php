<?php
class ADM_JustADemo_Block_Login extends Mage_Adminhtml_Block_Template
{
    public function getUsersJson()
    {
        $obj = Mage::getModel('customer/customer')->getCollection()->setOrder('email', 'ASC');
        $obj->getSelect()->limit(20);
        $userList=array();
        foreach($obj as $user) {
            $userList[] = array('value'=>$user->getEmail(), 'label'=>$user->getEmail() . ' ('. $user->getFirstname() . ' ' . $user->getLastname() .')');
        }

        return Mage::helper('core')->jsonEncode($userList);
    }
}
<?php
class ADM_JustADemo_Block_Adminhtml_Login extends Mage_Adminhtml_Block_Template
{
    public function getUsersJson()
    {
        $obj = Mage::getModel('admin/user')->getCollection()->setOrder('username', 'ASC');
        $obj->getSelect()->limit(20);
        $userList=array();
        foreach($obj as $user) {
            $userList[] = array('value'=>$user->getUsername(), 'label'=>$user->getUsername() . ' ('. $user->getFirstname() . ' ' . $user->getLastname() .')');
        }

        return Mage::helper('core')->jsonEncode($userList);
    }
}
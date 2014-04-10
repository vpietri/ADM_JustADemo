<?php
class ADM_JustADemo_Block_Adminhtml_Page_Header extends Mage_Adminhtml_Block_Page_Header
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('adm/justademo/page/header.phtml');
    }
}
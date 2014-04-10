<?php
class ADM_JustADemo_Block_Adminhtml_Page_Header extends Mage_Adminhtml_Block_Page_Header
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('adm/justademo/page/header.phtml');
    }

    public function getTitle()
    {
        $config = Mage::getSingleton('core/resource')->getConnection('core_read')->getConfig();
        $dbname = $config['dbname'];

        return $this->__('JustADemo: read dbname "%s"',$dbname);
    }
}
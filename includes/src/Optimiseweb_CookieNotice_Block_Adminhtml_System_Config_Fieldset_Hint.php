<?php

/**
 * Optimiseweb CookieNotice Block Adminhtml System Config Fieldset Hint
 *
 * @package     Optimiseweb_CookieNotice
 * @author      Kathir Vel (sid@optimiseweb.co.uk)
 * @copyright   Copyright (c) 2014 Optimise Web
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Optimiseweb_CookieNotice_Block_Adminhtml_System_Config_Fieldset_Hint extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_template = 'optimiseweb/cookienotice/system/config/fieldset/hint.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    public function getModuleName()
    {
        return 'Cookie Law Compliance - EU / UK - Implied Consent';
    }

    public function getModuleVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/Optimiseweb_CookieNotice/version');
    }

    public function getOptimisewebCookieNoticeVersion()
    {
        return $this->getModuleVersion();
    }

    public function getCheckExtensions()
    {
        return array();
    }

    private function getAdminEmail()
    {
        return Mage::getSingleton('admin/session')->getUser()->getEmail();
    }

    public function getOptimiseWebUrl()
    {
        $url = 'https://optimiseweb.co.uk?';
        $url .= 'utm_source=Magento_Extension&utm_medium=Extension_Settings&utm_campaign=Optimiseweb_CookieNotice';
        return $url;
    }

    public function getOptimiseWebEmailLink()
    {
        $email = 'info@optimiseweb.co.uk';
        $emailLink = 'mailto:' . $email;
        return $emailLink;
    }

    public function getKathirVelUrl()
    {
        $url = 'http://www.kathirvel.com?';
        $url .= 'utm_source=Magento_Extension&utm_medium=Extension_Settings&utm_campaign=Optimiseweb_CookieNotice';
        return $url;
    }

    public function getHelpDeskUrl()
    {
        $url = 'https://optimiseweb.co.uk/magento-extension-support/?';
        $url .= $this->getPxParams();
        return $url;
    }

    public function getPxUrl()
    {
        $url = 'https://optimiseweb.co.uk/magento-connect/assets/logo/optimiseweb.php?';
        $url .= $this->getPxParams();
        return $url;
    }

    public function getPxParams()
    {
        $v = $this->getModuleVersion();
        $ext = 'Optimiseweb_CookieNotice_' . $v;
        $modulesArray = (array) Mage::getConfig()->getNode('modules')->children();
        $aux = (array_key_exists('Enterprise_Enterprise', $modulesArray)) ? 'EE' : 'CE';
        $mageVersion = Mage::getVersion();
        $mage = 'Magento_' . $aux . '_' . $mageVersion;
        $hash = md5($ext . '_' . $mage . '_' . $ext);
        $url = Mage::getBaseUrl();
        $email = $this->getAdminEmail();
        return 'extension=' . $ext . '&magento=' . $mage . '&url=' . $url . '&email=' . $email . '&ctrl=' . $hash;
    }

}

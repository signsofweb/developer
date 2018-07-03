<?php

class Imaginato_Directory_Model_Store extends Mage_Core_Model_Store
{

    /**
     * Set current store currency code
     *
     * @param   string $code
     * @return  string
     */
    public function setCurrentCurrencyCode($code)
    {
        $code = strtoupper($code);
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            $this->_getCountrySession()->setCurrencyCode($code);
            if ($code == $this->getDefaultCurrency()) {
                Mage::app()->getCookie()->delete(self::COOKIE_CURRENCY, $code);
            } else {
                Mage::app()->getCookie()->set(self::COOKIE_CURRENCY, $code);
            }
        }
        return $this;
    }

    /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        // try to get currently set code among allowed
        $code = $this->_getCountrySession()->getCurrencyCode();
        if (empty($code)) {
            $code = $this->getDefaultCurrencyCode();
        }
        if (in_array($code, $this->getAvailableCurrencyCodes(true))) {
            return $code;
        }

        // take first one of allowed codes
        $codes = array_values($this->getAvailableCurrencyCodes(true));
        if (empty($codes)) {
            // return default code, if no codes specified at all
            return $this->getDefaultCurrencyCode();
        }
        return array_shift($codes);
    }

    /**
     * Get default store currency code
     *
     * @return string
     */
    public function getDefaultCurrencyCode()
    {
        $switchCountry = Mage::getModel('core/cookie')->get('__switch-county');
        $countryCurrency = Mage::getConfig()->getNode('global/country/defalut_currency')->asArray();
        if(!empty($switchCountry) && isset($countryCurrency[$switchCountry])){
            $result = $countryCurrency[$switchCountry];
        }else{
            $result = parent::getDefaultCurrencyCode();
        }
        return $result;
    }

    /**
     * Retrieve store session object
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getCountrySession()
    {
        if (!$this->_countrySession) {
            $this->_countrySession = Mage::getModel('core/session')
                ->init('country_'.Mage::getModel('core/cookie')->get('__switch-county'));
        }
        return $this->_countrySession;
    }
}

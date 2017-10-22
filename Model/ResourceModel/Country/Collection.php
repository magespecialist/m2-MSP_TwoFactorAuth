<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model\ResourceModel\Country;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'msp_tfa_country_codes_id';

    protected function _construct()
    {
        $this->_init(
            \MSP\TwoFactorAuth\Model\Country::class,
            \MSP\TwoFactorAuth\Model\ResourceModel\Country::class
        );
    }
}

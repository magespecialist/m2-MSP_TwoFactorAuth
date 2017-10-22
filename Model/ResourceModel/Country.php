<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Country extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('msp_tfa_country_codes', 'msp_tfa_country_codes_id');
    }
}

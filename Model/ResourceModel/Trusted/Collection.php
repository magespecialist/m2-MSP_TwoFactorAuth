<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model\ResourceModel\Trusted;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'msp_tfa_trusted_id';

    protected function _construct()
    {
        $this->_init(
            \MSP\TwoFactorAuth\Model\Trusted::class,
            \MSP\TwoFactorAuth\Model\ResourceModel\Trusted::class
        );
    }
}

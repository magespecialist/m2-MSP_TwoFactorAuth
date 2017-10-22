<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model\ResourceModel\UserConfig;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'msp_tfa_user_config_id';

    protected function _construct()
    {
        $this->_init(
            \MSP\TwoFactorAuth\Model\UserConfig::class,
            \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig::class
        );
    }
}

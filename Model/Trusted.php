<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Model;

use Magento\Framework\Model\AbstractModel;

class Trusted extends AbstractModel implements \MSP\TwoFactorAuth\Api\Data\TrustedInterface
{
    protected function _construct()
    {
        $this->_init('\MSP\TwoFactorAuth\Model\ResourceModel\Trusted');
    }

    public function getId()
    {
        return $this->getData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::ID);
    }

    public function getDateTime()
    {
        return $this->getData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::DATE_TIME);
    }

    public function getUserId()
    {
        return $this->getData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::USER_ID);
    }

    public function getDeviceName()
    {
        return $this->getData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::DEVICE_NAME);
    }

    public function getUserAgent()
    {
        return $this->getData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::USER_AGENT);
    }

    public function getToken()
    {
        return $this->getData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::TOKEN);
    }

    public function getLastIp()
    {
        return $this->getData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::LAST_IP);
    }

    public function setId($value)
    {
        $this->setData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::ID, $value);
        return $this;
    }

    public function setDateTime($value)
    {
        $this->setData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::DATE_TIME, $value);
        return $this;
    }

    public function setUserId($value)
    {
        $this->setData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::USER_ID, $value);
        return $this;
    }

    public function setDeviceName($value)
    {
        $this->setData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::DEVICE_NAME, $value);
        return $this;
    }

    public function setUserAgent($value)
    {
        $this->setData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::USER_AGENT, $value);
        return $this;
    }

    public function setToken($value)
    {
        $this->setData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::TOKEN, $value);
        return $this;
    }

    public function setLastIp($value)
    {
        $this->setData(\MSP\TwoFactorAuth\Api\Data\TrustedInterface::LAST_IP, $value);
        return $this;
    }
}

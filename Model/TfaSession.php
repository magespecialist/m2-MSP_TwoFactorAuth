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

use Magento\Framework\Session\SessionManager;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;

class TfaSession extends SessionManager implements TfaSessionInterface
{
    /**
     * Set 2FA session as passed
     * @return $this
     */
    public function grantAccess()
    {
        $this->storage->setData(TfaSessionInterface::KEY_PASSED, true);
        return $this;
    }

    /**
     * Return true if 2FA session has been passed
     * @return boolean
     */
    public function isGranted()
    {
        return !!$this->storage->getData(TfaSessionInterface::KEY_PASSED);
    }
}

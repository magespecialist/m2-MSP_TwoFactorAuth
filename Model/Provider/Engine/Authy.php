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
 * @package    MSP_NoSpam
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Model\Provider\Engine;

use Magento\Framework\App\RequestInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Model\Provider\EngineInterface;

class Authy implements EngineInterface
{
    const CODE = 'duo_security'; // Must be the same as defined in di.xml

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function getIsEnabled()
    {
        return true;
    }

    /**
     * Return true on token validation
     * @param UserInterface $user
     * @param RequestInterface $request
     * @return bool
     */
    public function verify(UserInterface $user, RequestInterface $request)
    {
        return true;
    }
}

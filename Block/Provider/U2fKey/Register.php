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
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Block\Provider\U2fKey;

use Magento\Backend\Block\Template;
use Magento\Store\Model\Store;
use MSP\TwoFactorAuth\Model\Provider\Engine\U2fKey;
use Zend\Json\Json;

class Register extends Template
{

    /**
     * @var U2fKey
     */
    private $u2fKey;

    public function __construct(
        Template\Context $context,
        U2fKey $u2fKey,
        array $data = []
    )
    {
        $this->u2fKey = $u2fKey;
        parent::__construct($context, $data);
    }

    public function getRegisterData()
    {
        return Json::encode($this->u2fKey->getRegisterData());
    }

    public function getApplicationName()
    {
        return $this->_storeManager->getStore(Store::ADMIN_CODE)->getBaseUrl();
    }
}
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

namespace MSP\TwoFactorAuth\Block\Provider\Authy;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;

class Verify extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * Get URL to post activation code to
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('*/*/verifypost');
    }

    /**
     * Get configuration URL
     * @return string
     */
    public function getConfigUrl()
    {
        return $this->getUrl('*/*/configure');
    }

    /**
     * Get verification information
     * return array
     */
    public function getVerifyInfo()
    {
        return $this->registry->registry('msp_tfa_authy_verify');
    }
}

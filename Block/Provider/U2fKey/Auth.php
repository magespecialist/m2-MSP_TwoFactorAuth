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

namespace MSP\TwoFactorAuth\Block\Provider\U2fKey;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\U2fKey;

class Auth extends Template
{
    /**
     * @var U2fKey
     */
    private $u2fKey;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Template\Context $context,
        Session $session,
        EncoderInterface $encoder,
        U2fKey $u2fKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->u2fKey = $u2fKey;
        $this->encoder = $encoder;
        $this->session = $session;
    }

    /**
     * Get register data JSON payload
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAuthenticateData()
    {
        return $this->encoder->encode($this->u2fKey->getAuthenticateData($this->session->getUser()));
    }

    public function getPostUrl()
    {
        return $this->getUrl('*/*/authpost');
    }

    public function getSuccessUrl()
    {
        return $this->getUrl('/');
    }
}

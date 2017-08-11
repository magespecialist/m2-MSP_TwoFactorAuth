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
use Magento\Framework\Json\EncoderInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\U2fKey;

class Configure extends Template
{
    /**
     * @var U2fKey
     */
    private $u2fKey;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    public function __construct(
        Template\Context $context,
        EncoderInterface $encoder,
        U2fKey $u2fKey,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->u2fKey = $u2fKey;
        $this->encoder = $encoder;
    }

    /**
     * Get register data JSON payload
     * @return string
     */
    public function getRegisterData()
    {
        return $this->encoder->encode($this->u2fKey->getRegisterData());
    }

    /**
     * Get success URL
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->getUrl('/');
    }

    /**
     * Get register post URL
     * @return string
     */
    public function getConfigurePostUrl()
    {
        return $this->getUrl('*/*/configurepost');
    }
}
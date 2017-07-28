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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\Google;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Model\Provider\Google;

class Qrcode extends Action
{
    /**
     * @var Raw
     */
    private $rawResult;

    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var Google
     */
    private $google;

    public function __construct(
        Context $context,
        TfaInterface $tfa,
        Google $google,
        Raw $rawResult

    ) {
        parent::__construct($context);
        $this->tfa = $tfa;
        $this->rawResult = $rawResult;
        $this->google = $google;
    }

    public function execute()
    {
        $pngData = $this->google->getQrCodeAsPng();
        $this->rawResult
            ->setHttpResponseCode(200)
            ->setHeader('Content-Type', 'image/png')
            ->setContents($pngData);

        return $this->rawResult;
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $provider = $this->tfa->getUserProvider();
        return $provider && ($provider->getCode() == Google::CODE) && $this->tfa->getUserMustActivateTfa();
    }
}

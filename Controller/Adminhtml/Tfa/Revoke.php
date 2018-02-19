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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\Tfa;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use MSP\TwoFactorAuth\Api\TrustedManagerInterface;
use MSP\TwoFactorAuth\Controller\Adminhtml\AbstractAction;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Revoke extends AbstractAction
{
    /**
     * @var TrustedManagerInterface
     */
    private $trustedManager;

    public function __construct(
        Action\Context $context,
        TrustedManagerInterface $trustedManager
    ) {
        parent::__construct($context);
        $this->trustedManager = $trustedManager;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $tokenId = $this->getRequest()->getParam('id');
        $userId = $this->getRequest()->getParam('user_id');
        $this->trustedManager->revokeTrustedDevice($tokenId);

        $this->messageManager->addSuccessMessage(__('Device authorization revoked'));
        return $this->_redirect('adminhtml/user/edit', ['user_id' => $userId]);
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed() && $this->_authorization->isAllowed('MSP_TwoFactorAuth::tfa');
    }
}

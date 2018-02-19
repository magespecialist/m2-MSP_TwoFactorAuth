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

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User as UserResourceModel;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Controller\Adminhtml\AbstractAction;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Reset extends AbstractAction
{
    /**
     * @var UserResourceModel
     */
    private $userResourceModel;

    /**
     * @var UserFactory
     */
    private $userInterfaceFactory;

    /**
     * @var TfaInterface
     */
    private $tfa;

    public function __construct(
        Context $context,
        UserResourceModel $userResourceModel,
        TfaInterface $tfa,
        UserFactory $userFactory
    ) {
        parent::__construct($context);
        $this->userResourceModel = $userResourceModel;
        $this->userInterfaceFactory = $userFactory;
        $this->tfa = $tfa;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $userId = $this->getRequest()->getParam('id');
        $providerCode = $this->getRequest()->getParam('provider');

        $user = $this->userInterfaceFactory->create();
        $this->userResourceModel->load($user, $userId);

        if (!$user->getId()) {
            throw new LocalizedException(__('Invalid user'));
        }

        $provider = $this->tfa->getProvider($providerCode);
        if (!$provider) {
            throw new LocalizedException(__('Unknown provider'));
        }

        $provider->resetConfiguration($user->getId());

        $this->messageManager->addSuccessMessage(__('Configuration has been reset for this user'));
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

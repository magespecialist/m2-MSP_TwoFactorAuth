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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\U2f;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResponseInterface;
use MSP\TwoFactorAuth\Controller\Adminhtml\AbstractAction;
use MSP\TwoFactorAuth\Model\Provider\Engine\U2fKey;
use MSP\TwoFactorAuth\Model\Tfa;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Configure extends AbstractAction
{
    /**
     * @var Tfa
     */
    private $tfa;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Tfa $tfa,
        Session $session,
        Action\Context $context
    ) {
    
        $this->tfa = $tfa;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();

        return $this->getResponse();
    }

    /**
     * @return \Magento\User\Model\User|null
     */
    private function getUser()
    {
        return $this->session->getUser();
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $user = $this->getUser();

        return
            $user &&
            $this->tfa->getProviderIsAllowed($user->getId(), U2fKey::CODE) &&
            !$this->tfa->getProvider(U2fKey::CODE)->isActive($user->getId());
    }
}

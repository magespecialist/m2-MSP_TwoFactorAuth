<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Command;

use Magento\Framework\Exception\LocalizedException;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User;

class TfaReset extends Command
{
    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var User
     */
    private $userResource;

    /**
     * @var UserFactory
     */
    private $userFactory;

    public function __construct(
        UserConfigManagerInterface $userConfigManager,
        UserFactory $userFactory,
        User $userResource
    ) {
        parent::__construct();
        $this->userConfigManager = $userConfigManager;
        $this->userResource = $userResource;
        $this->userFactory = $userFactory;
    }

    protected function configure()
    {
        $this->setName('msp:security:tfa:reset');
        $this->setDescription('Reset configuration for one user');

        $this->addArgument('user', InputArgument::REQUIRED, __('Username'));
        $this->addArgument('provider', InputArgument::REQUIRED, __('Provider code (google, authy, u2fkey)'));

        parent::configure();
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userName = $input->getArgument('user');
        $provider = $input->getArgument('provider');

        $user = $this->userFactory->create();

        $this->userResource->load($user, $userName, 'username');
        if (!$user->getId()) {
            throw new LocalizedException(__('Unknown user %1', $userName));
        }

        $this->userConfigManager->resetProviderConfig($user->getId(), $provider);
    }
}

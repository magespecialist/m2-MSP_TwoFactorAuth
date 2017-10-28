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
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface CountryInterface extends ExtensibleDataInterface
{
    const ID = 'msp_tfa_country_codes_id';
    const CODE = 'code';
    const NAME = 'name';
    const DIAL_CODE = 'dial_code';

    /**
     * Get value for msp_tfa_country_codes_id
     * @return int
     */
    public function getId();

    /**
     * Set value for msp_tfa_country_codes_id
     * @param int $value
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface
     */
    public function setId($value);

    /**
     * Get value for code
     * @return string
     */
    public function getCode();

    /**
     * Set value for code
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface
     */
    public function setCode($value);

    /**
     * Get value for name
     * @return string
     */
    public function getName();

    /**
     * Set value for name
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface
     */
    public function setName($value);

    /**
     * Get value for dial_code
     * @return string
     */
    public function getDialCode();

    /**
     * Set value for dial_code
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface
     */
    public function setDialCode($value);

    /**
     * Retrieve existing extension attributes object or create a new one
     * @return \MSP\TwoFactorAuth\Api\Data\CountryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     * @param \MSP\TwoFactorAuth\Api\Data\CountryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MSP\TwoFactorAuth\Api\Data\CountryExtensionInterface $extensionAttributes
    );
}

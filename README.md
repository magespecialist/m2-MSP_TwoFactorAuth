# MSP TwoFactorAuth

Two Factor Authentication module for maximum **backend access protection** in Magento 2.

> Member of **MSP Security Suite**
>
> See: https://github.com/magespecialist/m2-MSP_Security_Suite

Did you lock yourself out from Magento backend? <a href="https://github.com/magespecialist/m2-MSP_TwoFactorAuth#emergency-commandline-disable">click here.</a>

## Installing on Magento2:

**1. Install using composer**

From command line: 

`composer require msp/twofactorauth`

**2. Enable and configure from your Magento backend config**

Enable from **Store > Config > SecuritySuite > Two Factor Authentication**.

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/config.png" />

**3. Enable two factor authentication for your user**

You can select between a set of different 2FA providers.

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/user_tfa.png" />

**4. Google Authenticator example**

**4.1. Scan the QR code with your Two Factor Authentication application**

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/token.png" />

**4.2. Login and type a valid two factor authentication code**

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/login_token.png" />

**5. Duo Security example**

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/duo.png" />

## Emergency commandline disable:

If you messed up with two factor authentication you can disable it from command-line:

`php bin/magento msp:security:tfa:disable`

This will disable two factor auth globally.

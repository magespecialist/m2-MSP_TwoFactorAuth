# MSP TwoFactorAuth

Two Factor Authentication module for maximum **backend access protection** in Magento 2.

> Member of **MSP Security Suite**
>
> See: https://github.com/magespecialist/m2-MSP_SecuritySuiteFull

Did you lock yourself out from Magento backend? <a href="https://github.com/magespecialist/m2-MSP_TwoFactorAuth/new/master?readme=1#emergency-commandline-disable">click here.</a>

## Installing on Magento2:

**1. Install using composer**

From command line: 

`composer require msp/twocatorauth`

**2. Enable and configure from your Magento backend config**

Enable from **Store > Config > MageSpecialist > SecuritySuite > Two Factor Authentication**.

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/config.png" />

**3. Enable two factor authentication for your user**

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/user_tfa.png" />

**4. Scan the QR code with your Two Factor Authentication application**

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/token.png" />

**5. Login and type a valid two factor authentication code**

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_TwoFactorAuth/master/screenshots/login_token.png" />

## Emergency commandline disable:

If you messed up with two factor authentication you can disable it from command-line:

`php bin/magento msp:security:tfa:disable`

This will disable two factor auth globally.

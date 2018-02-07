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

'use strict';

define([
    'jquery',
    'ko',
    'uiComponent',
    'MSP_TwoFactorAuth/js/error',
    'MSP_TwoFactorAuth/js/authy/configure/registry',
    'mage/translate'
], function ($, ko, Component, error, registry) {
    return Component.extend({
        verifyCode: ko.observable(''),
        messageText: registry.messageText,
        waitText: ko.observable(''),

        verifyPostUrl: '',
        successUrl: '',

        defaults: {
            template: 'MSP_TwoFactorAuth/authy/configure/verify'
        },

        /**
         * Get verification post URL
         * @returns {String}
         */
        getVerifyPostUrl: function () {
            return this.verifyPostUrl;
        },

        /**
         * Go to next step
         */
        nextStep: function () {
            registry.currentStep('login');
            self.location.href = this.successUrl;
        },

        /**
         * Verify auth code
         */
        doVerify: function () {
            var me = this;

            this.waitText('Please wait...');
            $.post(this.getVerifyPostUrl(), {
                'tfa_verify': this.verifyCode()
            })
                .done(function (res) {
                    if (res.success) {
                        me.nextStep();
                    } else {
                        error.display(res.message);
                    }
                    me.waitText('');
                })
                .fail(function () {
                    error.display('There was an internal error trying to verify your code');
                    me.waitText('');
                });
        },

        /**
         * Go to previous step to change phone number
         */
        changePhoneNumber: function () {
            registry.currentStep('register');
        }
    });
});

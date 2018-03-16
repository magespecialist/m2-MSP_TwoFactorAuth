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
    'MSP_TwoFactorAuth/js/registry'
], function ($, ko, Component, error, registry) {
    return Component.extend({
        currentStep: ko.observable('register'),
        waitText: ko.observable(''),
        verifyCode: ko.observable(''),
        defaults: {
            template: 'MSP_TwoFactorAuth/google/auth'
        },

        trustThisDevice: registry.trustThisDevice,

        qrCodeUrl: '',
        postUrl: '',
        successUrl: '',

        /**
         * Get QR code URL
         * @returns {String}
         */
        getQrCodeUrl: function () {
            return this.qrCodeUrl;
        },

        /**
         * Get POST URL
         * @returns {String}
         */
        getPostUrl: function () {
            return this.postUrl;
        },

        /**
         * Go to next step
         */
        nextStep: function () {
            this.currentStep('login');
            self.location.href = this.successUrl;
        },

        /**
         * Verify auth code
         */
        doVerify: function () {
            var me = this;

            this.waitText('Please wait...');
            $.post(this.getPostUrl(), {
                'tfa_code': this.verifyCode(),
                'tfa_trust_device': me.trustThisDevice() ? 1 : 0
            })
                .done(function (res) {
                    if (res.success) {
                        me.nextStep();
                    } else {
                        error.display(res.message);
                        me.verifyCode('');
                    }
                    me.waitText('');
                })
                .fail(function () {
                    error.display('There was an internal error trying to verify your code');
                    me.waitText('');
                });
        }
    });
});

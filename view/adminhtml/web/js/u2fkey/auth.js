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
    'MSP_TwoFactorAuth/js/registry',
    'MSP_TwoFactorAuth/js/u2fkey/api'
], function ($, ko, Component, error, registry) {
    return Component.extend({
        currentStep: ko.observable('register'),
        trustThisDevice: registry.trustThisDevice,

        defaults: {
            template: 'MSP_TwoFactorAuth/u2fkey/auth'
        },

        postUrl: '',
        successUrl: '',
        touchImageUrl: '',
        authenticateData: {},

        /**
         * Start waiting loop
         */
        onAfterRender: function () {
            this.waitForTouch();
        },

        /**
         * Get touch image URL
         * @returns {String}
         */
        getTouchImageUrl: function () {
            return this.touchImageUrl;
        },

        /**
         * Get POST URL
         * @returns {String}
         */
        getPostUrl: function () {
            return this.postUrl;
        },

        /**
         * Get success URL
         * @returns {String}
         */
        getSuccessUrl: function () {
            return this.successUrl;
        },
        /**
         * Wait for key touch
         */
        waitForTouch: function () {
            var requestData = this.authenticateData,
                me = this;

            // eslint-disable-next-line no-undef
            u2f.sign(
                requestData,
                function (signResponse) {
                    $.post(me.getPostUrl(), {
                        'request': requestData,
                        'response': signResponse,
                        'tfa_trust_device': me.trustThisDevice() ? 1 : 0
                    }).done(function (res) {
                        if (res.success) {
                            me.currentStep('login');
                            self.location.href = me.getSuccessUrl();
                        } else {
                            me.waitForTouch();
                        }
                    }).fail(function () {
                        error.display('Invalid device');
                    });
                }, 120
            );
        }
    });
});

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
    'ko',
    'uiComponent',
    'MSP_TwoFactorAuth/js/duo/api'
], function (ko, Component, duo) {
    return Component.extend({
        currentStep: ko.observable('register'),

        defaults: {
            template: 'MSP_TwoFactorAuth/duo/auth'
        },

        signature: '',
        apiHost: '',
        postUrl: '',
        authenticateData: {},

        /**
         * Start waiting loop
         */
        onAfterRender: function () {
            window.setTimeout(function () {
                duo.init();
            }, 100);
        },

        /**
         * Get POST URL
         * @returns {String}
         */
        getPostUrl: function () {
            return this.postUrl;
        },

        /**
         * Get signature
         * @returns {String}
         */
        getSignature: function () {
            return this.signature;
        },

        /**
         * Get API host
         * @returns {String}
         */
        getApiHost: function () {
            return this.apiHost;
        }
    });
});

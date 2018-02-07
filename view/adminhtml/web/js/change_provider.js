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
    'uiComponent',
    'ko'
], function (Component, ko) {
    return Component.extend({
        showChangeMethod: ko.observable(false),

        providers: [],
        switchIcon: '',

        defaults: {
            template: 'MSP_TwoFactorAuth/change_provider'
        },

        /**
         * Get switch icon URL
         * @returns {String}
         */
        getSwitchIconUrl: function () {
            return this.switchIcon;
        },

        /**
         * Show available alternative 2FA providers
         */
        displayChangeMethod: function () {
            this.showChangeMethod(true);
        },

        /**
         * Return a list of alternative providers
         * @returns {Array}
         */
        getProviders: function () {
            return this.providers;
        }
    });
});

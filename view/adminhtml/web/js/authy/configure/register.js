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
        configurePostUrl: '',
        countries: [],

        country: ko.observable(''),
        phone: ko.observable(''),
        method: ko.observable(''),

        waitText: ko.observable(''),

        defaults: {
            template: 'MSP_TwoFactorAuth/authy/configure/register'
        },

        /**
         * Get configure POST url
         * @returns {String}
         */
        getConfigurePostUrl: function () {
            return this.configurePostUrl;
        },

        /**
         * Get a list of available countries
         * @returns {Array}
         */
        getCountries: function () {
            return this.countries;
        },

        /**
         * Go to next step
         */
        nextStep: function () {
            registry.currentStep('verify');
            window.setTimeout(function () {
                registry.currentStep('register');
            }, registry.secondsToExpire() * 1000);
        },

        /**
         * Start Authy registration procedure
         */
        doRegister: function () {
            var me = this;

            this.waitText('Please wait...');
            $.post(this.getConfigurePostUrl(), {
                'tfa_country': this.country(),
                'tfa_phone': this.phone(),
                'tfa_method': this.method()

            })
                .done(function (res) {
                    if (res.success) {
                        registry.messageText(res.message);
                        registry.secondsToExpire(res['seconds_to_expire']);
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
        }
    });
});

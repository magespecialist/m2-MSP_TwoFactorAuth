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

define(['Magento_Ui/js/form/element/abstract'], function (Abstract) {
    return Abstract.extend({
        /**
         * Get a list of forced providers
         * @returns {Array}
         */
        getForcedProviders: function () {
            return this.forced_providers;
        },

        /**
         * Get a list of enabled providers
         * @returns {Array}
         */
        getEnabledProviders: function () {
            return this.enabled_providers;
        },

        /**
         * Return true if a provider is selected
         * @param {String} provider
         * @returns {Boolean}
         */
        isSelected: function (provider) {
            var i, providers = this.value();

            for (i = 0; i < providers.length; i++) {
                if (providers[i] === provider) {
                    return true;
                }
            }

            return false;
        }
    });
});

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
  'Magento_Ui/js/form/element/abstract',
  'Magento_Ui/js/modal/confirm'
], function (Abstract, confirm) {
    return Abstract.extend({
        /**
         * Get a list of providers with reset option
         * @returns {Array}
         */
        getResetProviders: function () {
            return this.source.data['reset_providers'] ? this.source.data['reset_providers'] : [];
        },

        /**
         * Reset a provider
         * @param {Object} item
         */
        resetProvider: function (item) {
            confirm({
                title: 'Confirm',
                content: 'Are you sure you want to reset ' + item.label + ' provider?',
                actions: {
                    confirm: function () { // jscs:ignore jsDoc
                        self.location.href = item.url;
                    }
                }
            });
        }
    });
});

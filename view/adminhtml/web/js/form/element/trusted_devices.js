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
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    return Abstract.extend({
        /**
         * Get a list of trusted devices
         * @returns {Array}
         */
        getTrustedDevices: function () {
            return this.source.data['trusted_devices'] ? this.source.data['trusted_devices'] : [];
        },

        /**
         * Revoke a trusted device
         * @param {Object} item
         */
        revokeDevice: function (item) {
            self.location.href = item['revoke_url'];
        }
    });
});

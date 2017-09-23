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
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

'use strict';

define(['Magento_Ui/js/form/element/abstract'], function(Abstract) {
  return Abstract.extend({
    getForcedProviders: function() {
      return this.source.data['forced_providers'];
    },

    getEnabledProviders: function() {
      return this.source.data['enabled_providers'];
    },

    isSelected: function(provider) {
      var providers = this.value();
      var i;

      for (i=0; i<providers.length; i++) {
        if (providers[i] === provider) {
          return true;
        }
      }

      return false;
    }
  });
});

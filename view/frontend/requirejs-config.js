/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Tiargsa_CorreoArgentino/js/view/shipping': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Tiargsa_CorreoArgentino/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'Tiargsa_CorreoArgentino/js/action/create-shipping-address-mixin': true
            },
            'Magento_Checkout/js/action/set-billing-address': {
                'Tiargsa_CorreoArgentino/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Tiargsa_CorreoArgentino/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'Tiargsa_CorreoArgentino/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Tiargsa_CorreoArgentino/js/view/shipping-information-mixin': true,
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Tiargsa_CorreoArgentino/js/view/shipping-address/address-renderer/default': true,
            },
            'Magento_Checkout/js/view/shipping-information/address-renderer/default': {
                'Tiargsa_CorreoArgentino/js/view/shipping-information/address-renderer/default': true,
            },
            'Magento_Checkout/js/view/billing-address': {
                'Tiargsa_CorreoArgentino/js/view/billing-address': true,
            },
        }
    },
};

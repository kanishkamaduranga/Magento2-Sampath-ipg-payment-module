
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Elaboom_Sampath/js/view/payment/form-builder'
    ],
    function ($, Component, url, customerData, errorProcessor, fullScreenLoader, formBuilder) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Elaboom_Sampath/payment/sampathpaycorp',
                redirectAfterPlaceOrder: false
            },
            redirectAfterPlaceOrder: false,
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            afterPlaceOrder: function (data, event)  {

                var custom_controller_url = url.build('sampath/prams/index/method/40'); 
                $.post(custom_controller_url, 'json')
                    .done(function (response) {
                        customerData.invalidate(['cart']);
                        formBuilder(response).submit(); 
                    })
                    .fail(function (response) {
                        errorProcessor.process(response, this.messageContainer);
                    })
                    .always(function () {
                        fullScreenLoader.stopLoader();
                    });
            }
        });
    }
);

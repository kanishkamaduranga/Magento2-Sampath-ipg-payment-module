/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: '',
                component: 'Elaboom_Sampath/js/view/payment/method-renderer/sampathpaycorp-method'
            },
            {
                type: 'sampathpaycorp6',
                component: 'Elaboom_Sampath/js/view/payment/method-renderer/sampathpaycorp6-method'
            },
            {
                type: 'sampathpaycorp12',
                component: 'Elaboom_Sampath/js/view/payment/method-renderer/sampathpaycorp12-method'
            },
            {
                type: 'sampathpaycorp24',
                component: 'Elaboom_Sampath/js/view/payment/method-renderer/sampathpaycorp24-method'
            },
            {
                type: 'sampathpaycorp40',
                component: 'Elaboom_Sampath/js/view/payment/method-renderer/sampathpaycorp40-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Elaboom\Sampath\Model;



/**
 * Pay In Store payment method model
 */
class SampathPaycorp12 extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'sampathpaycorp12';

    protected $_isGateway = true;
    protected $_canCapture = false;
    protected $_canAuthorize = false;
    protected $_canOrder = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }


}

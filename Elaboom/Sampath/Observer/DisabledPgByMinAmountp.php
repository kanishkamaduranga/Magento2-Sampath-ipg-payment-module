<?php

namespace Elaboom\Sampath\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class DisabledPgByMinAmountp implements ObserverInterface
{
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $result          = $observer->getEvent()->getResult();
        $method_instance = $observer->getEvent()->getMethodInstance();
        $quote           = $observer->getEvent()->getQuote();
        $this->_logger->info($method_instance->getCode());

        //$grandTotal = $quote->getGrandTotal();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        $grandTotal = (int)$cart->getQuote()->getGrandTotal();

       

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

        switch ($method_instance->getCode()){

            case 'sampathpaycorp' :

                if($resource->getValue('payment/sampathpaycorp/min_amount')){
                    if( $grandTotal < $resource->getValue('payment/sampathpaycorp/min_amount')) {
                        $result->setData('is_available', false);
                    }
                }
                break;
            case 'sampathpaycorp6' :

                if($resource->getValue('payment/sampathpaycorp6/min_amount')){
                    if( $grandTotal < $resource->getValue('payment/sampathpaycorp6/min_amount')) {
                        $result->setData('is_available', false);
                    }
                }
                break;
            case 'sampathpaycorp12' :

                if($resource->getValue('payment/sampathpaycorp12/min_amount')){
                    if( $grandTotal < $resource->getValue('payment/sampathpaycorp12/min_amount')) {
                        $result->setData('is_available', false);
                    }
                }
                break;
            case 'sampathpaycorp24' :

                if($resource->getValue('payment/sampathpaycorp24/min_amount')){
                    if( $grandTotal < $resource->getValue('payment/sampathpaycorp24/min_amount')) {
                        $result->setData('is_available', false);
                    }
                }
                break;
            case 'sampathpaycorp40' :
    
             	if($resource->getValue('payment/sampathpaycorp40/min_amount')){
              		if( $grandTotal < $resource->getValue('payment/sampathpaycorp40/min_amount')) {
                        $result->setData('is_available', false);
                    }
                }
                break;
            
        }

    }
}

<?php


namespace Elaboom\Sampath\Block;


use Magento\Framework\View\Element\Template as TemplateAlias;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface as ManagerInterfaceAlias;

class Request extends TemplateAlias
{

    private $request;

    private $response_data;

    protected $messageManager;

    private $request_data;

    private $instalment_plan;

    public function __construct(
        Http $request,
        ManagerInterfaceAlias $messageManager,
        Context $context
    )
    {
        parent::__construct($context);
        $this->request = $request;
        $this->response_data = $this->request->getParams();
        $this->messageManager = $messageManager;
        $this->request_data = $this->loadData();
    }

    public function loadData(){
       
        $id = $this->response_data['id'];
        $hash_reference = $this->response_data['hash_reference'];

        if( $id == md5($hash_reference)){

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $tableName = $resource->getTableName('eb_sampath_paycorp');

            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $store = $storeManager->getStore();

            $record = $resource->getConnection()->fetchRow("SELECT * FROM `".$tableName."` WHERE `hash` = '".$hash_reference."'" );
            if(isset($record['clientRef']))
            {
                $this->instalment_plan  = $record['instalment_plan'];
                return $request_data = json_decode( $record['request'], true);
            }else {
                $this->messageManager->addError('Invalid Payment Request');
            }
        }else{
            $this->messageManager->addError('Invalid Payment Request');
        }

        return null;
    }

    public function getTitle(){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

        switch ($this->instalment_plan){

            case 6:
                return $resource->getValue('payment/sampathpaycorp6/title');
                break;        
            case 12:
                return $resource->getValue('payment/sampathpaycorp12/title');
                break;
            case 24:
                return $resource->getValue('payment/sampathpaycorp24/title');
                break;
            case 40:
                return $resource->getValue('payment/sampathpaycorp40/title');
                break;    
            }   
        return $resource->getValue('payment/sampathpaycorp/title');

    }

    public function setJs()
    {
        
        $request_data = $this->request_data;

        if( $request_data ){        
                

            $js = '<script src="https://sampath.paycorp.lk/webinterface/qw/paycorp_payments.js"></script>';
            $js .= "<script >
                    function buildPayment() {
                        // Build the payment object
                        return {
                            clientId: ".$request_data['clientId'].",
                            paymentAmount: ".$request_data['paymentAmount'].",
                            currency: '".$request_data['currency']."',
                            returnUrl: '".$request_data['returnUrl']."',
                            clientRef: '".$request_data['clientRef']."',
                            comment: '".$request_data['comment']."'
                        };
                    }
                
                </script>";

            $js .= '<script type="text/javascript">
                        window.onload=function() {
                            document.getElementById("AutoClick").click();
                        };
            
                    </script>';

            return $js;

        }  
        return null; 
    }
}

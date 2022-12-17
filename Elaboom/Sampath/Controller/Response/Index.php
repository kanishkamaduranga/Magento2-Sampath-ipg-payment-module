<?php


namespace Elaboom\Sampath\Controller\Response;



use Elaboom\Sampath\Helper\Data;
use Magento\Checkout\Model\Session as SessionAlias;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpAlias;
use Magento\Framework\Data\Form\FormKey as FormKeyAlias;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepositoryInterfaceAlias;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;
use PHPUnit\Exception;
use function GuzzleHttp\json_encode;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Message\ManagerInterface;

class Index extends Action
{


    /**
     * @var Context
     */
    protected $context;
    /**
     * @var Data
     */
    protected $sampath_data;
    /**
     * @var StoreManagerInterfaceAlias
     */
    protected $storemanager;
    /**
     * @var OrderRepositoryInterfaceAlias
     */
    protected $orderRepository;
    /**
     * @var SessionAlias
     */
    protected $checkoutSession;
    /**
     * @var FormKeyAlias
     */
    protected $formKey;
    /**
     * @var HttpAlias
     */
    protected $request;

    protected $_order;

    protected $order_model;

    protected $messageManager;

    public function __construct(
        Context $context,
        Data $sampath_data,
        StoreManagerInterfaceAlias $storemanager,
        OrderManagementInterface $orderManagementInterface,
        OrderRepositoryInterfaceAlias $orderRepository,
        SessionAlias $checkoutSession,
        FormKeyAlias $formKey,
        HttpAlias $request,
        Order $order_model,
        ManagerInterface $messageManager
    )
    {
        $this->request = $request;
        $this->formKey = $formKey;
        $this->request->setParam('form_key', $this->formKey->getFormKey());
        $this->context = $context;
        $this->sampath_data = $sampath_data;
        $this->storemanager = $storemanager;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->formKey = $formKey;
        $this->request = $request;
        $this->_order = $orderManagementInterface;
        $this->order_model = $order_model;
        $this->messageManager = $messageManager;

        parent::__construct($context);
    }

    public function execute()
    {
        $request = $_REQUEST;
        $redirect_url = "/";

        try {

            if (isset($request['reqid']) && isset($request['clientRef'])) {

                $csrfToken = $request['reqid'];
                $clientRef = $request['clientRef'];

                $auth_Token = $this->sampath_data->auth_Token;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://sampath.paycorp.lk/webinterface/qw/confirm?csrfToken=' . $csrfToken . '&authToken=' . $auth_Token . '&clientRef=' . $clientRef);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $response = curl_exec($ch);

                $errors = curl_error($ch);
                curl_close($ch);

                $params = explode('&', $response);

                $results = [];
                foreach ($params as $element) {

                    //Make Key Value Pair Using List
                    list($key, $value) = explode('=', $element);
                    $results[$key] = $value;
                }

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

                $tableName = $resource->getTableName('eb_sampath_paycorp');

                $record = $resource->getConnection()->fetchRow("SELECT * FROM `" . $tableName . "` WHERE `clientRef` = '" . $clientRef . "'");

                /*echo "SELECT * FROM `" . $tableName . "` WHERE `clientRef` = '" . $clientRef . "'";
                var_dump($record);
                die();*/

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();

                if (isset($record['clientRef'])) {

                    $order_model = $this->order_model->loadByIncrementId( $clientRef);
                    $entity_id = $order_model->getId();
                    $order = $this->orderRepository->get($entity_id);
                    
                    $amount = $results['paymentAmount'];
                    $payment = $order->getPayment();

                    if (isset($results['txnReference'])) {

                        if( in_array( strval ($results['responseCode']), [ '00', '11', '08' ]) ) {

                            $payment->registerAuthorizationNotification($amount);
                            $payment->registerCaptureNotification($amount);
                            $payment->save();

                            $sql = "UPDATE `" . $tableName . "` SET
                                                `paymentAmount` = '" . $results['paymentAmount'] . "',
                                                `txnReference` = '" . $results['txnReference'] . "',
                                                `response` = '" . json_encode($results, true) . "',
                                                `status` = '2'
                                                WHERE `id` = '" . $record['id'] . "'";


                            

                            $update = $connection->query($sql);

                            $order->setState(Order::STATE_PROCESSING, true);
                            $order->setStatus(Order::STATE_PROCESSING);
                            $order->addStatusToHistory($order->getStatus(), 'Payment has been successful.');
                            $order->addStatusToHistory($order->getStatus(),
                                        '<br>txnReference : '. $results['txnReference'].
                                        '<br>responseText : '. $results['responseText'].
                                        '<br>cardHolderName : '. $results['cardHolderName'].
                                        '<br>paymentAmount : '. $results['paymentAmount'].
                                        '<br>cardType : '. $results['cardType'].
                                        '<br>currency : '. $results['currency']
                                    );
                            $order->save();
                            $this->messageManager->addSuccessMessage(__('Payment has been successful.'));
                            $this->messageManager->addSuccessMessage($results['responseText']);

                            //$resultRedirect = $this->resultRedirectFactory->create();
                            //$redirect_url = $resultRedirect->setPath('checkout/onepage/success');
                            //return $resultRedirect;
                            $redirect_url = $this->storemanager->getStore()->getUrl('checkout/onepage/success/');
                            
                            //TODO
                        }else{

                            $sql = "UPDATE `eb_sampath_paycorp` SET
                                            `response` = '" . json_encode($results, true) . "',
                                            `status` = '3'
                                            WHERE `id` = '" . $record['id'] . "'";

                            $update = $connection->query($sql);

                            /* $order->setState(Order::STATE_CANCELED, true);
                            $order->setStatus(Order::STATE_CANCELED);*/

                            $status =  $this->_order->cancel( $entity_id);
                            $order->addStatusToHistory(Order::STATE_CANCELED, 'Payment failed.');
                            $order->addStatusToHistory(Order::STATE_CANCELED,
                                        '<br>txnReference : '. $results['txnReference'].
                                        '<br>responseText : '. $results['responseText'].
                                        '<br>cardHolderName : '. $results['cardHolderName'].
                                        '<br>paymentAmount : '. $results['paymentAmount'].
                                        '<br>cardType : '. $results['cardType'].
                                        '<br>currency : '. $results['currency']
                                    );
                            $order->save();
                            $this->messageManager->addErrorMessage($results['responseText']);

                            //$resultRedirect = $this->resultRedirectFactory->create();
                            //$redirect_url = $resultRedirect->setPath('checkout/onepage/error');
                            //return $resultRedirect;
                            //$redirect_url = $this->storemanager->getStore()->getUrl('checkout/onepage/error');
                           
                            $this->reloadCart();
                            $redirect_url = $this->storemanager->getStore()->getUrl('checkout/cart');
                            //TODO
                        }

                    } else {

                        $sql = "UPDATE `eb_sampath_paycorp` SET
                                            `response` = '" . json_encode($results, true) . "',
                                            `status` = '3'
                                            WHERE `id` = '" . $record['id'] . "'";

                        $update = $connection->query($sql);

                        /* $order->setState(Order::STATE_CANCELED, true);
                         $order->setStatus(Order::STATE_CANCELED);*/

                        $status =  $this->_order->cancel( $entity_id);
                        $order->addStatusToHistory(Order::STATE_CANCELED, 'Payment failed.');
                        $order->save();
                        $this->messageManager->addErrorMessage(__('Payment Error, Please contact Administrator'));

                        //TODO

                        //$resultRedirect = $this->resultRedirectFactory->create();
                        //$redirect_url =$resultRedirect->setPath('checkout/onepage/error');
                        //return $resultRedirect;
                        
                        $this->reloadCart();
                        $redirect_url = $this->storemanager->getStore()->getUrl('checkout/cart');
                    }

                } else {
                    $this->messageManager->addErrorMessage('Payment Error, Please contact Administrator');
                }
            } else {
                $this->messageManager->addErrorMessage('Payment Error, Please contact Administrator');
            }

        }catch (Exception $exception){
            print_r($exception->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($redirect_url);
        return $resultRedirect;
    //echo $redirect_url;

        //$this->_redirect('/');
    }

    public function reloadCart()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_checkoutSession = $objectManager->create('\Magento\Checkout\Model\Session');
        $_quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
        
        $order = $_checkoutSession->getLastRealOrder();
        $quote = $_quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
        if ($quote->getId()) {
            $quote->setIsActive(1)->setReservedOrderId(null)->save();
            $_checkoutSession->replaceQuote($quote);
            //$resultRedirect = $this->resultRedirectFactory->create();
            //$resultRedirect->setPath('checkout/cart');
            //$this->messageManager->addWarningMessage('Payment Failed.');
            //return $resultRedirect;
        }
    }
}

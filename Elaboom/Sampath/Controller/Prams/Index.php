<?php


namespace Elaboom\Sampath\Controller\Prams;


use Elaboom\Sampath\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action as ActionAlias;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use function GuzzleHttp\json_encode;

class Index extends ActionAlias
{

    /**
     * @var Data
     */
    private $sampath_data;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    private $request;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        \Magento\Framework\Locale\Resolver $store,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\App\Request\Http $request,
        Data $sampath_data
    )
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig; //Used for getting data from System/Admin config
        $this->checkoutSession = $checkoutSession; //Used for getting the order: $order = $this->checkoutSession->getLastRealOrder(); And other order data like ID & amount
        $this->store = $store; //Used for getting store locale if needed $language_code = $this->store->getLocale();
        $this->urlBuilder = $urlBuilder; //Used for creating URLs to other custom controllers, for example $success_url = $this->urlBuilder->getUrl('frontname/path/action');
        $this->resultJsonFactory = $resultJsonFactory; //Used for returning JSON data to the afterPlaceOrder function ($result = $this->resultJsonFactory->create(); return $result->setData($post_data);)

        $this->context = $context;
        $this->sampath_data = $sampath_data;
        $this->request = $request;
        $this->countryFactory = $countryFactory;
    }
    public function execute()
    {

        $method = $this->request->getParam('method');

        $order = $this->checkoutSession->getLastRealOrder();
        $entityorderId=$order->getEntityId();
        $OrderID = $order->getIncrementId();

        $payment = $order->getPayment();
        $payment->setIsTransactionClosed(false);
        $payment->setIsTransactionPending(true);
        $payment->save();

        $order->setState(Order::STATE_PENDING_PAYMENT, true);
        $order->setStatus(Order::STATE_PENDING_PAYMENT);

        $order->save();

        $billing = $order->getBillingAddress();
        $ShipToFirstName = $billing->getFirstName();
        $ShipToLastName = $billing->getLastName();

        $amount = $order->getGrandTotal();
        $amount_for_payment_gateway = $amount*100;

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore();
        $return_url = $store->getBaseUrl().'sampath/response';

        $currency = $store->getCurrentCurrency()->getCode();
        $clientId = $this->sampath_data->client_id[(int)$method];

        $hash_reference = $this->sampath_data->generateHashReference( $OrderID);

        $request_array = [
            'clientId' => $clientId,
            'paymentAmount' => $amount_for_payment_gateway,
            'currency' => $currency,
            'returnUrl' => $return_url,
            'clientRef' => $OrderID,
            'comment' => $ShipToFirstName. ' ' .$ShipToLastName,
        ];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('eb_sampath_paycorp');

        $sql = "INSERT INTO `".$tableName."` (`clientRef`, `hash`, `instalment_plan`, `request`,  `status`)
                VALUES ( '".$OrderID."', '".$hash_reference."', '".$method."', '".json_encode( $request_array, true)."',  '1')";

        $inset = $connection->query($sql);

        $result = $this->resultJsonFactory->create();

        return $result->setData([
            'action'            => $return_url = $store->getBaseUrl().'sampath/request?id='.md5($hash_reference),
            'fields'    =>  [
                'hash_reference'    => $hash_reference
            ],
        ]);
    }

}

<?php


namespace Elaboom\Sampath\Controller\Request;



use Elaboom\Sampath\Helper\Data;
use Magento\Framework\App\Action\Action as ActionAlias;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface as ManagerInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use function GuzzleHttp\json_decode;

class Index extends ActionAlias
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Data
     */
    private $sampath_data;
    /**
     * @var Http
     */
    private $request;

    private $_resultPageFactory;

    protected $_pageFactory;

    public function __construct(
        Context $context,
        Http $request,
        Data $sampath_data,
        PageFactory $resultPageFactory,
        ManagerInterfaceAlias $messageManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
        $this->context = $context;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->sampath_data = $sampath_data;
    }

    public function execute()
    {
        //
        return $this->_pageFactory->create();
        /*$response_data = $this->request->getParams();

        $id = $response_data['id'];
        $hash_reference = $response_data['hash_reference'];

        if( $id == md5($hash_reference)){

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $tableName = $resource->getTableName('eb_sampath_paycorp');

            $record = $resource->getConnection()->fetchRow("SELECT * FROM `".$tableName."` WHERE `hash` = '".$hash_reference."'" );
            if(isset($record['clientRef']))
            {
                $request_data = json_decode( $record['request'], true);

                $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $store = $storeManager->getStore();

                $this->_view->loadLayout();
                

                $block = $this->_view->getLayout()->createBlock('Elaboom\Sampath\Block\Request');
                $block->setTemplate('Elaboom_Sampath::request.phtml');

                $block->setData('clientId', $request_data['clientId']);
                $block->setData('paymentAmount', $request_data['paymentAmount']);
                $block->setData('currency', $request_data['currency']);
                $block->setData('returnUrl', $request_data['returnUrl']);
                $block->setData('clientRef', $request_data['clientRef']);
                $block->setData('comment', $request_data['comment']);

                $this->getResponse()->appendBody($block->toHtml());
                $this->_view->renderLayout();

            }else {
                $this->messageManager->addError('Invalid Payment Request');
                $this->_redirect('/');
            }
        }else{
            $this->messageManager->addError('Invalid Payment Request');
            $this->_redirect('/');
        }*/
    }

}

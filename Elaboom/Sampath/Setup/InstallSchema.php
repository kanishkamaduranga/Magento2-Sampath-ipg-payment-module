<?php


namespace Elaboom\Sampath\Setup;


use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('eb_sampath_paycorp')) {

            $table = $installer->getConnection()->newTable(
                $installer->getTable('eb_sampath_paycorp')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'ID'
            )->addColumn(
                'clientRef',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                25,
                ['nullable => false'],
                'clientRef'
            )->addColumn(
                'hash',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                250,
                ['nullable => false'],
                'hash'
            )->addColumn(
                'instalment_plan',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable => false', 'default' => 0 ],
                'instalment_plan'
            )->addColumn(
                'request',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable => false'],
                'Request data'
            )->addColumn(
                'paymentAmount',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                null,
                ['nullable => false'],
                'paymentAmount'
            )->addColumn(
                'txnReference',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                25,
                ['nullable => true'],
                'txnReference'
            )->addColumn(
                'response',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable => true'],
                'Response data'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                1,
                ['default' => 0 ],
                '1 => pending , 2 => Success , 3 => Failed '
            )->setComment('Sampath payment details ');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}

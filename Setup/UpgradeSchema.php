<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Tiargsa\CorreoArgentino\Api\Data\ZoneInterface;
use Tiargsa\CorreoArgentino\Api\Data\RateInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->upgradeTo200($setup);
        }
        $setup->endSetup();
    }

    private function upgradeTo200(SchemaSetupInterface $setup)
    {
        $tables2delete = [
            'ids_CorreoArgentino_guia_generada',
            'ids_CorreoArgentino_codigo_postal',
            'ids_CorreoArgentino_sucursal',
            'Tiargsa_CorreoArgentino_sucursal',
            'Tiargsa_CorreoArgentino_codigo_postal',
            'Tiargsa_CorreoArgentino_guia_generada'
        ];

        foreach ($tables2delete as $table) {
            if ($setup->tableExists($table)) {
                $setup->getConnection()->dropTable($table);
            }
        }

        $tables2rename = [
            [
                'from' => 'ids_CorreoArgentino_zona',
                'to' => 'Tiargsa_CorreoArgentino_zona'
            ],
            [
                'from' => 'ids_CorreoArgentino_tarifa',
                'to' => 'Tiargsa_CorreoArgentino_tarifa'
            ]
        ];

        foreach ($tables2rename as $table) {
            if ($setup->tableExists($table['from'])) {
                $setup->getConnection()->renameTable($table['from'], $table['to']);
            }
        }

        /*
         * Creates Tiargsa_CorreoArgentino_zona table if not exist
         */
        if (!$setup->tableExists('Tiargsa_CorreoArgentino_zona')) {
            $TiargsacorreoZona = $setup->getConnection()
                ->newTable($setup->getTable('Tiargsa_CorreoArgentino_zona'))
                ->addColumn(
                    ZoneInterface::ZONE_ID,
                    Table::TYPE_SMALLINT,
                    6,
                    ['identity' => true, 'nullable' => false, 'primary' => true]
                )
                ->addColumn(ZoneInterface::NAME, Table::TYPE_TEXT, 40, ['nullable' => false]);

            $setup->getConnection()->createTable($TiargsacorreoZona);
        }

        /*
         * Creates Tiargsa_CorreoArgentino_tarifa table if not exist
         */
        if (!$setup->tableExists("Tiargsa_CorreoArgentino_tarifa")) {
            $TiargsacorreoTarifa = $setup->getConnection()
                ->newTable($setup->getTable('Tiargsa_CorreoArgentino_tarifa'))
                ->addColumn(
                    RateInterface::RATE_ID,
                    Table::TYPE_SMALLINT,
                    6,
                    ['identity' => true, 'nullable' => false, 'primary' => true]
                )
                ->addColumn(
                    RateInterface::RANGE,
                    Table::TYPE_DECIMAL,
                    '10,2',
                    ['nullable' => false]
                )
                ->addColumn(
                    RateInterface::STANDARD_VALUE,
                    Table::TYPE_DECIMAL,
                    '10,2',
                    ['nullable' => false]
                )
                ->addColumn(
                    RateInterface::PICKUP_VALUE,
                    Table::TYPE_DECIMAL,
                    '10,2',
                    ['nullable' => true, 'default' => null]
                )
                ->addColumn(
                    RateInterface::PRIORITY_VALUE,
                    Table::TYPE_DECIMAL,
                    '10,2',
                    ['nullable' => true, 'default' => null]
                )
                ->addColumn(
                    ZoneInterface::ZONE_ID,
                    Table::TYPE_SMALLINT,
                    6,
                    ['nullable' => false]
                )
                ->addIndex(
                    $setup->getIdxName('Tiargsa_CorreoArgentino_tarifa', [RateInterface::ZONE_ID]),
                    [RateInterface::ZONE_ID]
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'Tiargsa_CorreoArgentino_tarifa',
                        RateInterface::ZONE_ID,
                        'Tiargsa_CorreoArgentino_zona',
                        ZoneInterface::ZONE_ID
                    ),
                    RateInterface::ZONE_ID,
                    'Tiargsa_CorreoArgentino_zona',
                    ZoneInterface::ZONE_ID,
                    Table::ACTION_CASCADE
                );

            $setup->getConnection()->createTable($TiargsacorreoTarifa);
        }

        if (!$setup->getConnection()->tableColumnExists('quote', 'codigo_sucursal_correoargentino')) {
            $setup->getConnection()->addColumn('quote', 'codigo_sucursal_correoargentino', [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Codigo de sucursal correo',
                'default' => null
            ]);
        }
    }
}

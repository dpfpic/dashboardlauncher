<?php

namespace OCA\DashboardLauncher\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version1000Date20260728000000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('dashboardlauncher_buttons')) {
            $table = $schema->createTable('dashboardlauncher_buttons');

            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('ordre', 'integer', [
                'notnull' => true,
                'default' => 10,
            ]);
            $table->addColumn('titre', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('icone', 'string', [
                'notnull' => true,
                'length' => 255,
                'default' => 'icon-link',
            ]);
            $table->addColumn('route', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('groupes', 'text', [
                'notnull' => false,
                'comment' => 'JSON encoded array of allowed Nextcloud groups',
            ]);
            $table->addColumn('actif', 'boolean', [
                'notnull' => true,
                'default' => true,
            ]);
            $table->addColumn('taille', 'string', [
                'notnull' => true,
                'length' => 20,
                'default' => 'medium',
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['ordre'], 'dl_buttons_order_idx');
        }

        return $schema;
    }
}

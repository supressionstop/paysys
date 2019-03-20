<?php

use Phinx\Migration\AbstractMigration;

class InitCreateTables extends AbstractMigration
{
    /*
     * Create tables.
     */
    public function up()
    {
        $this->table('transactions')
            ->create();
        $this->table('transaction_details', ['id' => 'transaction_id'])
            ->create();
        $this->table('transaction_statuses_history')
            ->create();
    }

    /*
     * Drop tables.
     */
    public function down()
    {
        $this->dropTable('transactions');
        $this->dropTable('transaction_details');
        $this->dropTable('transaction_statuses_history');
    }
}

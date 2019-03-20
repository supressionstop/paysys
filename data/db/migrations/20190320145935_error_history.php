<?php

use Phinx\Migration\AbstractMigration;

class ErrorHistory extends AbstractMigration
{
    public function up()
    {
        $transactions_status_history = $this->table('transaction_statuses_history');

        $transactions_status_history
            ->addColumn('error_code', 'string', ['null' => true])
            ->addColumn('error_description', 'text', ['null' => true])
            ->update();
    }

    public function down()
    {
        $transactions_status_history = $this->table('transaction_statuses_history');

        $transactions_status_history
            ->removeColumn('error_code')
            ->removeColumn('error_description')
            ->update();
    }
}

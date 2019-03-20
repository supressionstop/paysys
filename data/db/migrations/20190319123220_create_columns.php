<?php

use Phinx\Migration\AbstractMigration;

class CreateColumns extends AbstractMigration
{
    public function up()
    {
        $transactions = $this->table('transactions');
        $transactions_details = $this->table('transaction_details');
        $transactions_status_history = $this->table('transaction_statuses_history');

        $transactions
            ->addColumn('status', 'enum', ['values' => ['init','external','delivered','awaiting_callback','received','success','decline']])
            ->addColumn('status_timestamp', 'timestamp',['default' => 'CURRENT_TIMESTAMP'])
            ->update();

        $transactions_details
            ->addColumn('amount', 'integer')
            ->addColumn('currency','string', ['limit' => 3])
            ->addColumn('id_foreign_system', 'string')
            ->update();

        $transactions_status_history
            ->addColumn('transaction_id', 'integer')
            ->addColumn('change_time','timestamp')
            ->addColumn('from', 'enum', ['values' => ['init','external','delivered','awaiting_callback','received','success','decline'], 'null' => true])
            ->addColumn('to','enum', ['values' => ['init','external','delivered','awaiting_callback','received','success','decline']])
            ->update();
    }

    public function down()
    {
        $transactions = $this->table('transactions');
        $transactions_details = $this->table('transaction_details');
        $transactions_status_history = $this->table('transaction_statuses_history');

        $transactions
            ->removeColumn('status')
            ->removeColumn('status_timestamp')
            ->update();

        $transactions_details
            ->removeColumn('amount')
            ->removeColumn('currency')
            ->removeColumn('id_foreign_system')
            ->update();

        $transactions_status_history
            ->removeColumn('change_time')
            ->removeColumn('from')
            ->removeColumn('to')
            ->update();
    }
}

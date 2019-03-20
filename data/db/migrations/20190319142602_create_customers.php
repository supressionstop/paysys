<?php

use Phinx\Migration\AbstractMigration;

class CreateCustomers extends AbstractMigration
{
    public function up()
    {
        $this->table('customers')
            ->addColumn('email','string', ['limit' => 128])
            ->addColumn('birthday','date')
            ->addColumn('language', 'enum', ['values' =>['en','tr','cn','de','no','jp']])
            ->addColumn('billing_first_name','string', ['limit' => 64])
            ->addColumn('billing_last_name','string', ['limit' => 64])
            ->addColumn('billing_address_1','string', ['limit' => 128])
            ->addColumn('billing_city','string', ['limit' => 64])
            ->addColumn('billing_postcode','string', ['limit' => 16])
            ->addColumn('billing_country','string', ['limit' => 2])
            ->addColumn('payment_method','string', ['limit' => 32])
            ->addColumn('card_number','string', ['limit' => 16])
            ->addColumn('cvv','integer')
            ->addColumn('expiry_month','integer')
            ->addColumn('expiry_year','integer')
            ->create();
    }

    public function down()
    {
        $this->dropTable('customers');
    }
}

<?php

use Phinx\Seed\AbstractSeed;

class CustomerTruncate extends AbstractSeed
{
    public function run()
    {
        $customers = $this->table('customers');
        $customers->truncate();
    }
}

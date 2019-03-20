<?php

use Phinx\Seed\AbstractSeed;

class CustomerSeed extends AbstractSeed
{
    public function run()
    {
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'email' => $faker->email,
                'birthday'=> $faker->date($format = 'Y-m-d', $max = 'now'),
                'language'=>rand(1,6),
                'billing_first_name'=>$faker->firstName,
                'billing_last_name'=>$faker->lastName,
                'billing_address_1'=>$faker->streetAddress,
                'billing_city'=>$faker->city,
                'billing_postcode'=>$faker->postcode,
                'billing_country'=>$faker->countryCode,
                'payment_method'=>$faker->word,
                'card_number'=>$faker->creditCardNumber,
                'cvv'=>rand(100,999),
                'expiry_month'=>rand(1,12),
                'expiry_year'=>rand(1,12)
                ];
        }

        $this->table('customers')->insert($data)->save();
    }
}

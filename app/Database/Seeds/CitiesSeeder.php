<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CitiesSeeder extends Seeder
{
    public function run()
    {
        // Top 10 cities in Maharashtra
        $citiesData = [
            ['state' => 'Maharashtra', 'name' => 'Mumbai'],
            ['state' => 'Maharashtra', 'name' => 'Pune'],
            ['state' => 'Maharashtra', 'name' => 'Nagpur'],
            ['state' => 'Maharashtra', 'name' => 'Thane'],
            ['state' => 'Maharashtra', 'name' => 'Nashik'],
            ['state' => 'Maharashtra', 'name' => 'Aurangabad'],
            ['state' => 'Maharashtra', 'name' => 'Solapur'],
            ['state' => 'Maharashtra', 'name' => 'Amravati'],
            ['state' => 'Maharashtra', 'name' => 'Kolhapur'],
            ['state' => 'Maharashtra', 'name' => 'Akola'],
        ];
        // Insert data into the 'cities' table
        $this->db->table('cities')->insertBatch($citiesData);
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiceMasterSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name' => 'Shutter',
            'number_of_services' => 3,
            'duration' => '365 days',
            'charges' => 300,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Insert the data into the database
        $this->db->table('service_master')->insert($data);
    }
}

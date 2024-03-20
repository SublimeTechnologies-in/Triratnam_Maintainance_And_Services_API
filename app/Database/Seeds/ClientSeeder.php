<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use Faker\Factory;

class ClientSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $password = md5('12345'); // Replace 'password123' with the desired password
            $name = $faker->name;
            $username = $faker->email;

            $this->db->table('credentials')->insert(
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => $password,
                    'user_type' => 'client',
                    'is_active' => rand(0, 1),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            );

            $type = ['company', 'consultancy'];
            $data = [
                'credential_id' => $this->db->insertID(), // Assuming you have credentials with IDs from 1 to 5
                'name' => $name,
                'contact' => $faker->phoneNumber,
                'email' => $username,
                'address' => $faker->address,
                'website' => $faker->domainName,
                'overview' => $faker->paragraph,
                'type' => $type[rand(0,1)],
                'city_id' => rand(1, 10),
                'ref_id' => rand(1, 10),
                'alternate_contact' => $faker->phoneNumber,
                'id_proof_link' => $faker->imageUrl(),
                'is_verified' => rand(0, 1),
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ];

            $this->db->table('clients')->insert($data);
        }
    }
}

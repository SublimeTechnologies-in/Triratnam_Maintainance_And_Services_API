<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use Faker\Factory;

class ExecutivesSeeder extends Seeder
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
                    'user_type' => 'executive',
                    'is_active' => rand(0, 1),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            );
            $data = [
                'credential_id' => $this->db->insertID(), // Assuming you have credentials with IDs from 1 to 5
                'name' => $name,
                'contact' => $faker->phoneNumber,
                "alternate_contact" => $faker->phoneNumber,
                "date_of_birth" => $faker->date,
                'email' => $username,
                'address' => $faker->address,
                'alternative_address' => $faker->address,
                'passport_size_photo' => $faker->imageUrl(),
                'id_proof_link' => $faker->imageUrl(),
                'address_proof_link' => $faker->imageUrl(),
                'additional_doc_link' => $faker->imageUrl(),
                'city_id' => rand(1, 10),
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ];

            $this->db->table('executives')->insert($data);
        }
    }
}

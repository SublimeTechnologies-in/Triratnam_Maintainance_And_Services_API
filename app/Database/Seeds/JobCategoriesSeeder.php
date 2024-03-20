<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JobCategoriesSeeder extends Seeder
{
    public function run()
    {
        // Job categories with Font Awesome icons
        $categoriesData = [
            ['name' => 'Software Development', 'icon' => 'fas fa-code'],
            ['name' => 'Web Development', 'icon' => 'fas fa-globe'],
            ['name' => 'Mobile App Development', 'icon' => 'fas fa-mobile-alt'],
            ['name' => 'Data Science', 'icon' => 'fas fa-chart-bar'],
            ['name' => 'UI/UX Design', 'icon' => 'fas fa-paint-brush'],
            ['name' => 'Digital Marketing', 'icon' => 'fas fa-bullhorn'],
            ['name' => 'Finance', 'icon' => 'fas fa-money-bill'],
            ['name' => 'Human Resources', 'icon' => 'fas fa-users'],
            ['name' => 'Sales', 'icon' => 'fas fa-handshake'],
            ['name' => 'Customer Support', 'icon' => 'fas fa-headset'],
            ['name' => 'Healthcare', 'icon' => 'fas fa-medkit'],
            ['name' => 'Education', 'icon' => 'fas fa-graduation-cap'],
            ['name' => 'Manufacturing', 'icon' => 'fas fa-industry'],
            ['name' => 'Engineering', 'icon' => 'fas fa-cogs'],
            ['name' => 'Retail', 'icon' => 'fas fa-shopping-cart'],
            ['name' => 'Hospitality', 'icon' => 'fas fa-hotel'],
            ['name' => 'Agriculture', 'icon' => 'fas fa-tractor'],
            ['name' => 'Construction', 'icon' => 'fas fa-hard-hat'],
            ['name' => 'Transportation', 'icon' => 'fas fa-shipping-fast'],
            ['name' => 'Media and Entertainment', 'icon' => 'fas fa-film'],
        ];

        // Insert data into the 'job_categories' table
        $this->db->table('job_categories')->insertBatch($categoriesData);
    }
}

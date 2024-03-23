<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageColumnToCustomer extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customers', ['image' => ['type' => 'text', 'after' => 'address']]);
    }

    public function down()
    {
        //
    }
}

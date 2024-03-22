<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServiceMasterTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'number_of_services' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'duration' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'charges' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('service_master');
    }

    public function down()
    {
        $this->forge->dropTable('service_master');
    }
}

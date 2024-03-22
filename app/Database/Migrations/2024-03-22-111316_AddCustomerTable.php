<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shop_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'owner_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'whatsapp_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'address' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'user_id' => [
                'type'       => 'int',
                'unsigned'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('customers');
    }

    public function down()
    {
        $this->forge->dropTable('customers');
    }
}

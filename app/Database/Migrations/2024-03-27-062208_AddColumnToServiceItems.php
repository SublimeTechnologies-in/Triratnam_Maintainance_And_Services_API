<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnToServiceItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn(
            'service_item',
            [
                'servicing_date' => ['type' => "date", 'default' => null],
                'remark' => ['type' => "TEXT"],
            ]
        );
    }

    public function down()
    {
        //
    }
}

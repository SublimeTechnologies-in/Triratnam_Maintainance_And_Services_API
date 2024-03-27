<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MarkAsCompleteLeadFollowUop extends Migration
{
    public function up()
    {
        $this->forge->addColumn('lead_followups', ['is_completed' => ['type' => 'date', 'default' => null]]);
    }

    public function down()
    {
        //
    }
}

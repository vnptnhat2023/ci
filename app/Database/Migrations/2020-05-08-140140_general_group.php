<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralGroup extends Migration
{
	public function up()
	{
		$fields = [
      'id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true,
        'auto_increment' => true
      ],
      'title' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
				'null' => false
      ],
      'slug' => [
        'type' => 'VARCHAR',
        'constraint' => 48,
				'null' => false,
				'unique' => true
      ],
      'status' => [
        'type' => 'ENUM("active", "inactive")',
        'default' => 'active'
      ],
      'created_at'=> [
        'type' => 'DATE',
        'null' => true,
        'default' => null
      ],
      'updated_at'=> [
        'type' => 'DATE',
        'null' => true,
        'default' => null
      ],
      'deleted_at'=> [
        'type' => 'DATE',
        'null' => true,
        'default' => null
      ]
		];

		$this->forge->addField($fields);

		$this->forge->addKey( 'id', true );

    $this->forge->createTable( 'general_group', true );
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable( 'general_group', true );
	}
}

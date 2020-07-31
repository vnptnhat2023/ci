<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Extension extends Migration
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
      'author' => [
				'type' => 'VARCHAR',
				'constraint' => 32,
        'default' => 'unknown',
        'null' => false,
			],
			'contact' => [
				'type' => 'VARCHAR',
				'constraint' => 128,
        'default' => 'unknown',
        'null' => false,
			],
      'category_name' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
				'default' => 'unknown',
				'null' => false
      ],
      'category_slug' => [# item_id
        'type' => 'VARCHAR',
        'constraint' => 48,
				'default' => 'unknown',
				'null' => false
      ],
      'description' => [
				'type' => 'VARCHAR',
				'constraint' => 512,
        'null' => true,
        'default' => null
			],
			'title' => [
				'type' => 'VARCHAR',
				'constraint' => 128,
        'null' => false
			],
			'slug' => [
				'type' => 'VARCHAR',
				'constraint' => 64,
				'null' => false,
				'comment' => 'CamelcaseWithFirstChar'
			],
			'version' => [
				'type' => 'VARCHAR',
				'constraint' => 11,
        'null' => false,
        'default' => '1'
			],
			'status' => [
        'type' => 'ENUM("disable","enable")',
        'null' => false,
        'default' => 'enable'
			],
			'created_at' => [
        'type' => 'DATE',
        'null' => true,
        'default' => null
      ],
      'updated_at' => [
        'type' => 'DATE',
        'null' => true,
        'default' => null
      ],
      'deleted_at' => [
        'type' => 'DATE',
        'null' => true,
        'default' => null
      ]
		];

		$this->forge->addField($fields);

		$this->forge->addKey( 'id', true );

		$this->forge->addKey( 'title', false, true );

		$this->forge->addKey( 'slug', false, true );

    $this->forge->createTable( 'extension', true );
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable( 'extension', true );
	}
}

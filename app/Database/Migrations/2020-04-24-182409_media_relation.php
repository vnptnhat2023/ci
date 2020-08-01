<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MediaRelation extends Migration
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

      'category_id' => [
        'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => false,
        'default' => 0
			],

      'page_id' => [
        'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => false,
        'default' => 0
			],

      'post_id' => [
        'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => false,
        'default' => 0
			],

      'media_id' => [
        'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => false,
				'default' => 0
			],

			'user_id' => [
        'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => false,
				'default' => 0
      ]
		];

		$this->forge
		->addField( $fields )
		->addKey( 'id', true )
		->createTable( 'media_relation', true );
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable( 'media_relation', true );
	}
}

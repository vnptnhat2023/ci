<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralRelation extends Migration
{

	public function up()
	{

		$fields = [

      'id' => [
        'type' => 'BIGINT',
        'unsigned' => true,
        'auto_increment' => true
			],

      'name' => [
				'type' => 'CHAR',
				'constraint' => 2,
        'default' => 'po',
        'null' => false,
				'comment' => 'ca,pa,po,gg,...'
			],

			'name_id' => [
				'type' => 'BIGINT',
				'unsigned' => true,
				'null' => false,
				'default' => 0,
				'comment' => 'po: post, ca: category, pa: page'
			],

      'ggid' => [
        'type' => 'BIGINT',
				'unsigned' => true,
				'null' => false,
        'default' => 0,
				'comment' => 'General group id'
			],

      'giid' => [# item_id
        'type' => 'BIGINT',
				'unsigned' => true,
				'null' => false,
        'default' => 0,
				'comment' => 'General item Id'
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

		$this->forge
		->addField( $fields )
		->addKey( 'id', true )
		->createTable( 'general_relation', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'general_relation', true );
	}
}

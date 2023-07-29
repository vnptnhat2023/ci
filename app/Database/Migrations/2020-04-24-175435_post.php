<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Post extends Migration
{

	public function up()
	{

		$fields = [

			'id' => [
				'type' => 'BIGINT',
				'unsigned' => true,
				'auto_increment' => true
			],

			'title' => [
				'type' => 'VARCHAR',
				'constraint' => 128,
				'null' => false,
				'default' => 'unknown'
			],

			'slug' => [
				'type' => 'VARCHAR',
				'constraint' => 192,
				'null' => false,
				'default' => 'unknown'
			],

			'excerpt' => [
				'type' => 'VARCHAR',
				'constraint' => 512,
				'null' => true,
				'default' => null
			],

			'content' => [
				'type' => 'TEXT',
				'null' => false,
				'default' => ''
			],

			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 64,
				'null' => false,
				'default' => 'category'
			],

			'name_id' => [
				'type' => 'BIGINT',
				'unsigned' => true,
				'null' => false,
				'default' => 0
			],

			'user_id' => [
				'type' => 'BIGINT',
				'unsigned' => true,
				'null' => false,
				'default' => 0
			],

			'media_relation_id' => [
				'type' => 'BIGINT',
				'unsigned' => true,
				'null' => false,
				'default' => 0
			],

			'status' => [
				'type' => 'ENUM("publish","private","draff")',
				'null' => false,
				'default' => 'draff'
			],

			'typeof' => [
				'type' => 'VARCHAR',
				'constraint' => 64,
				'null' => false,
				'default' => 'text'
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
		->addKey( 'slug', false, true )
		->createTable( 'post', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'post', true );
	}
}
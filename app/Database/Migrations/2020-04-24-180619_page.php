<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Page extends Migration
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

      'icon' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
        'null' => true
			],

      'content' => [
        'type' => 'TEXT',
        'null' => true,
				'default' => null
			],

      'advanced_content' => [
        'type' => 'TEXT',
				'null' => true,
				'default' => null
			],

      'advanced_position' => [
        'type' => 'ENUM("top","bottom")',
        'default' => 'bottom'
			],

      'parent_id' => [
        'type' => 'INT',
				'unsigned' => true,
				'null' => false,
        'default' => 0
			],

      'status' => [
				'type' => 'ENUM("publish","private","draff")',
				'null' => false,
        'default' => 'draff'
			],

      'sort' => [
        'type' => 'INT',
        'unsigned' => true,
        'default' => 0
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

		$this->forge
		->addField( $fields )
		->addKey( 'id', true )
		->createTable( 'page', true );

		# $this->forge->addKey('slug');
    # $this->forge->addKey('parent_id');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable( 'page', true );
	}
}

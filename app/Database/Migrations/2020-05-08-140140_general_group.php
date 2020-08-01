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
				'null' => false,
				'default' => 'unknown'
			],

      'slug' => [
        'type' => 'VARCHAR',
        'constraint' => 48,
				'null' => false,
				'default' => 'unknown'
			],

      'status' => [
				'type' => 'ENUM("active", "inactive")',
				'null' => false,
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

		$this->forge
		->addField( $fields )
		->addKey( 'id', true )
		->addKey( 'slug', false, true )
		->createTable( 'general_group', true );
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable( 'general_group', true );
	}
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserGroup extends Seeder
{
  public function run()
  {
    $data = [
      [
        'name' => '1st Group',
				'permission' => '["all"]',
				'role' => 'administrator'
      ],
      [
        'name' => 'guest',
				'permission' => '["null"]',
				'role' => 'guest'
      ]
    ];

		$this->db
		->table( 'user_group' )
		->insertBatch( $data );
  }
}
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
        'permission' => '["all"]'
      ],
      [
        'name' => 'guest',
        'permission' => '["null"]'
      ]
    ];

		$this->db
		->table( 'user_group' )
		->insertBatch( $data );
  }
}
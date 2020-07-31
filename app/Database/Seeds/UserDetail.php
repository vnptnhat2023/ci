<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserDetail extends Seeder
{
  public function run()
  {
    $data = [
      [
        'user_id' => '1',
        'gender' => 'male'
      ],
      [
        'user_id' => '2',
        'gender' => 'male'
      ]
    ];

    $this->db->table( 'user_detail' )->insertBatch( $data );
  }
}
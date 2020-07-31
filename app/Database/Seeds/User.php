<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class User extends Seeder
{
  public function run()
  {
    $data = [
      [
        'group_id' => '1',
        'username' => 'administrator',
        'email'    => 'webmaster@local.host',
        'password' => '$2y$12$Zylna4hIiLakeIYdnRqg2.gK9eMWj4Y3cO3mAc5YGxU9ADurwR9Vy',
        'status' => 'active'
      ],
      [
        'group_id' => '2',
        'username' => 'member',
        'email'    => 'member@local.host',
        'password' => '$2y$12$Zylna4hIiLakeIYdnRqg2.gK9eMWj4Y3cO3mAc5YGxU9ADurwR9Vy',
        'status' => 'active'
      ]
    ];

    $this->db->table( 'user' )->insertBatch( $data );
  }
}
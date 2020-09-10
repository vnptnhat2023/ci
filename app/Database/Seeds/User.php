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
        'password' => '$2y$10$vITbJQyusQXmr3ePgbHlG.roFKgUr0Bklra8VW.oHNuN4w/MOK1um',
        'status' => 'active'
      ],
      [
        'group_id' => '2',
        'username' => 'member',
        'email'    => 'member@local.host',
        'password' => '$2y$10$O1RBHXGvTUb6dzos5MJZM.KNjIB28oxh5hP.8cDCkyT6tYJFQHuU2',
        'status' => 'active'
      ]
    ];

    $this->db->table( 'user' )->insertBatch( $data );
  }
}
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AddPage extends Seeder
{
  public function run()
  {
    $data = [

			'title' => 'first page',

			'slug' => 'first-page',

			'icon' => null,

			'content' => null,

			'advanced_content' => null,

			'advanced_position' => 'top',

			'parent_id' => 0,

			'status' => 'publish',

			'sort' => 0,

      'created_at' => date('Y-m-d')
    ];

		$this->db
		->table( 'page' )
		->insert( $data );
  }
}
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GeneralSetting extends Seeder
{
  public function run()
  {
    $data = [
      [
        'setting_name' => 'admin_user',
        'setting_value' => '{"record":"10","order_by":"id","direction":"ASC"}'
			],

      [
        'setting_name' => 'admin_post',
        'setting_value' => '{"record":"10","order_by":"id","direction":"DESC"}'
      ]
    ];

    $this->db->table( 'general_setting' )->insertBatch( $data );
  }
}
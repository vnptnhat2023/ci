<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Seed extends Seeder
{
  public function run()
  {
    $this->call('User');
    $this->call('UserDetail');
    $this->call('UserGroup');
    $this->call('GeneralSetting');
    $this->call('Page');
  }
}
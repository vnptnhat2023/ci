<?php

namespace App\Entities;

use CodeIgniter\Entity;

class Setting extends Entity
{
  protected $setting_value;

  protected $casts = [
    'setting_value' => 'json'
  ];

  protected function setSettingValue($json)
  {
    $this->setting_value = $json;
  }
}
<?php

namespace BAPI\Entities\User;

class Group extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [];

  protected function setName(string $username)
  {
    $this->attributes['name'] = mb_strtolower($username);
  }

  protected function setPermission($data)
  {
    $this->attributes['permission'] = (array) $data;
  }
}
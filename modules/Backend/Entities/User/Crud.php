<?php

namespace BAPI\Entities\User;

class Crud extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [
    'user_username' => 'username',
    'user_password' => 'password',
    'user_email' => 'email',
    'user_status' => 'status'
  ];

  public function createFillable() : \Codeigniter\Entity
  {
    $this->attributes['status'] ??= config('\BAPI\Config\User')->getSetting('db.fill.status');

    return $this;
  }

  public function setUsername(string $username)
  {
    $this->attributes['username'] = mb_strtolower($username);
  }

  public function setEmail(string $email)
  {
    $this->attributes['email'] = mb_strtolower($email);
  }

}
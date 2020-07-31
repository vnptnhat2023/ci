<?php

namespace BAPI\Entities\User;

class Profile extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [];

  public function setEmail(string $email)
  {
    $this->attributes['email'] = mb_strtolower($email);
  }

  public function setFullname(string $fullname)
  {
    $this->attributes['fullname'] = mb_strtolower($fullname);
  }

}
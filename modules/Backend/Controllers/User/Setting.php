<?php

namespace BAPI\Controllers\User;

use \BAPI\Controllers\Ext\SettingResource;

class Setting extends SettingResource
{
  protected string $settingName = 'admin_user';

  # ==========================================================
  public function index()
	{
    return $this->showEXT($this->settingName);
  }

  # ==========================================================
  public function create()
  {
    return $this->createEXT();
  }

  # ==========================================================
  public function delete($id = null)
  {
    return $this->deleteEXT( $this->settingName, $id === 'purge'  );
  }

  # ==========================================================
  public function update($id = null)
  {
    return $this->updateEXT( $this->settingName, $id === 'undel' );
  }

}
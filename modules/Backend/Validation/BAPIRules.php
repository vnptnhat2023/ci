<?php namespace BAPI\Validation;

class BAPIRules
{
  public function inPermission(array $data) : bool
  {
    if ( empty( $data ) ) {
			return false;
		}

		$isFalse = true;

    $permission = ( new \BAPI\Config\User() )->getSetting('permission');

    foreach ( $data as $value ) {
      if ( ! in_array( $value, $permission, true ) ) {
				$isFalse = false;
        break;
			}
    }

    return $isFalse;
  }

	/**
	 * Already exist in filesystem_helper::set_realpath()
	 */
  public function isPath(string $str) : bool
  {
    return (bool) preg_match('/^[a-z0-9\_\-\\/]+$/i', $str);
  }
}
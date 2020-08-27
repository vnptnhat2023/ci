<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Session;

interface SessionFacadeInterface
{
	public function get ( string $key = null );

	public function has (string $key): bool;

	public function destroy () : void;

	public function getFlashdata ( string $key = null );

	public function set ( $data, $value = null ) : void;
}
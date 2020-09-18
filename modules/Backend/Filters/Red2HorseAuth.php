<?php
namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;

class Red2HorseAuth implements FilterInterface
{

	private function handleArg( $arguments )
	{
		$data = [];

		foreach ( $arguments as $value )
		{
			$t = explode( '.', $value );
			$f = $t[ array_key_first( $t ) ];
			$l = $t[ array_key_last( $t ) ];

			if ( empty( $f ) || empty( $l ) || in_array( $l, $d[ $f ] ?? $data ) ) {
				continue;
			}

			$data[ $f ][] = $l;
		}

		return $data;
	}

  public function before( req $request, $arguments = null )
  {
		$arguments = ! empty( $arguments ) ? $this->handleArg( $arguments ) : [];
		$hasPermission = Services::Red2HorseAuth()->withPermission( $arguments );

    if ( false === $hasPermission ) {
      throw PageNotFoundException::forPageNotFound();
    }
  }

  public function after( req $request, res $response, $arguments = null )
  {
  }

}

<?php
namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;

class Red2HorseAuth implements FilterInterface
{

	private function test( $arguments )
	{
		$d = [];

		foreach ( $arguments as $value )
		{
			$t = explode( '.', $value );
			$f = $t[ array_key_first( $t ) ];
			$l = $t[ array_key_last( $t ) ];

			if ( empty( $f ) || empty( $l ) || in_array( $l, $d[ $f ] ?? $d ) ) {
				continue;
			}

			$d[ $f ][] = $l;
		}

		return $d;
	}

  public function before( req $request, $arguments = null )
  {
		if ( ! empty( $arguments ) )
		{
			$d = $this->test( $arguments );
			print_r($d);
		}
		else
		{
			$arguments = [];
		}

		die;

    if ( ! Services::Red2HorseAuth()->withPermission( $arguments ?: [] ) ) {
      throw PageNotFoundException::forPageNotFound();
    }
  }

  public function after( req $request, res $response, $arguments = null )
  {
  }

}

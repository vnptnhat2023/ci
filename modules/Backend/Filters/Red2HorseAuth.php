<?php
namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;

class Red2HorseAuth implements FilterInterface
{

	private function handleArg( array $arguments )
	{
		helper('text');
		$data = [];

		foreach ( $arguments as $value )
		{
			$value = reduce_multiples( (string) $value, '|', true );
			$t = explode( '|', $value );
			$f = $t[ array_key_first( $t ) ];
			$l = $t[ array_key_last( $t ) ];

			if ( empty( $f ) || empty( $l ) || in_array( $l, $d[ $f ] ?? $data ) ) {
				continue;
			}

			$data[ $f ][] = $l;
		}

		// die(var_dump($data));
		return $data;
	}

  public function before( req $request, $arguments = null )
  {
		// die(var_dump($arguments));
		// $args = $this->handleArg( (array) $arguments );

    if ( false === Services::Red2HorseAuth()->withPermission( ( array ) $arguments ) ) {
      throw PageNotFoundException::forPageNotFound();
    }
  }

  public function after( req $request, res $response, $arguments = null )
  {
  }

}

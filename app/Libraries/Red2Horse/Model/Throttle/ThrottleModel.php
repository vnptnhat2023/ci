<?php

declare( strict_types = 1 );
namespace Red2Horse\Model\Throttle;

use Red2Horse\Exception\ErrorMethodException;
use Red2Horse\Mixins\Classes\Sql\Model;

use function Red2Horse\Mixins\Functions\Sql\getField;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ThrottleModel extends Model
{
	public 			string 		$table 			= 'throttle';
    public			array 		$allowedFields 	= [ 'id', 'ip', 'attempt', 'created_at', 'updated_at' ];
    public			array 		$createdAt 		= [ 'created_at' => 'Y-m-d H:i:s' ];
    public			array 		$updatedAt  	= [ 'updated_at' => 'Y-m-d H:i:s' ];

    public function __construct ()
    {
        $this->init();
    }

    public function throttleFetch ( array $props )
    {
        $where = [ getField( 'ip', 'throttle' ) => $props[ 'ip' ] ];

        $rowAttempt = ( array ) $this
            ->select( [ getField( 'attempt', 'throttle' ) ] )

            ->andWhere( $where, function ( $filter ) {
                $noExplodeData = [ getField( 'ip', 'throttle' ), getField( 'id', 'throttle' ) ];
                $filter->setNoExplode( 'kv', $noExplodeData );
            } )
            
            ->fetchFirst();

        if ( ! isset( $rowAttempt[ 'attempt' ] ) )
        {
            if ( ! $this->throttleAdd( $props ) )
            {
                throw new ErrorMethodException( 'Cannot throttle attempt' );
            }

            $rowAttempt[ 'attempt' ] = 1;
        }

        return $rowAttempt;
    }
    
    private function throttleAdd ( array $props ) : bool
    {
        $set = [
            getField( 'attempt', 'throttle' )  => $props[ 'attempt' ], 
            getField( 'ip', 'throttle' )       => $props[ 'ip' ]
        ];
        $addFilter = function( $filter ) { $filter->useExplodeCombine = false; };
        $added = $this->add( $set, $addFilter );

        return ( bool ) $added;
    }

    public function throttleUpdate ( array $props, bool $reset = false ) : bool
    {
        $set = [
            getField( 'attempt', 'throttle' )   => $reset ? 0 : $props[ 'attempt' ], 
            getField( 'ip', 'throttle' )        => $props[ 'ip' ]
        ];
        $where = [
            getField( 'ip', 'throttle' )        => $props[ 'ip' ]
        ];
        $queryFilter = function ( $filter ) {
            $filter->useExplodeCombine = false;
        };
        
        $query = $this
            ->set( $set, $queryFilter )
            ->andWhere( $where, $queryFilter )
            ->update();

        return ( bool ) $query;
    }
}
<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle;

use Red2Horse\Exception\ErrorMethodException;
use Red2Horse\Exception\ErrorValidationException;
use Red2Horse\Facade\Cache\CacheFacade;
use Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface\ThrottleAdapterInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Instance\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ThrottleCache implements ThrottleAdapterInterface
{
    use TraitSingleton;

    private         array           $props;
    private         CacheFacade     $cache;
    private         string          $attemptKey     = 'throttle_attempt';
    private         int             $throttleAttempt;

    public function __construct () {}

    public function init ( array $props ) : int
    {
        $this->props = $props;
        $this->cache = getComponents( 'cache' );

        if ( ! isset( $this->throttleAttempt ) )
        {
            $this->throttleAttempt = $this->fetch();
        }

        return $this->throttleAttempt;
    }

    private function fetch () : int
    {
        if ( is_int( $attempt = $this->get() ) )
        {
            return $attempt;
        }

        $this->handle( [ $this->attemptKey => 1 ] );

        return 1;
    }

    public function isSupported () : bool
    {
        return $this->cache->isSupported();
    }

    public function increment () : bool
    {
        return $this->handle( [ $this->attemptKey => $this->props[ 'attempt' ] ] );
    }

    public function decrement () : bool
    {
        return $this->handle( [ $this->attemptKey => $this->props[ 'attempt' ] ] );
    }

    public function cleanup () : void
    {
        $this->handle( [ $this->attemptKey => 0 ] );
    }

    public function delete () : bool
    {
        return $this->cache->delete( $this->props[ 'cacheName' ] );
    }

    /**
     * @throws ErrorMethodException
     */
    private function handle ( array $data ) : bool
    {
        $cached = $this->cache->set( $this->props[ 'cacheName' ], $data, $this->props[ 'timeout' ] );
        
        if ( ! $cached )
        {
            throw new ErrorMethodException( 'Cannot set throttle attempt' );
        }

        return $cached;
    }

    /** 
     * @throws ErrorValidationException
     * @return false|int
     */
    private function get ()
    {
        $attempt = $this->cache->get( $this->props[ 'cacheName' ] );

        if ( isset( $attempt[ $this->attemptKey ] ) && is_numeric( $attempt[ $this->attemptKey ] ) )
        {
            $attempt = ( int ) $attempt[ $this->attemptKey ];

            if ( $attempt < 0 )
            {
                throw new ErrorValidationException( 'Invalid data format: "attempt"' );
            }

            return $attempt;
        }

        return false;
    }
}
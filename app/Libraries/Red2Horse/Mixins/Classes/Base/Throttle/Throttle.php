<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle;

use Red2Horse\Exception\ErrorPropertyException;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface\ThrottleInterface;
use Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface\ThrottleAdapterInterface;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\getInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Throttle implements ThrottleInterface
{
    use TraitSingleton;

    private     int     $attempt;
	private     int     $type;
	private     int     $typeAttempt;
    private     int     $typeLimit;
	private     int     $timeout;
    private     string  $cacheName;
    private     string  $currentAdapter;
    private     array   $adapters;

    private     bool    $adapterFetched = false;


    public function __construct ()
    {
        $this->selfInit();
    }

    private function selfInit () : void
    {
        $configThrottle         = getConfig( 'throttle' );
        $this->cacheName        = $configThrottle->cacheName;
        $this->adapters         = $configThrottle->adapters;
        $this->currentAdapter   = $configThrottle->currentAdapter;

        $this->reset();
    }

    private function adapterInit () : void
    {
        if ( ! $this->adapterFetched )
        {
            $this->attempt          = $this->adapterInstance()->init( $this->__toAssoc() );
            $this->adapterFetched   = true;
        }
    }

    public function isSupported () : bool
    {
        if ( ! getConfig( 'throttle' )->useThrottle )
        {
            return false;
        }

        $this->init();
        
        if ( ! $this->adapterInstance()->isSupported() )
        {
            return false;
        }

        return true;
    }

    private function adapterInstance () : ThrottleAdapterInterface
    {
        $adapterNamespace   = $this->adapters[ $this->currentAdapter ];
        $throttleAdapter    = getInstance( $adapterNamespace );
        return $throttleAdapter;
    }

	public function cleanup () : void
	{
        $this->adapterInstance()->cleanup();
        $this->reset();
	}

    private function setCurrentAdapter ( string $adapterName ) : void
    {
        $inArray = in_array( $adapterName, array_keys( $this->adapters ) );

        if ( $inArray )
        {
            $this->currentAdapter = $adapterName;
        }
    }
    
    private function getCurrentAdapter () : string
    {
        return $this->currentAdapter;
    }
    
	public function getAttempts () : int
	{
        return $this->attempt;
	}

	public function getTypes () : int
	{
        return $this->type;
	}

    private function typeHandle () : bool
    {
        /*
        attempt     = type * typeAttempt
        type        = attempt / typeAttempt ( { 11,...,14 % 5 } =   { ceil( [2] .1,.2, .3, .4) = 3.0 }, 
                                                                    { 16,...19 } % 5 = { ceil( [3] .1,.2, .3, .4 ) = 4.0 } )
        typeAttempt = attempt / type

        type < 2                            => attempt < 1 * typeAttempt
        type >= typeLimit                   => attempt =< type * typeAttempt
        type >=2 && type <= typeLimit       => 2 * type <= attempt =< type * typeAttempt
        */
        if ( $this->type < 2 && $this->attempt <= $this->typeAttempt )
        {
            $this->type = 1;
        }
        else if ( $this->type > $this->typeLimit || $this->attempt > $this->typeAttempt * $this->typeLimit )
        {
            return false;
        }
        else
        {
            $this->type = ( int ) ceil( $this->attempt / $this->typeAttempt );
        }
        return true;
    }

	public function isLimited () : bool
	{
        return $this->type >= $this->typeLimit || $this->attempt > $this->typeAttempt * $this->typeLimit;
	}

    private function getIpAddress () : string
    {
        return getComponents( 'request' )->getIpAddress();
    }

    public function decrement () : bool
    {
        return $this->decrementRun();
    }

	public function increment () : bool
	{
        return $this->incrementRun();
	}

    private function handle () : bool
    {
        if ( $this->type >= $this->typeLimit || $this->attempt > $this->typeAttempt * $this->typeLimit )
        {
            return false;
        }

        if ( $this->attempt < 0 )
        {
            $this->attempt = 0;
        }

        if ( ! $this->typeHandle() )
        {
            return false;
        }

        return true;
    }

    private function init() : void
    {
        $this->adapterInit();

        if ( ! $this->adapterFetched )
        {
            throw new ErrorPropertyException( 'Fetch failed' );
        }

        $this->handle();
    }

    private function incrementRun () : bool
    {
        $this->attempt += 1;

        $adapter = $this->adapterInstance();
        $adapter->init( $this->__toAssoc() );

        return $adapter->increment();
    }

    private function decrementRun () : bool
    {
        if ( $this->attempt <= 0 )
        {
            return true;
        }

        $this->attempt -= 1;

        $adapter = $this->adapterInstance();
        $adapter->init( $this->__toAssoc() );

        return $adapter->decrement();
    }

    private function reset () : void
	{
        $configThrottle = getConfig( 'throttle' );

        if ( $configThrottle->type < 0 || $configThrottle->typeAttempt <= 0 || $configThrottle->typeLimit <= 0 )
        {
            throw new ErrorPropertyException( 'Invalid properties format' );
        }
        
        $this->attempt      = $configThrottle->attempt;
        $this->type         = $configThrottle->type;
        $this->typeLimit    = $configThrottle->typeLimit;
        $this->typeAttempt  = $configThrottle->typeAttempt;
        $this->timeout      = $configThrottle->timeout;
	}

    private function __toAssoc () : array
    {
        $data = [
            'attempt'       => $this->attempt,
            'type'          => $this->type,
            'type_attempt'  => $this->typeAttempt,
            'type_limit'    => $this->typeLimit,
            'ip'            => $this->getIpAddress(),
            'cacheName'     => $this->cacheName,
            'timeout'       => $this->timeout
        ];

        return $data;
    }
}
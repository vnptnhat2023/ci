<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface\ThrottleInterface;
use Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface\ThrottleAdapterInterface;
use Red2Horse\Mixins\Traits\Object\TraitReadOnly;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** setConfig -> init */
class Throttle implements ThrottleInterface
{
    use TraitSingleton, TraitReadOnly;

    private         int         $attempt        = 0;
	private         int         $type           = 1;
	private         int         $typeAttempt    = 5;
    private         int         $typeLimit      = 3;
	private         int         $timeout        = 1800;
    private         string      $cacheName;
    private         array       $adapters       = [
        'cache'     => \Red2Horse\Mixins\Classes\Base\Throttle\ThrottleCache::class,
        'database'  => \Red2Horse\Mixins\Classes\Base\Throttle\ThrottleDatabase::class
    ];
    protected       string      $currentAdapter;

    public function __construct ()
    {
        $this->init();
    }

    public function init () : void
    {
        $configThrottle         = getConfig( 'throttle' );
        $this->cacheName        = $configThrottle->cacheName;
        $this->adapters         = $configThrottle->adapters;
        $this->currentAdapter   = $configThrottle->currentAdapter;

        $this->reset();
    }

	public function cleanup () : void
	{
        $this->getAdapterInstance()->cleanup( $this );
        $this->reset();
	}

    private function reset () : void
	{
        $configThrottle     = getConfig( 'throttle' );

        $this->type         = $configThrottle->type;
        $this->typeLimit    = $configThrottle->type;
        $this->typeAttempt  = $configThrottle->typeAttempt;
        $this->timeout      = $configThrottle->timeout;
	}

    public function getAdapterInstance () : ThrottleAdapterInterface
    {
        $adapterNamespace = $this->adapters[ $this->currentAdapter ];
        return getInstance( $adapterNamespace );
    }

    public function setCurrentAdapter ( string $adapterName ) : void
    {
        $inArray = in_array( $adapterName, array_keys( $this->adapters ) );

        if ( $inArray )
        {
            $this->currentAdapter = $adapterName;
        }
    }
    
    public function getCurrentAdapter () : string
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

	public function isLimited () : bool
	{
        return $this->type >= $this->typeLimit;
	}

	public function increment () : bool
	{
        if ( $this->isLimited() && $this->attempt >= ( $this->typeLimit * $this->typeAttempt ) )
        {
            return false;
        }

        $this->attempt += 1;

        if ( ( $this->attempt > 0 ) && ( $this->attempt % $this->typeAttempt ) === 0 )
        {
            $this->type += 1;
        }

        $this->getAdapterInstance()->increment( $this );
        return true;
	}
}
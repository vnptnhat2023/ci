<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

interface ThrottleInterface
{
    public function init () : void;

    public function getAdapterInstance () : ThrottleAdapterInterface;

    public function getCurrentAdapter () : string;

    public function setCurrentAdapter ( string $adapterName ) : void;

    public function cleanup () : void;

    public function getAttempts () : int;

    public function isLimited (): bool;

    public function increment () : bool;

    public function getTypes () : int;
}
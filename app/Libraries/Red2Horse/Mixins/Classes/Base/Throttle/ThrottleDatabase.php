<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle;

use Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface\ThrottleAdapterInterface;
use Red2Horse\Mixins\Classes\Sql\Model;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Model\model;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ThrottleDatabase implements ThrottleAdapterInterface
{
    use TraitSingleton;

    // Throttle/ThrottleModel
    protected       string      $modelName = 'Throttle/ThrottleModel';
    protected       Model       $model;
    private         array       $props;
    private         array       $rowAttempt;

    public function __construct (){}

    public function init ( array $props ) : int
    {
        $this->props = $props;
        // $this->modelName = $props[ 'model_namespace' ];

        if ( ! isset( $this->model ) )
        {
            helpers( 'model' );
            $this->model = model( $this->modelName );
        }

        if ( ! isset( $this->rowAttempt ) )
        {
            $this->rowAttempt = $this->fetch();
        }

        return ( int ) $this->rowAttempt[ 'attempt' ];
    }
    
    public function isSupported () : bool
    {
        return $this->model->getInit();
    }

    private function fetch () : array
    {
        return $this->model->throttleFetch( $this->props );
    }

    private function throttleModelUpdate ( bool $reset = false ) : bool
    {
        return $this->model->throttleUpdate( $this->props, $reset );
    }

    public function increment () : bool
    {
        return $this->throttleModelUpdate();
    }

    public function decrement () : bool
    {
        return $this->throttleModelUpdate();
    }

    public function cleanup () : void
    {
        $this->throttleModelUpdate( true );
    }

    public function delete () : bool
    {
        return $this->throttleModelUpdate();
    }
}
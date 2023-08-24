<?php
declare( strict_types = 1 );
namespace Red2Horse\Mixins;

/**
 * @property object $traitCallInstance required
 * @property string[] $traitCallMethods required
 * @property array $traitCallback
 * ```
 * [
 *  'callback' => ?\Closure null,
 *  'arguments' => string[] [],
 *  'before' => bool false, # Callback before
 *  'after' => bool false # Callback after
 * ]
 * ```
 * @property string $traitBeforePrefix
 * @property string $traitAfterPrefix
 */
trait TraitCall
{
	private object $traitCallInstance;
	private array $traitCallMethods = [];
    private array $traitCallback = [
        'callback' => null,
        'arguments' => [],
        'before' => false,
        'after' => false
    ];
    private string $traitBeforePrefix = 'before_';
    private string $traitAfterPrefix = 'after_';

	/** @return mixed */
    public function __call( string $name, array $arguments )
    {
        if ( in_array( $name, $this->traitCallMethods, true ) )
        {
            $beforeName = $this->traitBeforePrefix . $name;
            $afterName = $this->traitAfterPrefix . $name;
            $callback = $this->traitCallback[ 'callback' ] ?? false;

            if ( is_callable( $callback ) )
            {
                $arguments = ! empty( $this->traitCallback[ 'arguments' ] )
                    ? array_merge( $arguments, $this->traitCallback[ 'arguments' ] )
                    : $arguments;

                if ( $this->traitCallback[ 'before' ] ) {
                    $callback( $beforeName, $arguments );
                }

                $run = $this->traitCallInstance->$name( ...$arguments );

                if ( $this->traitCallback[ 'after' ] ) {
                    $callback( $afterName, $arguments );
                }

                return $run;
            }

            return $this->traitCallInstance->$name( ...$arguments );
        }

        $error = sprintf(
            '%s::%s not found !.%5$sFile %s.%5$sLine %s',
            self::class, $name, __FILE__, __LINE__, PHP_EOL
        );
        throw new \BadMethodCallException( $error, 404 );
	}
}
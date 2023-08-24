<?php
/**
 * R2h events.
 */
class r2hEvents
{
    public function BeforeLogin ( ...$data ) : array
    {
        return $data;
    }

    // public function __call( string $method, ...$args )
    // {
    //     dd(func_get_args());
    //     return $this->$method( ...$args ) ?? null;
    // }
}
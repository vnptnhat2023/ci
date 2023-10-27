<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces\Sql;

use Closure;
use Red2Horse\Mixins\Classes\Sql\BaseBuilder;

interface BaseBuilderInterface
{
    /** @param array $data Non-associative array */
    public function select ( array $arrays, ?Closure $callable = null, int $len = 100 ) : BaseBuilder;

    /** @param array $data Non-associative array */
    public function distinct ( array $arrays, ?Closure $callable = null, int $len = 100 ) : BaseBuilder;

    public function get ();

    /**
     * @return null|object
     */
    public function update ( int $len = 1/*, ?Closure $callable = null*/ );

    /** 
     * @return null|object
     */
    public function delete ( int $len = 1/*, ?Closure $callable = null*/ );

    /**
     * @param array $data Associative
     * @return null|object
     */
    public function insert ( array $data, ?Closure $callable = null, int $len = 100 );

    /** @param array $data (Non-)Associative */
    public function from ( array $data, ?Closure $callable = null, int $len = 100 ) : BaseBuilder;

    /** @param array $data Associative */
    public function orWhere ( array $data, ?Closure $callable = null, int $len = 100 ) : self;

    /** @param array $data Associative */
    public function where ( array $data, ?Closure $callable = null, int $limit = 100 ) : BaseBuilder;

    /**
     * @param array $data ( Non- )Associative
     * @param array $on Associative [ 'a.b' => 'c.d' ]
     */
    public function join (
        array $data, 
        ?array $on = null, 
        ?string $onType = null, 
        ?Closure $callable = null, 
        ?Closure $callableJOn = null,
        int $len = 1 
    ) : BaseBuilder;

    /** @param array $data Associative [ 'a.b' => 'c.d' ] */
    public function on ( array $data, ?Closure $callable = null, int $len = 100 ) : BaseBuilder;

    /** @param array $data Associative */
    public function set ( array $data, ?Closure $callable = null, int $len = 100 ) : BaseBuilder;

    /** @param array $data Associative */
    public function like ( array $data, ?Closure $callable = null, int $limit = 100 ) : BaseBuilder;
    public function limit ( int $before = 0, int $after = 0/*, ?Closure $callable = null*/ ) : BaseBuilder;

    /** @param array $data Associative */
    public function in ( array $data, ?Closure $callable = null, int $len = 100 ) : BaseBuilder;

    /** @param array $data Non-associative */
    public function null (  array $data, ?Closure $callable = null, int $len = 100, ?string $type = null  );

    /** @param array $data Associative */
    public function orderBy ( array $data, ?Closure $callable = null, int $len = 100 ) : self;
}
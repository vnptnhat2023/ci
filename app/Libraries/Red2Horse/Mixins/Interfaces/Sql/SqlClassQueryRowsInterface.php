<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces\Sql;

use Closure;
use Red2Horse\Mixins\Classes\Sql\SqlClassQueryRows;

interface SqlClassQueryRowsInterface
{
    /** @param array $data Non-associative array */
    public function select ( array $arrays, ?Closure $callable = null, int $len = 100 ) : SqlClassQueryRows;

    /** @param array $data Non-associative array */

    public function distinct ( array $arrays, ?Closure $callable = null, int $len = 100 ) : SqlClassQueryRows;

    public function update ( int $len = 1, ?Closure $callable = null ) : SqlClassQueryRows;

    /** 
     * @param array $where Associative
     * @param array $in Non-associative
     */
    public function delete ( int $len = 1, ?Closure $callable = null ) : SqlClassQueryRows;

    /** @param array $data Associative */
    public function insert ( array $data, ?Closure $callable = null, int $len = 100 ) : SqlClassQueryRows;

    /** @param array $data (Non-)Associative */
    public function from ( array $data, ?Closure $callable = null, int $len = 100 ) : SqlClassQueryRows;

    /** @param array $data Associative */
    public function orWhere ( array $data, ?Closure $callable = null, int $len = 100 ) : self;

    /** @param array $data Associative */
    public function where ( array $data, ?Closure $callable = null, int $limit = 100 ) : SqlClassQueryRows;

    /**
     * @param array $data ( Non- )Associative
     * @param array $on Associative [ 'a.b' => 'c.d' ]
     */
    public function join ( array $data, ?array $on = null, ?string $onType = null, ?Closure $callable = null, int $len = 1 ) : SqlClassQueryRows;

    /** @param array $data Associative [ 'a.b' => 'c.d' ] */
    public function on ( array $data, ?Closure $callable = null, int $len = 100 ) : SqlClassQueryRows;

    /** @param array $data Associative */
    public function set ( array $data, ?Closure $callable = null, int $len = 100 ) : SqlClassQueryRows;

    public function limit ( int $before = 0, int $after = 0, ?Closure $callable = null ) : SqlClassQueryRows;

    public function is (/* string $is */);

    public function not (/* string $not */);

    /** @param array $data Non-associative */
    public function in ( array $data, ?Closure $callable = null, int $len = 100 ) : SqlClassQueryRows;

    public function null (/* string $as */);

    public function orderBy ( array $data, ?Closure $callable = null, int $len = 100 ) : self;
}
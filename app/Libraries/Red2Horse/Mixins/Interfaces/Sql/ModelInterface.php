<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces\Sql;

use Red2Horse\Facade\Query\QueryFacadeInterface;
use Red2Horse\Mixins\Classes\Sql\Model;

interface ModelInterface
{
    public static function setTable ( string $table, ?QueryFacadeInterface $connection = null ) : Model;
    
    public function table ( string $table, ?QueryFacadeInterface $connection = null ) : Model;

    public function fetchFirst ( array $where = [], array $orderBy = [] );

    public function edit ( array $set, array $where, int $len = 1, ?\Closure $callable = null );

    public function remove ( array $where = [], array $in = [], int $len = 1, ?\Closure $callable = null );

    public function add ( array $data, ?\Closure $callable = null, int $len = 100 );
}
<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces\Sql;

use Red2Horse\Facade\Query\QueryFacadeInterface;
use Red2Horse\Mixins\Classes\Sql\Model;

interface ModelInterface
{
    public static function model ( string $table, ?QueryFacadeInterface $connection = null ) : Model;
    
    public function init ( ?string $table, ?QueryFacadeInterface $connection = null ) : Model;

    /** @param string[] $allowedFields */
    public function toggleAllowedFields ( array $allowedFields ) : Model;

    public function setConnection ( ?QueryFacadeInterface $connection = null ) : Model;

    public function setModelProperty ( ?\stdClass $childProperties = null ) : Model;
}
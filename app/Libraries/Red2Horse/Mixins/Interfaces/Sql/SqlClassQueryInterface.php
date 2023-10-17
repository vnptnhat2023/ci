<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces\Sql;

interface SqlClassQueryInterface
{
    public static function setTable ( string $table, ?string $model = null ) : self;
    
    public function table ( string $table, ?string $model = null ) : self;

    /** @return mixed */
    public function query ( string $sql );
    
    /**
     * @param array $set assoc
     * @param array $where assoc
     */
    public function edit ( array $set, array $where );
    
    /**
     * @param array $where assoc
     */
    public function delete ( array $where );
    
    /**
     * @param array $data assoc-only
     */
    public function add ( array $data );
    
    /**
     * @param array $set assoc-only
     * @param array $where not assoc
     * @param array $in not assoc
     */
    public function editIn ( array $set, array $where, array $in );
    
    /**
     * @param array $where assoc-only
     * @param array $in not assoc
     */
    public function deleteIn ( array $where, array $in );
}
<?php

namespace App\Libraries;

final class Extension {

  /** Store all enabled extension */
  private static array $loaded = [];

  private static ?object $getInstance = null;

  /** Set true when need modify $loaded, access withSetter():Method */
  private bool $withSetter = false;

  public function __construct (array $classes = [] )
  {
    helper('string');
    $this->fill( $classes );
	}

  public static function getInstance ( array $classes = [] )
  {
    if ( self::$getInstance ) {
      return self::$getInstance->fill( $classes );
    }

    return self::$getInstance = new Extension( $classes );
  }

  public function fill ( array $classes = [] ) : void
  {
    if ( ! empty( $classes ) ) {

			foreach ( $classes as $key => $value )
			{
        $key = strCamelCase( $key );

        if ( ! array_key_exists( $key, self::$loaded ) )
        {
          self::$loaded[ $key ] = $value;
        }
        else
        {
					foreach ( $value as $sKey => $itNull )
					{
            if ( ! array_key_exists( $sKey, self::$loaded[ $key ] ) ) {
              self::$loaded[ $key ][ $sKey ] = $itNull;
            }
          }
				}

      }
    }
  }

  public function getLoaded () : array
  {
    return self::$loaded;
  }

  /** When need modify $loaded property */
  public function withSetter () : self
  {
    $this->withSetter = true;
    return $this;
  }

  public function __call ( string $key, $value )
	{
		$result = null;
    $key = strCamelCase( $key );

    if ( array_key_exists( $key, self::$loaded ) )
		{
      $extNamespace = "\\Ext\\{$key}\\{$key}";
      $result = $extNamespace::getInstance( empty( $value ) ? null : $value );
    }
    else if ( method_exists( $this, $key ) )
    {
			$result = $this->$key( $value );
    }
    else if ( property_exists( $this, $key ) )
    {
      $result = $this->$key;
    }

		return $result;
	}

	public function __get ( string $key )
	{
		$result = null;
    $key = strCamelCase( $key );

    if ( array_key_exists( $key, self::$loaded ) )
		{
      $extNamespace = "\\Ext\\{$key}\\{$key}";
      $result = $extNamespace::getInstance();
    }
    else if ( method_exists( $this, $key ) )
    {
			$result = $this->$key();
    }
    else if ( property_exists( $this, $key ) )
    {
      $result = $this->$key;
    }

		return $result;
	}

	public function __set ( string $key, $value = null ) : self
	{
    $upperKey = strCamelCase( $key );

    if ( array_key_exists( $upperKey, self::$loaded ) )
    {
      if ( $this->withSetter === true ) {
        self::$loaded[ $upperKey ] = $value;
      }

			return $this;
    }
    else if ( method_exists( $this, $upperKey ) )
		{
			$this->$upperKey( $value );

			return $this;
		}

    $this->$key = $value;

		return $this;
  }
}
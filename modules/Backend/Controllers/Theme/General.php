<?php

namespace BAPI\Controllers\Theme;

use \CodeIgniter\RESTful\ResourceController;
use \BAPI\Controllers\Mixins\BAPITrait;
use \BAPI\Entities\Theme\General as ThemeGeneral;

class General extends ResourceController
{
  use BAPITrait;

  protected $modelName = '\App\Models\Option';

  /**
	 * @var \BAPI\Entities\Theme\General $entityTheme
	 */
  private string $entityTheme = '\BAPI\Entities\Theme\General';

	# --- Instead of entityTheme property
	/**
	 * @var ThemeGeneral $entityThemeClass
	 */
  private ThemeGeneral $entityThemeClass;

  # --- Update::json_encode
  private int $jsonDeep = 3;

  public function __construct()
  {
    $this->entityThemeClass = new $this->entityTheme();
  }

  # ==========================================================
  public function index(string $id = 'Backend')
  {
    $id = ucfirst($id);

    if ( $id !== 'Backend' )
    {
      $id = 'Frontend';
      $path = FRONTENDPATH . "Views/{$id}/Theme/";
    }
    else
    {
      $path = BACKENDPATH . "Views/{$id}/Theme/";
    }

    $data = [
      'current_theme' => $this->_currentTheme( $id ),
      'data' => $this->_scanTheme( $path )
    ];

    return $this->res( $data, null, '' );
	}

  # ==========================================================
	public function update($id = null)
  {
    $rawInput = $this->request->getRawInput();

    if ( empty( $rawInput ) ) {
      return $this->resErr( lang('api.errorEmptyData') );
    }

    $validation = \Config\Services::validation();
    $rules = config('Theme')->getRules();
    $rawArray = $this->_entityTheme( $rawInput );

    if ( ! $validation->setRules( $rules )->run( $rawArray ) ) {
      return $this->resErr( $validation->getErrors() );
    }

    $this->model->setUpdateRules( $id );
    $data = json_encode( $rawArray, 0, $this->jsonDeep );

    if ( ! $this->model->update( $id, $data ) ) {
      return $this->resErr( $this->model->errors() );
    }

    return $this->index();
	}

  # ----------------------------------------------------------
  private function _currentTheme(string $id) : array
	{
    $default = ( $id === 'Backend' ) ? config('Theme')::backend
      : config('Theme')::frontend;
    $title = 'site_title';
    $slogan = 'site_slogan';

    if ( $dbData = $this->model->_find( $id ) )
    {
      $data = ! is_array( $dbData )
        ? json_decode( $dbData, true, $this->jsonDeep ) : $dbData;
      $data[ $title ] = $data[ $title ] ?? $default[ $title ];
      $data[ $slogan ] = $data[ $slogan ] ?? $default[ $slogan ];
    }
    else
    {
      $data = $this->model->_saveCache( $id, $default );
    }

    return $this->_entityTheme( $data );
  }

  # ----------------------------------------------------------
  /**
   * Scan all info.json file in the theme folder
   */
  private function _scanTheme(string $path, string $needed = 'info.json') : array
  {
    helper('filesystem');
    $data = [];
    $formatRules = new \CodeIgniter\Validation\FormatRules;

    if ( $map = directory_map( $path, 2, true ) ) {
      foreach ( $map as $folders => $files ) {
        if ( is_array( $files ) AND in_array( $needed, $files ) ) {
          $key = array_search( $needed, $files );
          $path = "{$path}{$folders}{$files[ $key ]}";
          $str = @file_get_contents( $path );

          if ( $formatRules->valid_json( $str ) ) {
            $infoArray = esc( json_decode( $str, true ) );
            $data[] = $this->_entityTheme( $infoArray );
          }
        }
      }
    }

    return $data;
  }

  # __________________________________________________________
  private function _entityTheme($data)
  {
    if ( empty( $data ) ) return $data;

    $entity = $this->entityThemeClass->fill( $data );

    return $entity->toRawArray();
  }
}
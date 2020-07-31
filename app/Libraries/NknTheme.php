<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class My_theme {

  private $config_key;
  private $title;
  private $slogan;
  private $infomation;

  public function __construct()
  {
    // $this->CI =& get_instance();
  }
  /**
   * [initialize set value for property variable]
   * @param  string $config_key    [load default config/theme: frontend or backend]
   * @param  array  $current_theme [current theme using (fetch from db)]
   */
  public function initialize(string $config_key = 'frontend', array $current_theme = []) 
  {
    $this->config_key = $config_key;

    $this->title = $current_theme['site_title'];
    $this->slogan = $current_theme['site_slogan'];
    $this->infomation = $current_theme;
  }

  public function controller_hook(string $file_name = 'Theme_hooks', string $class_name, 
    string $method_name, Home $instance)
  {
    if ( file_exists( $this->inc_path("{$file_name}.php") ) ) {
      require_once( $this->inc_path("{$file_name}.php") );

      if ( class_exists($class_name) ) {
        $class_name = new $class_name();

        if (method_exists($class_name, $method_name)) {
          $class_name->$method_name($instance);
        }
      }
    }
  }

  public function get_title(string $before = '', string $after = '') :string
  {
    return $before . $this->title . $after;
  }

  public function get_slogan() :string
  {
    return $this->slogan;
  }

  public function get_infomation(bool $object = FALSE) 
  {
    return $object ? (object) $this->infomation : $this->infomation;
  }

  public function asset_url($file_name = '') :string
  {
    return base_url("assets/{$this->config_key}/{$this->infomation['asset_path']}/{$file_name}");
  }

  public function view_path($file_name = '') :string
  {// for default CI load view method
    return "{$this->config_key}/theme/{$this->infomation['view_path']}/{$file_name}";
  }

  public function inc_path($file_name = '') :string
  {// for require, include
    $file_name = str_replace('.php', '', $file_name);
    return VIEWPATH . "{$this->config_key}/theme/{$this->infomation['view_path']}/{$file_name}.php";
  }
}
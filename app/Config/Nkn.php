<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Nkn extends BaseConfig
{
	# --- Default PAGE?
	# --- Default PAGE TYPE? "extension needed multiple pages and subPage"
	# --- Page TYPE, title, slug, id?
	# --- Relation using?
	# --- * Controller Or Content?

	/** NknAuth session-name */
	public const NKNss = 'oknkn';

	/** NknAuth cookie-name */
	public const NKNck = 'konkn';
	public const NKNckTtl = WEEK;

  public const throttle = [
  	'type' => 1,
  	'limit_one' => 4,
  	'limit' => 10,
  	'timeout' => 30
	];
}
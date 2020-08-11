<?php

namespace App\Libraries\NknAuth;

use CodeIgniter\Session\Handlers\FileHandler;

class NknFileHandler extends FileHandler
{

	public function __construct($config, string $ipAddress)
	{
		parent::__construct($config, $ipAddress);

		if (! empty($config->sessionSavePath))
		{
			$this->savePath = rtrim($config->sessionSavePath, '/\\');
			ini_set('session.save_path', $config->sessionSavePath);
		}
		else
		{
			$sessionPath = rtrim(ini_get('session.save_path'), '/\\');

			if (! $sessionPath)
			{
				$sessionPath = WRITEPATH . 'session';
			}

			$this->savePath = $sessionPath;
		}

		$this->matchIP = $config->sessionMatchIP;

		$this->configureSessionIDRegex();
	}
}
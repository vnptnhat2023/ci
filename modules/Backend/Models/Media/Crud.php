<?php

namespace BAPI\Models\Media;

use CodeIgniter\Model;

class Crud extends Model
{
  protected $table = 'media';
	protected $returnType = 'array';

	protected $dateFormat = 'date';
  protected $useTimestamps = true;
}
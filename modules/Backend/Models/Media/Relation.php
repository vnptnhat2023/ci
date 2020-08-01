<?php

namespace BAPI\Models\Media;

use CodeIgniter\Model;

class Relation extends Model
{

  protected $table = 'media_relation';
	protected $returnType = 'array';

	protected $dateFormat = 'date';
	protected $useTimestamps = true;

}
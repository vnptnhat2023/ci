<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Mail;

use CodeIgniter\Email\Email;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
interface MailAdapterInterface
{
	public function to ( $to ) : Email;

	public function subject ( $subject ) : Email;

	public function message ( $message ) : Email;

	public function send () : bool;
}
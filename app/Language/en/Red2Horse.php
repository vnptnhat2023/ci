<?php

$homeLink = sprintf( '%s', anchor( base_url(), 'Back to home.' ) );

return [
	'homeLink' => $homeLink,
	'login' => 'login lnbl',
	'logout' => 'logout lnbl',
	'seed' => 'Seeder lnbl',
	'resetPassword' => 'reset password lnbl',

	'errorCookieUpdate' => 'Validated cookie, when update userId: {0}',
	'errorNeedLoggedIn' => 'You have not login yet. ',
	'errorIncorrectInformation' => 'Incorrect information.',
	'errorNotReadyYet' => 'The current account is in status `{0}` has not been activated yet.',
	'errorThrottleLimitedTime' => 'Please try to login after {number} {minutes}',

	/** Database */
	'errorDatabaseConnect' => 'cannot connect to db.',
	'errorDatabaseNotDefined' => 'Database not defined.',
	'errorCreatingDatabase' => 'The database : "{0}" is already exist or have an error when creating database.',
	'successCreatedDatabase' => 'Database: {0} has been created.',

	'noteDenyRequestPassword' => 'You are logged in, so you are not authorized to use this function.',
	'noteLoggedInAnotherPlatform' => 'Your account has been logged-in with another platform.',

	'success' => '{0} successfully.',
	'successLogged' => 'You are already logged in. ' . $homeLink,
	'successLoggedWithUsername' => 'Hi {0}, you are already logged in. ' . $homeLink,
	'successLogoutWithUsername' => '{0} you have been logged out. ' . $homeLink,
	'successLogout' => 'You have been logged out. ' . $homeLink,
	'successResetPassword' => 'Hi {0}, your password has been reset to {1}.',
	'successSeeder' => 'Seeder successfully.',

	'labelUsername' => 'username',
	'labelUserOrEmail' => 'name',
	'labelPassword' => 'password',
	'labelEmail' => 'email',
	'labelCaptcha' => 'captcha',
	'labelRememberMe' => 'remember me',
	'labelHomePage' => 'home',
	'labelResetPasswordPage' => 'reset password',
	'LabelBtnLoginSubmit' => 'log in',
	'LabelBtnResetSubmit' => 'reset',
	'LabelBtnClear' => 'clear',
	'LabelBtnSubmit' => 'Submit',

	'groupId' => 'group id',
	'labelUserGroupName' => 'group name',
	'labelGroupPermission' => 'permission',
	'labelGroupRole' => 'role',
	'labelGroupDeletedAt' => 'delete at',

	'placeholderUsername' => 'user name',
	'placeholderUserOrEmail' => 'email',
	'placeholderPassword' => 'password',
	'placeholderCaptcha' => 'captcha',

	'isAssoc' => 'Data must be an associative array.',

	'id' => 'id',
	'status' => 'status',
	'activity' => 'activity',
	'last_login' => 'last login',
	'created_at' => 'created at',
	'updated_at' => 'updated at',
	'deleted_at' => 'deleted at',
	'session_id' => 'session id',
	'selector' => 'selector',
	'token' => 'token',
	'ci_captcha' => 'Incorrect Captcha',

	/** Database */
	'db_hostname' => 'host name',
	'db_username' => 'user name',
	'db_password' => 'password',
	'db_database' => 'database',
	'db_port' 	  => 'port',

	/** File */
	'errorFileCannotWrite' => 'cannot write file',

	/** Throttle */
	'throttle_id' 				=> 'throttle id',
	'throttle_attempt' 			=> 'attempt',
	'throttle_ip' 				=> 'ip address',
	'throttle_createdAt' 		=> 'created at',
	'throttle_updatedAt' 	  	=> 'updated at',

	'errorThrottleIsOff'			=> 'Throttle is off',
	'errorThrottleNotSupported'		=> 'Throttle not supported',
];
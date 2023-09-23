<?php

function r2hI18 ( string $field, bool $w = false ) : string {
	$str = lang( "Red2Horse.{$field}" );

	return $w ? ucwords( $str ) : ucfirst( $str );
}
function bgColor () : string
{
	$arr = [
		'lemonchiffon',
		'cadetblue',
		'lightgoldenrodyellow',
		'khaki'
	];
	$arr2 = [
		'coral',
		'currentColor',
		'chocolate',
		'lightcoral',
		'darkseagreen',
		'darkkhaki'
	];
	
	if ( (int) date('H') < 12 )
	{
		return $arr2[ random_int( 0, count( $arr2 ) - 1 ) ];
	}
	return $arr[ random_int( 0, count( $arr ) - 1 ) ];
}

function fontFamily () : string
{
	$arr = [
		'Courier New',
		'monospace',
		'garamond',
		'dubai',
		'bahnschrift'
	];
	return $arr[ random_int( 0, count( $arr ) - 1 ) ] ;
}

?>

<!DOCTYPE html>
<html lang="vi">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= r2hI18( 'login' ) ?></title>

		<link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>">

		<script src="<?= base_url('public/assets/js/vue@2.6.11.min.js') ?>"></script>
		<script src="<?= base_url('public/assets/js/vee-validate.min.js') ?>"></script>

		<style>
			body
			{
				font-family:  <?= fontFamily() ?>, cursive, monospace, sans-serif;
				/* background-image: url(https://source.unsplash.com/random); */
				background-color: <?= bgColor() ?>;
				background-repeat: no-repeat;
				background-position: center center;
				background-size: cover;
				margin: 0px;
				padding: 0px;
				min-height: 100vh;
			}

			.form-box .alert
			{
				background: none;
			}

			.alert .alert-success
			{
				border-color: #506950;
			}

			.alert .alert-danger
			{
				border-color: #dd7472;
			}

			.form-box-title
			{
				color: wheat;
				margin-bottom: 50px;
				/* position: absolute;
				top: 65px;
				transform-origin: 0 0;
				transform: rotate(90deg);
				text-align: right; */
			}

			.form-box
			{
				background: rgba(0,0,0,.8);
				opacity: .9;
				margin-top: 80px;
				padding: 40px;
				border-radius: 3.5px;
			}

			.form-box .form-group label
			{
				color: #fff;
			}

			.form-box .form-group input
			{
				border-radius: 0px;
				/*height: 35px;*/
				background: none;
				color: #fff;
			}

			.submit-btn{
				border-radius: 0px;
				background: #fff!important;
				color: red!important;
				padding: 10px 25px;
				font-weight: 600
			}
		</style>
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3"
				id="login-page">

					<?= form_open( base_url( '/login' ) ) ?>

					<div class="form-box">
						<!-- <div class="pull-right">
							<span class="languages">
								<a href="<?= current_url() . '/?lang=en' ?>">En</a>
							</span>&nbsp;
							<span class="languages">
								<a href="<?= current_url() . '/?lang=vi' ?>">Vi</a>
							</span>
						</div> -->

						<h3 class="form-box-title"><?= r2hI18( 'login' ) ?></h3>

						<!-- Message -->
						<?php
							if ( ! empty( $message->errors ) )
							{
								echo '<div class="alert alert-danger">';

								foreach ( $message->errors as $error )
								{
									echo "<p>{$error}</p>";
								}

								echo '</div>';
							}

							if ( ! empty( $message->success ) )
							{
								echo '<div class="alert alert-success">';

								foreach ($message->success as $success )
								{
									echo "<p>{$success}</p>";
								}

								echo '</div>';
							}
						?>
						<!-- End message -->

						<!-- Form -->
						<?php if ( $result->show->form ) : ?>

								<div class="form-group" :class="{'has-warning': errors.has( 'username' )}">
									<label for="username"><?= r2hI18( 'labelUserOrEmail' ) ?></label>

									<input type="text" class="form-control"
									v-validate="'required|min:5|max:128'"
									data-vv-as="<?= r2hI18( 'labelUserOrEmail' ) ?>"
									name="username"
									id="username"
									placeholder="<?= r2hI18( 'placeholderUserOrEmail', false) ?>"
									maxlength="128"
									value="<?= set_value( 'username' ) ?>"
									autocomplete="off"
									spellcheck="false">

									<span v-show="errors.has( 'username' )" class="help-block">{{ errors.first( 'username' ) }}</span>
								</div>

								<div class="form-group" :class="{'has-warning': errors.has( 'password' )}">
									<label for="password"><?= r2hI18( 'labelPassword' ) ?></label>

									<input type="password"
									class="form-control"
									maxlength="32"
									v-validate="'required|min:5|max:64'"
									data-vv-as="<?= r2hI18( 'labelPassword' ) ?>"
									name="password"
									id="password"
									placeholder="<?= r2hI18( 'placeholderPassword', false ) ?>"
									autocomplete="off"
									spellcheck="false"
									value="<?= set_value( 'password' ) ?>">

									<span v-show="errors.has( 'password' )" class="help-block">
										{{ errors.first( 'password' ) }}
									</span>
								</div>

								<!-- Captcha form -->
								<?php if ( $result->show->captcha ) : helper( 'captcha' ); ?>

									<div class="form-group">
										<label for="captcha"><?= r2hI18( 'labelCaptcha' ) ?></label>

										<div class="input-group">
											<input type="text"
											class="form-control"
											id="captcha"
											name="captcha"
											placeholder="<?= r2hI18( 'placeholderCaptcha', false ) ?>">

											<span class="input-group-addon"
											style="margin: 0; padding: 0;">
												<?= ci_captcha() ?>
											</span>
										</div>
									</div>

								<?php endif ?>
								<!-- Captcha form -->
							
								<!-- Remember me checkbox -->
								<?php if ( $result->show->remember_me ) : ?>
									<div class="form-group">
										<div class="checkbox">
											<label>
												<input
												type="checkbox"
												name="remember_me"
												value="1" <?= set_checkbox( 'remember_me', '1' ); ?>>
												<span><?= r2hI18( 'labelRememberMe' ) ?></span>
											</label>
										</div>
									</div>
								<?php endif; ?>
								<!-- End remember me checkbox -->

							<div class="form-group">
								<button
								type="submit"
								class="btn btn-primary"
								style="border-radius: none;"
								:disabled="errors.has( 'username' ) || errors.has( 'password' )"
								>
									<?= r2hI18( 'LabelBtnLoginSubmit' ) ?>
								</button>
							</div>

							<div style="margin: 30px 0;">
								<span class="pull-right">
									<a href="<?= base_url( 'login/forgot' ) ?>">
										<span><?= r2hI18( 'resetPassword' ) ?></span>&nbsp;
										<!-- <span class="glyphicon glyphicon-arrow-right"></span> -->
									</a>
								</span>

								<span>
									<a href="<?= base_url() ?>">
										<!-- <span class="glyphicon glyphicon-arrow-left"></span>&nbsp; -->
										<span><?= r2hI18( 'labelHomePage' ) ?></span>
									</a>
								</span>
							</div>

						<?php endif; ?>
						<!-- End form -->

					</div>
					<?= form_close() ?>
				</div>
			</div>
		</div>

		<script>
			Vue.use( VeeValidate, { locale: 'vi' } );
			new Vue( { el:'#login-page' } );
		</script>
	</body>
</html>
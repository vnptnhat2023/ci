<?php

// echo '<pre>';
// die(var_dump( \Config\Services::Red2HorseAuth()->getMessage() ));

if ( ! function_exists( 'NknI18' ) )
{
	function NknI18 ( string $field, bool $w = true ) : string {
		$str = lang( "NknAuth.{$field}" );

		return true === $w ? ucwords( $str ) : ucfirst( $str );
	}
}

?>

<!DOCTYPE html>
<html lang="vi">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= NknI18( 'LabelBtnLoginSubmit' ) ?></title>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

		<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/vee-validate/2.2.15/vee-validate.min.js"></script>

		<style>
			body {
				font-family: cursive, monospace, sans-serif;
				/* background-image: url(https://source.unsplash.com/random); */
				background-repeat: no-repeat;
				background-position: center center;
				background-size: cover;
				margin: 0px;
				padding: 0px;
				min-height: 100vh;
			}

			.form-box-title {
				position: absolute;
				top: 65px;
				transform-origin: 0 0;
				transform: rotate(90deg);
				text-align: right;
			}
			.form-box {
				background: rgba(0,0,0,.8);
				opacity: .9;
				margin-top: 80px;
				padding: 40px;
				border-radius: 3.5px;
			}
			.form-box .form-group label{
				color: #fff;
			}
			.form-box .form-group input{
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

					<?= form_open( base_url( 'login' ) ) ?>

					<div class="form-box">

						<h3 class="form-box-title"><?= NknI18( 'LabelBtnLoginSubmit' ) ?></h3>

						<?php
							if ( isset( $errors[ 0 ] ) ) {

								echo '<div class="alert alert-warning">';
									foreach ( $errors as $error ) { echo "<p>{$error}</p>"; }
								echo '</div>';

							}

							if ( isset( $success[ 0 ] ) ) {

								echo '<div class="alert alert-success">';
									foreach ( $success as $success ) { echo "<p>{$success}</p>"; }
								echo '</div>';

							}
						?>

						<?php if ( false === $result->successfully ) : ?>

						<div class="form-group" :class="{'has-warning': errors.has( 'username' )}">
							<label for="username"><?= NknI18( 'labelUserOrEmail' ) ?></label>

							<input type="text" class="form-control"
							v-validate="'required|min:5|max:128'"
							data-vv-as="<?= NknI18( 'labelUserOrEmail' ) ?>"
							name="username"
							id="username"
							placeholder="<?= NknI18( 'placeholderUserOrEmail', false) ?>"
							maxlength="128"
							value="<?php echo set_value( 'username' ) ?>"
							autocomplete="off"
							spellcheck="false">

							<span v-show="errors.has( 'username' )" class="help-block">{{ errors.first( 'username' ) }}</span>
						</div>

						<div class="form-group" :class="{'has-warning': errors.has( 'password' )}">
							<label for="password"><?= NknI18( 'labelPassword' ) ?></label>

							<input type="password"
							class="form-control"
							maxlength="32"
							v-validate="'required|min:5|max:64'"
							data-vv-as="<?= NknI18( 'labelPassword' ) ?>"
							name="password"
							id="password"
							placeholder="<?= NknI18( 'placeholderPassword', false ) ?>"
							autocomplete="off"
							spellcheck="false"
							value="<?php echo set_value( 'password' ) ?>">

							<span v-show="errors.has( 'password' )" class="help-block">
								{{ errors.first( 'password' ) }}
							</span>
						</div>

						<?php if ( true === $result->showCaptcha ) : helper( 'captcha' ); ?>

						<div class="form-group">
							<label for="captcha"><?= NknI18( 'labelCaptcha' ) ?></label>

							<div class="input-group">
								<input type="text"
								class="form-control"
								id="captcha"
								name="captcha"
								placeholder="<?= NknI18( 'placeholderCaptcha', false ) ?>">

								<span class="input-group-addon"
								style="margin: 0; padding: 0;">
									<?= ci_captcha() ?>
								</span>
						  </div>
						</div>

						<?php endif ?>

						<div class="form-group">
							<div class="checkbox">
								<label>
									<input
									type="checkbox"
									name="remember_me"
									value="1" <?php echo set_checkbox( 'remember_me', '1' ); ?>>
									<span><?= NknI18( 'labelRememberMe' ) ?></span>
								</label>
							</div>
						</div>

						<div class="form-group">
							<button
							type="submit"
							class="btn btn-primary"
							style="border-radius: none;"
							:disabled="errors.has( 'username' ) || errors.has( 'password' )"
							>
								<?= NknI18( 'LabelBtnLoginSubmit' ) ?>
							</button>

							<button type="reset" class="btn btn-default" style="border-radius: none;" >
								<?= NknI18( 'LabelBtnClear' ) ?>
							</button>
						</div>

						<div>
							<span class="pull-right">
								<a href="<?php echo base_url( 'login/forgot' ) ?>">
									<span><?= NknI18( 'labelResetPasswordPage' ) ?></span>&nbsp;
									<span class="glyphicon glyphicon-arrow-right"></span>
								</a>
							</span>

							<span>
								<a href="<?php echo base_url() ?>">
									<span class="glyphicon glyphicon-arrow-left"></span>&nbsp;
									<span><?= NknI18( 'labelHomePage' ) ?></span>
								</a>
							</span>
						</div>

						<?php endif; ?>

					</div>
					<?php echo form_close() ?>
				</div>
			</div>
		</div>

		<script>
			Vue.use(VeeValidate,{locale: 'vi'})
			new Vue({el:'#login-page'})
		</script>
	</body>
</html>
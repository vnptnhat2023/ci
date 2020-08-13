<?php

if ( ! function_exists( 'NknI18' ) )
{
	function NknI18 ( string $field, bool $w = true ) : string {
		$str = lang( "NknAuth.{$field}" );

		return true === $w ? ucwords( $str ) : ucfirst( $str );
	}
}

?>

<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= ucfirst(lang('NknAuth.LabelBtnResetSubmit')) ?></title>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

		<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/vee-validate/2.2.15/vee-validate.min.js"></script>

		<style>
	 		body {
				font-family: cursive, monospace, sans-serif;
      	background-image: url(https://source.unsplash.com/random);
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
        background-repeat: no-repeat;
        background-position: center center;
		    opacity: .9;
		    margin-top: 80px;
		    padding: 40px;
		    border-radius: 3.5px;
      }
      /*.form-box {
        top: 50%;
		    left: 50%;
		    position: absolute;
		    width: 500px;
		    margin-top: 100px;
		    background: rgba(0,0,0,.8);
		    margin-left: -250px;
		    padding: 20px 30px;
		    line-height: 50px;
      }*/
      .form-box .form-group label{
        color: #fff;
      }
      .form-box .form-group input{
        border-radius: 0px;
        /*height: 35px;*/
        background: none;
        color: #fff;
      }
      .submit-btn {
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
				id="forget-password-page">

					<?= form_open(  base_url( 'login/forgot' ) , '@submit="submit($event)"' ) ?>

					<div class="form-box">

						<h2 class="form-box-title"><?= NknI18( 'labelResetPasswordPage' ) ?></h2>
						<?php
							if ( isset( $success[0] ) ) {
								echo '<div class="alert alert-warning">';
								foreach ($success as $error) { echo "<p>{$error}</p>"; }
								echo '</div>';
							}

							if ( isset( $success[0] ) ) {
								echo '<div class="alert alert-success">';
								foreach ($success as $success) { echo "<p>{$success}</p>"; }
								echo '</div>';
							}
						?>

						<div class="form-group" :class="{'has-warning': errors.has('username')}">
							<label for="username"><?= NknI18( 'labelUsername', false ) ?></label>

							<input
							type="text"
							class="form-control"
							maxlength="32"
							v-validate="'required|min:5|max:32'"
							data-vv-as="<?= NknI18( 'labelUsername', false ) ?>"
							name="username"
							id="username"
							placeholder="<?= NknI18( 'labelUsername', false ) ?>"
							value="<?php echo set_value('username') ?>"
							autocomplete="off" spellcheck="false">

							<span v-show="errors.has('username')" class="help-block">{{ errors.first('username') }}</span>
						</div>

						<div class="form-group" :class="{'has-warning': errors.has('email')}">

							<label for="email"><?= NknI18( 'labelEmail', false ) ?></label>

							<input type="email"
							class="form-control"
							maxlength="64"
							v-validate="'required|min:5|max:64|email'"
							data-vv-as="<?= NknI18( 'labelEmail', false ) ?>"
							name="email"
							id="email"
							placeholder="<?= NknI18( 'labelEmail', false ) ?>"
							value="<?php echo set_value('email') ?>"
							autocomplete="off" spellcheck="false">

							<span v-show="errors.has('email')" class="help-block">{{ errors.first('email') }}</span>

						</div>

						<?php if ( true === $result->captcha ) : helper( 'captcha' ); ?>

						<div class="form-group">
							<label for="captcha"><?= NknI18( 'labelCaptcha', false ) ?></label>

							<div class="input-group">
								<input type="text"
								class="form-control"
								id="ci_captcha"
								name="ci_captcha"
								placeholder="<?= $i18placeholderCaptcha ?>">

								<span class="input-group-addon"
								style="margin: 0; padding: 0;">
									<?= ci_captcha() ?>
								</span>
						  </div>
						</div>

						<?php endif ?>

						<div class="form-group">
							<button
							type="submit"
							class="btn btn-primary"
							:disabled="errors.has('username') || errors.has('email')"><?= NknI18( 'LabelBtnResetSubmit' ) ?></button>
							<button type="reset" class="btn btn-default"><?= NknI18( 'LabelBtnClear') ?></button>
						</div>

						<div>
							<a href="<?php echo base_url('login') ?>">
								<span class="glyphicon glyphicon-arrow-left"></span>&nbsp;
								<span><?= NknI18( 'LabelBtnLoginSubmit' ) ?></span>
							</a>
						</div>
					</div>
					<?php echo form_close() ?>

				</div>
			</div>
		</div>

		<script>
			Vue.use(VeeValidate,{locale: 'vi'})
			new Vue({el:'#forget-password-page'})
		</script>
	</body>
</html>
<?php
use yii\helpers\Url;
$clientId = \Yii::$app->params['googleCloudClientId'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · QuickNav</title>
    <!-- Vendors styles-->
    <link rel="stylesheet" href="<?= Url::to('@web/coreui/vendors/simplebar/css/simplebar.css', true) ?>">
    <link rel="stylesheet" href="<?= Url::to('@web/coreui/css/vendors/simplebar.css', true) ?>">
    <!-- Main styles for this application-->
    <link href="<?= Url::to('@web/coreui/css/style.css', true) ?>" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
  </head>
  <body>
    <div class="bg-light min-vh-100 d-flex flex-row align-items-center">
      <div class="container">
        <div class="row justify-content-center">
          <div class="card col-md-5 p-4 mb-0">
            <div class="card-body text-center">
              <h1>Masuk</h1>
              <p class="text-medium-emphasis mb-4">Lanjutkan menggunakan akun google anda</p>
              <div id="g_id_onload"
                data-client_id="<?= $clientId ?>"
                data-login_uri="<?= Url::toRoute('auth/signin-with-google-callback', true) ?>"
                data-auto_prompt="false"
                data-ux_mode="redirect">
              </div>
              <div class="g_id_signin"
                data-type="standard"
                data-size="large"
                data-theme="outline"
                data-text="sign_in_with"
                data-shape="rectangular"
                data-logo_alignment="center"
                style="display: flex; justify-content: center">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CoreUI and necessary plugins-->
    <script src="<?= Url::to('@web/coreui/vendors/@coreui/coreui/js/coreui.bundle.min.js', true) ?>"></script>
    <script src="<?= Url::to('@web/coreui/vendors/simplebar/js/simplebar.min.js', true) ?>"></script>
  </body>
</html>
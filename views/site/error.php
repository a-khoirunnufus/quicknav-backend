<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception$exception */

use yii\helpers\Url;

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error · QuickNav</title>
    <!-- Vendors styles-->
    <link rel="stylesheet" href="<?= Url::to('@web/coreui/vendors/simplebar/css/simplebar.css', true) ?>">
    <link rel="stylesheet" href="<?= Url::to('@web/coreui/css/vendors/simplebar.css', true) ?>">
    <!-- Main styles for this application-->
    <link href="<?= Url::to('@web/coreui/css/style.css') ?>" rel="stylesheet">
  </head>
  <body>
    <div class="min-vh-100 d-flex flex-row align-items-center">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <div class="clearfix">
              <!-- <h1 class="float-start display-3 me-4">400</h1> -->
              <h4 class="pt-3">Terjadi kesalahan</h4>
              <p class="text-medium-emphasis"><?= $exception->getMessage() ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CoreUI and necessary plugins-->
    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="vendors/simplebar/js/simplebar.min.js"></script>
  </body>
</html>
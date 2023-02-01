<?php
use yii\helpers\Url;

$session = \Yii::$app->session;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Portal · QuickNav</title>
  <!-- Vendors styles-->
  <link rel="stylesheet" href="<?= Url::to('@web/coreui/vendors/simplebar/css/simplebar.css', true) ?>">
  <link rel="stylesheet" href="<?= Url::to('@web/coreui/css/vendors/simplebar.css', true) ?>">
  <!-- Main styles for this application-->
  <link href="<?= Url::to('@web/coreui/css/style.css') ?>" rel="stylesheet">
  <style>
    .custom-alert {
      position: fixed;
      top: 1rem;
      width: 400px;
      right: 1rem;
      z-index: 1030;
    }
  </style>
</head>
<body>
  
  <?php if($session->hasFlash('success')): ?>
    <div class="alert custom-alert alert-success alert-dismissible fade show" role="alert">
      <?= $session->getFlash('success') ?> 
      <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <?php if($session->hasFlash('failed')): ?>
    <div class="alert custom-alert alert-danger alert-dismissible fade show" role="alert">
      <?= $session->getFlash('failed') ?> 
      <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- alert -->
  <?php if($session->hasFlash('notification.type') and $session->hasFlash('notification.message')): ?>
    <div class="alert custom-alert alert-<?= $session->getFlash('notification.type') ?> alert-dismissible fade show" role="alert">
      <?= $session->getFlash('notification.message') ?>
      <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?= $this->render('_sidebar') ?>

  <div class="wrapper d-flex flex-column min-vh-100 bg-light">
    <?= $this->render('_header') ?>

    <div class="body flex-grow-1 px-3">
      <div class="container-lg">
        <?= $content ?>
      </div>
    </div>

    <!-- footer -->
  </div>

  <!-- CoreUI and necessary plugins-->
  <script src="<?= Url::to('@web/coreui/vendors/@coreui/coreui/js/coreui.bundle.min.js', true) ?>"></script>
  <script src="<?= Url::to('@web/coreui/vendors/simplebar/js/simplebar.min.js', true) ?>"></script>
</body>
</html>
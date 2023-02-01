<?php
use yii\helpers\Url;
?>

<div class="sidebar sidebar-fixed" id="sidebar">
  <div class="sidebar-brand d-md-flex">
    <p class="h5 m-0">QuickNav</p>
  </div>
  <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
    <li class="nav-item"><a class="nav-link" href="<?= Url::toRoute('home/index', true) ?>">
        <svg class="nav-icon"><use xlink:href="<?= Url::to('@web/coreui/vendors/@coreui/icons/svg/free.svg#cil-speedometer', true) ?>"></use>
        </svg> Home</a></li>
    <li class="nav-title">Pengujian</li>
    <!-- <li class="nav-item"><a class="nav-link" href="<?= Url::toRoute('user-testing/index', true) ?>">
        <svg class="nav-icon"><use xlink:href="<?= Url::to('@web/coreui/vendors/@coreui/icons/svg/free.svg#cil-home', true) ?>"></use>
        </svg> Permulaan</a></li> -->
    <li class="nav-item"><a class="nav-link" href="<?= Url::toRoute('task/index', true) ?>">
        <svg class="nav-icon"><use xlink:href="<?= Url::to('@web/coreui/vendors/@coreui/icons/svg/free.svg#cil-task', true) ?>"></use>
        </svg> Tugas</a></li>
    <!-- <li class="nav-item"><a class="nav-link" href="<?= Url::toRoute('user-testing/help', true) ?>">
        <svg class="nav-icon"><use xlink:href="<?= Url::to('@web/coreui/vendors/@coreui/icons/svg/free.svg#cil-book', true) ?>"></use>
        </svg> Panduan</a></li> -->
  </ul>
  <!-- <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button> -->
</div>
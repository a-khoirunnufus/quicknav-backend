<?php
use yii\helpers\Url;
?>

<div class="sidebar sidebar-fixed" id="sidebar">
  <div class="sidebar-brand d-flex flex-column">
    <p class="h5 m-0">QuickNav</p>
    <p class="text-light m-0" style="font-size: .9rem">Fasilitator</p>
  </div>
  <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
    <li class="nav-item"><a class="nav-link" href="<?= Url::toRoute('home/index', true) ?>">
        Home</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= Url::toRoute('participant/index', true) ?>">
        Partisipan</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= Url::toRoute('task/select-participant', true) ?>">
        Tugas</a></li>
  </ul>
  <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
</div>
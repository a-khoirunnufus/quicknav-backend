<?php
use yii\helpers\Url;

$this->params['breadcrumbs'] = [
  [ 'label' => 'Home', 'link' => null, 'active' => true ],
];
?>

<div class="card mb-4">
  <div class="card-body">
    Selamat datang, Fasilitator <strong><?= \Yii::$app->user->identity->name ?></strong>
    <hr>
    <div class="row">
      <div class="col-auto">
        <a href="<?= Url::toRoute('home/flush-cache') ?>" class="btn btn-warning">Bersihkan Cache</a>
      </div>
    </div>
  </div>
</div>
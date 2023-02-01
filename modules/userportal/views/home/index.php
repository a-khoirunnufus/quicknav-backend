<?php
$this->params['breadcrumbs'] = [
  [ 'label' => 'Home', 'link' => null, 'active' => true ],
];
?>

<div class="card mb-4">
  <div class="card-body">
    Selamat datang, <strong><?= \Yii::$app->user->identity->name ?></strong>
  </div>
</div>
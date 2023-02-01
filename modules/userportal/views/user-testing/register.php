<?php
use yii\helpers\Url;
use app\models\User;

$csrfToken = \Yii::$app->request->csrfToken;
$identity = \Yii::$app->user->identity;

$this->params['breadcrumbs'] = [
  [ 'label' => 'Pengujian', 'link' => Url::toRoute('user-testing/index'), 'active' => false ],
  [ 'label' => 'Daftar sebagai partisipan', 'link' => Url::toRoute('user-testing/register'), 'active' => true ],
];
?>

<div class="card shadow-sm" style="width: fit-content; margin: 0 auto;">
  <div class="card-body">
    <h4 class="card-title mb-0">Daftar sebagai partisipan</h4>

    <form action="<?= Url::toRoute('user-testing/add-participant') ?>" method="post" style="width: 400px; margin-top: 40px">
      <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
      <input type="hidden" name="user_id" value="<?= $identity->id ?>">
      <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= $identity->name ?>">
      </div>
      <div class="mb-3">
        <label for="age" class="form-label">Umur</label>
        <input type="number" class="form-control" id="age" name="age">
      </div>
      <div class="mb-4">
        <label for="job" class="form-label">Pekerjaan</label>
        <input type="text" class="form-control" id="job" name="job">
      </div>
      <button type="submit" class="btn btn-primary w-100 fw-semibold">Daftar</button>
    </form>
  </div>
</div>
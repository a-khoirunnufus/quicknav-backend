<?php
use yii\helpers\Url;

// TODO: Breadcrumb data
$this->params['breadcrumbs'] = [
  [ 'label' => 'Pengujian', 'link' => Url::toRoute('user-testing/index'), 'active' => false ],
  [ 'label' => 'Tugas', 'link' => Url::toRoute('task/index'), 'active' => false ],
  [ 'label' => 'Deskripsik File', 'link' => null, 'active' => true ],
];

$csrfToken = \Yii::$app->request->csrfToken;
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="card-title mb-0">Deskripsikan File Berikut</h4>

    <ul class="list-group list-group-flush" style="margin-top: 40px">
      <li class="list-group-item">
        <div class="row">
          <div class="col-sm-4">
            Nama file
          </div>
          <div class="col-sm-8">
            <?= $file['name'] ?>
          </div>
        </div>
      </li>
      <li class="list-group-item">
        <div class="row">
          <div class="col-sm-4">
            Lokasi file
          </div>
          <div class="col-sm-8">
            <?= $target->path_to_file ?>
          </div>
        </div>
      </li>
      <li class="list-group-item">
        <div class="row">
          <div class="col-sm-4">
            Terakhir diakses pada
          </div>
          <div class="col-sm-8">
            <?= date('j M Y', strtotime($file['viewedByMeTime'])) ?>
          </div>
        </div>
      </li>
      <li class="list-group-item">
        <div class="row align-items-center">
          <div class="col-sm-4">
            Buka di google drive
          </div>
          <div class="col-sm-8">
            <a href="https://drive.google.com/drive/folders/<?= $file['parent'] ?>" target="_blank" role="button" class="btn btn-outline-primary">Buka</a>
          </div>
        </div>
      </li>
    </ul>

    <form action="<?= Url::toRoute('task/describe-target') ?>" method="post" class="mt-3">
      <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
      <input type="hidden" name="target_id" value="<?= $target['id'] ?>">
      <div class="mb-3">
        <textarea class="form-control" name="description" id="deskripsi-file" rows="3" placeholder="tuliskan sesuatu yang menggambarkan file..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </div>
</div>
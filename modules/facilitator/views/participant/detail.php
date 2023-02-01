<?php
use yii\helpers\Url;

$this->params['breadcrumbs'] = [
  [ 'label' => 'Partisipan', 'link' => Url::toRoute('participant/index'), 'active' => false ],
  [ 'label' => 'Detail', 'link' => null, 'active' => true ],
];

$csrfToken = \Yii::$app->request->csrfToken;
$session = \Yii::$app->session;
$user = app\models\User::getUserByParticipantId($participant['id']);
?>

<div class="mb-4">
  <h4 class="card-title mb-0">Detail Partisipan</h4>
  <div class="small text-medium-emphasis">Partisipan <?= $participant['name'] ?></div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="d-flex flex-row align-items-center justify-content-between" style="margin-bottom: 40px">
      <h5 class="card-title mb-0">Karakteristik Drive Partisipan</h5>
    </div>    
    
    <table class="table">
      <tbody>
        <tr>
          <th style="width: 300px" scope="row">Visualisasi hirariki file</th>
          <td><a target="_blank" href="<?= Url::toRoute([
              'participant/display-tree-view',
              'participant_id' => $participant['id']]) ?>" class="btn btn-primary btn-sm"
              >Lihat</a></td>
        </tr>
        <tr>
          <th style="width: 300px" scope="row">Jumlah seluruh file</th>
          <td><?= $numberOfFiles ?></td>
        </tr>
        <tr>
          <th style="width: 300px" scope="row">Jumlah file pada kedalaman setiap kedalaman</th>
          <td>
            <ul class="list-group list-group-horizontal">
              <?php foreach($fileCountsPerDepth as $key => $value): ?>
                <li class="list-group-item"><?=$key?>: <?=$value?></li>
              <?php endforeach; ?>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">    
    <h5 class="card-title" style="margin-bottom: 40px">Biodata</h5>
    <table class="table" style="margin-top: 40px">
      <tbody>
        <tr>
          <th style="width: 300px" scope="row">Nama</th>
          <td><?= $user['name'] ?></td>
        </tr>
        <tr>
          <th style="width: 300px" scope="row">Email</th>
          <td><?= $user['email'] ?></td>
        </tr>
        <tr>
          <th style="width: 300px" scope="row">Umur</th>
          <td><?= $participant['age'] ?></td>
        </tr>
        <tr>
          <th style="width: 300px" scope="row">Pekerjaan</th>
          <td><?= $participant['job'] ?></td>
        </tr>
        <tr>
          <th style="width: 300px" scope="row">Perizinan Google Drive</th>
          <td>
            <?php if($user['g_access_token'] != null): ?>
              <span class="text-success">Akses sudah diizinkan</span>
            <?php else: ?>
              <span class="text-warning">Akses belum diizinkan</span>
            <?php endif; ?>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
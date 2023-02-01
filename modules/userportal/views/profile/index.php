<?php
use yii\helpers\Url;

$this->title = 'Profil';
$identity = Yii::$app->user->identity;

$this->params['breadcrumbs'] = [
  [ 'label' => 'Home', 'link' => Url::toRoute('home/index'), 'active' => false ],
  [ 'label' => 'Profil', 'link' => null, 'active' => true ],
];

?>

<style>
  #profile-table td {
    padding: .5rem;
    vertical-align: top;
  }
  #profile-table tr td:nth-child(1) {
    text-align: left;
    font-weight: 700;
  }
</style>

<div class="card shadow-sm" style="width: fit-content; margin: 0 auto;">
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        <table id="profile-table">
          <tr>
            <td>Nama</td>
            <td><?= $identity->name ?></td>
          </tr>
          <tr>
            <td>Email</td>
            <td><?= $identity->email ?></td>
          </tr>
          <tr>
            <td>Perizinan</td>
            <td>
              <?php if($identity->g_access_token == null): ?>
                <p>Aplikasi tidak diizinkan untuk mengakses google drive anda.</p>
                <a href="<?= Url::toRoute('auth/add-google-drive-access') ?>" class="btn btn-outline-primary btn-sm">Izinkan Akses Google Drive</a>
              <?php else: ?>
                <span>Aplikasi diizinkan untuk mengakses google drive anda.</span>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

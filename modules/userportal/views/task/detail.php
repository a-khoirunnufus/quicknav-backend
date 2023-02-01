<?php
use yii\helpers\Url;

// TODO: Breadcrumb data
$this->params['breadcrumbs'] = [
  [ 'label' => 'Pengujian', 'link' => Url::toRoute('user-testing/index'), 'active' => false ],
  [ 'label' => 'Tugas', 'link' => Url::toRoute('task/index'), 'active' => false ],
  [ 'label' => 'Detail Tugas', 'link' => null, 'active' => true ],
];
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="card-title mb-0">Detail Tugas</h4>
    <div class="small text-medium-emphasis">GH - Antarmuka Google Drive dengan Petunjuk</div>

    <?php if(count($items) === 0): ?>
      <div class="alert alert-warning" role="alert" style="margin-top: 40px">
        <div class="d-flex">
          <svg style="height: 20px; width: 20px;"><use xlink:href="<?= Url::to('@web/coreui/vendors/@coreui/icons/svg/free.svg#cil-warning', true) ?>"></use></svg>
          <span class="ms-3">Belum ada item untuk tugas ini, silahkan hubungi fasilitator.</span>
        </div>
      </div>
    <?php else: ?>
      <table class="table" id="table-task-list" style="margin-top: 40px; font-size: .9rem">
        <thead>
          <tr class="table-light">
            <th class="text-center" scope="col">Kode</th>
            <th scope="col">Keterangan</th>
            <th class="text-center" scope="col">Status</th>
            <th scope="col">Dilakukan pada</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($items as $item): ?>
            <tr>
              <th class="text-center" scope="row"><?= $item['code'] ?></th>
              <td>Pergi ke file dengan deskripsi: <?= $item['description'] ?></td>
              <td class="text-center">
                <?php if($item['status'] == 'NOT_COMPLETE'): ?>
                  <span class="badge text-bg-secondary text-light">Belum selesai</span></td>
                <?php elseif($item['status'] == 'PENDING'): ?>
                  <span class="badge text-bg-warning text-light">Pending</span></td>
                <?php elseif($item['status'] == 'COMPLETED'): ?>
                  <span class="badge text-bg-success text-light">Selesai</span></td>
                <?php endif; ?>
              <td><?= $item['run_at'] ? date('j M Y H:i:s', strtotime($item['run_at'])) : '-' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </div>
</div>

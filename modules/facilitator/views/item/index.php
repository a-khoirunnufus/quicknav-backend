<?php

use yii\helpers\Url;

$this->params['breadcrumbs'] = [
  [ 'label' => 'Pilih Partisipan', 'link' => Url::toRoute('task/select-participant'), 'active' => false ],
  [ 'label' => 'Daftar Tugas', 'link' => Url::toRoute(['task/list', 'participant_id' => $participant['id']]), 'active' => false ],
  [ 'label' => 'Daftar Item Tugas', 'link' => null, 'active' => true ],
];
?>

<div class="mb-4">
  <h4 class="card-title mb-0">Item Tugas</h4>
  <div class="small text-medium-emphasis">Partisipan <?= $participant['name'] ?></div>
  <div class="small text-medium-emphasis">Tugas <?= $task['code'] ?> - <?= $task['name'] ?></div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="validation-tab" data-coreui-toggle="tab" data-coreui-target="#validation-tab-pane" type="button" role="tab"><h5 class="mb-0">Validasi</h5></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="report-tab" data-coreui-toggle="tab" data-coreui-target="#report-tab-pane" type="button" role="tab"><h5 class="mb-0">Laporan</h5></button>
      </li>
    </ul>
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="validation-tab-pane" role="tabpanel" tabindex="0">
        <table class="table" id="table-task-list" style="margin-top: 40px; font-size: .9rem">
          <thead>
            <tr class="table-light">
              <th class="text-center" scope="col">Kode</th>
              <th scope="col">File</th>
              <th class="text-center" scope="col">Status</th>
              <th scope="col">Dilakukan pada</th>
              <th class="text-center" scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($items as $item): ?>
              <tr>
                <th class="text-center" scope="row"><?= $item['code'] ?></th>
                <td><?= $item['file_name'] ?></td>
                <td class="text-center">
                  <?php if($item['status'] == 'NOT_COMPLETE'): ?>
                    <span class="badge text-bg-secondary text-light">Belum selesai</span></td>
                  <?php elseif($item['status'] == 'PENDING'): ?>
                    <span class="badge text-bg-warning text-light">Pending</span></td>
                  <?php elseif($item['status'] == 'COMPLETED'): ?>
                    <span class="badge text-bg-success text-light">Selesai</span></td>
                  <?php endif; ?>
                <td><?= $item['run_at'] ? date('j M Y H:i:s', strtotime($item['run_at'])) : '-' ?></td>
                <td class="text-center">
                  <a class="btn btn-primary btn-sm <?= $item['status'] == 'NOT_COMPLETE' ? 'disabled' : '' ?>"
                      href="<?= Url::toRoute(['item/validation', 
                        'participant_id' => $participant['id'], 
                        'task_id' => $task['id'],
                        'task_item_id' => $item['id'],
                      ]) ?>"
                      aria-disabled="<?= $item['status'] == 'NOT_COMPLETE' ? 'true' : 'false' ?>"
                      >Validasi</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="tab-pane fade" id="report-tab-pane" role="tabpanel" tabindex="0">
        <?= $this->render('_report', [
          'taskId' => $task['id'],
          'itemReports' => $itemReports,
        ]) ?>
      </div>
    </div>
  </div>
</div>
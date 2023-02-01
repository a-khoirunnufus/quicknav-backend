<?php
use yii\helpers\Url;
?>


<a href="<?= Url::toRoute(['item/generate-report', 'task_id' => $taskId]) ?>" class="btn btn-primary btn-sm mt-3">Generate Report</a>
<a href="<?= Url::toRoute(['item/download-report', 'task_id' => $taskId]) ?>" class="btn btn-success btn-sm mt-3 text-white">Download Report</a>

<table class="table mt-3" id="table-task-list" style="font-size: .9rem">
  <thead>
    <tr class="table-light">
      <th scope="col">#</th>
      <th scope="col">Kode</th>
      <th scope="col">Nama File</th>
      <th scope="col">Status Tugas</th>
      <th scope="col">Waktu Penyelesaian</th>
      <th scope="col">Jumlah Langkah</th>
      <th scope="col">Laporan digenerate pada</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($itemReports as $item): ?>
      <tr>
        <th scope="row"><?= $item['order'] ?></th>
        <td scope="row"><?= $item['code'] ?></td>
        <td><?= $item['file_name'] ?></td>
        <td><?= intval($item['is_success']) === 1 ? 'berhasil' : 'gagal' ?></td> 
        <td><?= $item['time_completion'] ?></td>
        <td><?= $item['number_of_step'] ?></td>
        <td><?= $item['generate_at'] ?></td>
        <td>
          <a href="<?= Url::toRoute([
              'item/report-details', 
              'task_id' => $taskId, 
              'details' => $item['details']
            ]) ?>"
            class="btn btn-info btn-sm">Detail</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
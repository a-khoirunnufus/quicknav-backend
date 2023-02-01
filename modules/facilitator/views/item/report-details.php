<?php
use yii\helpers\Url;

$task = app\modules\facilitator\models\UtTask::findOne($taskId);
$drive = new app\components\DriveFileUt($task->participant_id);

$this->params['breadcrumbs'] = [
  [ 'label' => 'Pilih Partisipan', 'link' => Url::toRoute('task/select-participant'), 'active' => false ],
  [ 'label' => 'Daftar Tugas', 'link' => Url::toRoute(['task/list', 'participant_id' => $task->participant_id]), 'active' => false ],
  [ 'label' => 'Daftar Item Tugas', 'link' => Url::toRoute(['item/index', 'participant_id' => $task->participant_id, 'task_id' => $task->id]), 'active' => false ],
  [ 'label' => 'Detail Laporan', 'link' => null, 'active' => true ],
];
?>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <ul class="list-group list-group-horizontal" style="width: 100%; overflow-x: auto">
      <?php foreach($data as $item): ?>
        <li class="list-group-item" style="white-space: nowrap">
          <span style="max-width: 300px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            <?php 
            $file = $drive->getFileById($item['file_id']);
            if(isset($file['name'])) {
              echo $file['name'];
            } else {
              echo 'NOT_FOUND';
            }
            ?>
          </span><br>
          <span><?= $item['time'] ?> Detik</span><br>
          <span class="badge text-bg-light"><?= strtolower($item['source']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
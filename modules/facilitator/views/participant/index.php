<?php
use yii\helpers\Url;

$this->params['breadcrumbs'] = [
  [ 'label' => 'Pilih Partisipan', 'link' => Url::toRoute('task/select-participant'), 'active' => true ],
];
?>

<div class="card shadow-sm">
  <div class="card-body">
    <h4 class="card-title mb-0">Daftar Partisipan</h4>

    <table class="table" id="table-task-list" style="margin-top: 40px">
      <thead>
        <tr class="table-light">
          <th scope="col">Nama</th>
          <th scope="col">Umur</th>
          <th scope="col">Pekerjaan</th>
          <th class="text-center" scope="col"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($participants as $participant): ?>
          <tr>
            <td><?= $participant['name'] ?></td>
            <td><?= $participant['age'] ?></td>
            <td><?= $participant['job'] ?></td>
            <td class="text-center"><a href="<?= Url::toRoute(['participant/detail', 'participant_id' => $participant['id']]) ?>" class="btn btn-primary btn-sm">
                Detail</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
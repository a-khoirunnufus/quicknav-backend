<?php

namespace app\modules\facilitator\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\modules\facilitator\models\UtTask;
use app\components\DriveFileUt;
use app\modules\facilitator\models\UtTaskTarget;
use app\modules\facilitator\models\UtTaskItem as Item;

class TaskController extends Controller
{
  public function behaviors()
  {
    return [
      'access' => [
        'class' => AccessControl::class,
        'rules' => [
          [
            'allow' => true,
            'roles' => ['@'],
            'matchCallback' => function ($rule, $action) {
              return Yii::$app->user->identity->email === 'a.khoirunnufus@gmail.com';
            }
          ],
        ],
      ],
    ];
  }
  
  /**
   * Page related actions
   */

  public function actionSelectParticipant()
  {
    $participants = (new \yii\db\Query())
      ->select(['id', 'name', 'age', 'job'])
      ->from('ut_participant')
      ->all();

    return $this->render('select-participant', [
      'participants' => $participants,
    ]);
  }

  public function actionList()
  {
    $pid = \Yii::$app->request->get('participant_id');
    $participant = (new \yii\db\Query())
      ->select(['id', 'name'])
      ->from('ut_participant')
      ->where(['id' => $pid])
      ->one();
    $tasks = (new \yii\db\Query())
      ->select(['id', 'code', 'name', 'order', 'interface', 'hint_visible', 'is_lock'])
      ->from('ut_task')
      ->where(['participant_id' => $pid])
      ->orderBy(['order' => SORT_ASC])
      ->all();
    
    return $this->render('list', [
      'participant' => $participant,
      'tasks' => $tasks,
    ]);
  }

  public function actionSetup()
  {
    $pId = \Yii::$app->request->get('participant_id');
    $tId = \Yii::$app->request->get('task_id');
    $drive = new DriveFileUt($pId);
    
    $participant = (new \yii\db\Query())
      ->select(['id', 'name'])
      ->from('ut_participant')
      ->where(['id' => $pId])
      ->one();
    $task = UtTask::findOne($tId);

    $numberOfFiles = $drive->numberOfFiles;
    $filesPerDepth = $drive->filesPerDepth;
    $fileCountsPerDepth = $drive->fileCountsPerDepth;

    // selected files 1
    $cache = Yii::$app->cache;
    $cacheKey = $pId.'_ut_selected_files_1';
    $selectedFiles1 = $cache->get($cacheKey);
    $selectedFiles1Arr = [];
    if($selectedFiles1) {
      foreach($selectedFiles1 as $fileId) {
        $res = $drive->getFileById($fileId);
        if($res) {
          $pathToFile = $drive->getPathToFile($drive->fileHierarchy, $fileId);
          $res['depth'] = count($pathToFile);
          $res['pathToFile'] = implode("/", $pathToFile);
          $selectedFiles1Arr[] = $res;
        }
      }
    }

    // final targets
    $targets = UtTaskTarget::find()
      ->where(['task_id' => $tId])
      ->all();
    $targetsExtended = [];
    foreach($targets as $item) {
      $file = $drive->getFileById($item['file_id']);
      $newTarget['id'] = $item['id'];
      $newTarget['name'] = $file['name'];
      $newTarget['depth'] = $item['file_depth'];
      $newTarget['path_to_file'] = $item['path_to_file'];
      $newTarget['viewed_by_me_time'] = $file['viewedByMeTime'];
      $newTarget['frequency'] = $item['frequency'];
      $newTarget['status'] = $item['status'];
      $newTarget['description'] = $item['description'];
      $targetsExtended[] = $newTarget;
    }

    // final items
    $items = Item::find()
      ->where(['task_id' => $tId])
      ->orderBy('order ASC')
      ->asArray()
      ->all();

    return $this->render('setup', [
      'participant' => $participant,
      'numberOfFiles' => $numberOfFiles,
      'filesPerDepth' => $filesPerDepth,
      'fileCountsPerDepth' => $fileCountsPerDepth,
      'selectedFiles1' => $selectedFiles1Arr,
      'task' => $task,
      'targets' => $targetsExtended,
      'items' => $items,
    ]);
  }

  /**
   * Resource related actions
   */

  public function actionAddTask()
  {
    $postData = \Yii::$app->request->post();

    try{
      $utTask = new UtTask();
      $utTask->code = $postData['code'];
      $utTask->name = $postData['name'];
      $utTask->order = $postData['order'];
      $utTask->interface = $postData['interface'];
      $utTask->hint_visible = $postData['hint_visible'];
      $utTask->is_lock = $postData['is_lock'];
      $utTask->participant_id = $postData['participant_id'];
      $utTask->save();
      Yii::$app->session->setFlash('success', 'Tugas berhasil ditambahkan.');
    } catch (\Exception $e) {
      Yii::$app->session->setFlash('failed', 'Tugas gagal ditambahkan.');
    }
    
    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionUpdateTask()
  {
    $postData = \Yii::$app->request->post();

    try{
      $utTask = UtTask::findOne($postData['task_id']);
      $utTask->code = $postData['code'];
      $utTask->name = $postData['name'];
      $utTask->order = $postData['order'];
      $utTask->interface = $postData['interface'];
      $utTask->hint_visible = $postData['hint_visible'];
      $utTask->is_lock = $postData['is_lock'];
      $utTask->participant_id = $postData['participant_id'];
      $utTask->save();
      Yii::$app->session->setFlash('success', 'Tugas berhasil diupdate.');
    } catch (\Exception $e) {
      Yii::$app->session->setFlash('failed', 'Tugas gagal diupdate.');
    }
    
    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionDeleteTask()
  {
    $taskId = \Yii::$app->request->post('task_id');

    try{
      $utTask = UtTask::findOne($taskId);
      $utTask->delete();
      Yii::$app->session->setFlash('success', 'Tugas berhasil dihapus.');
    } catch (\Exception $e) {
      Yii::$app->session->setFlash('failed', 'Tugas gagal dihapus.');
    }
    
    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionLoadDefaultTask()
  {
    $pId = \Yii::$app->request->post('participant_id');

    $defaultTasks = (new \yii\db\Query())
      ->select(['code', 'name', 'order', 'interface', 'hint_visible'])
      ->from('ut_task_ref')
      ->orderBy(['order' => SORT_ASC])
      ->all();

    $transaction = UtTask::getDb()->beginTransaction();
    try {
      foreach($defaultTasks as $task) {
        $utTask = new UtTask();
        $utTask->code = $task['code'];
        $utTask->name = $task['name'];
        $utTask->order = $task['order'];
        $utTask->interface = $task['interface'];
        $utTask->hint_visible = $task['hint_visible'];
        $utTask->participant_id = $pId;
        $utTask->save();
      }
      $transaction->commit();
      Yii::$app->session->setFlash('success', 'Tugas berhasil diload.');
    } catch(\Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Tugas gagal diload.');
    } catch(\Throwable $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Tugas gagal diload.');
    }
    
    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionSelectFile1()
  {
    $pId = \Yii::$app->request->post('participant_id');
    $selectedFiles = \Yii::$app->request->post('selected_files');
    $selectedFileIds = explode(',', $selectedFiles);
      
    // save to cache
    $cache = Yii::$app->cache;
    $cacheKey = $pId.'_ut_selected_files_1';
    $cache->set($cacheKey, $selectedFileIds, 3600);

    // redirect back
    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionSetFinalTargets()
  {
    $tId = \Yii::$app->request->post('task_id');
    $pId = \Yii::$app->request->post('participant_id');
    $finalTargetsString = \Yii::$app->request->post('final_targets');
    $drive = new DriveFileUt($pId);

    $finalTargetsRaw = explode(',', $finalTargetsString);
    $finalTargets = [];
    foreach($finalTargetsRaw as $targetString) {
      $target = explode('@', $targetString);
      $finalTargets[] = [
        'fileId' => $target[0],
        'frequency' => intval($target[1]),
      ];
    }

    $transaction = UtTaskTarget::getDb()->beginTransaction();
    try {
      // save to db
      foreach($finalTargets as $item) {
        $target = new UtTaskTarget();
        $target->file_id = $item['fileId'];
  
        $pathToFile = $drive->getPathToFile($drive->fileHierarchy, $item['fileId']);
        $target->path_to_file = implode(" > ", $pathToFile);
        $target->file_depth = count($pathToFile);
        
        $target->frequency = $item['frequency'];
        $target->task_id = $tId;
        $target->status = 'valid';
        $target->save();
      }
      $transaction->commit();
      Yii::$app->session->setFlash('success', 'Target berhasil disimpan.');
    } catch(\Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Target gagal disimpan.');
    } catch(\Throwable $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Target gagal disimpan.');
    }

    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionTargetValidation()
  {
    $request = Yii::$app->request;
    $targetId = $request->post('target_id');
    $status = $request->post('status');

    try{
      $target = UtTaskTarget::findOne($targetId);
      $target->status = $status;
      $target->save();
      Yii::$app->session->setFlash('success', 'Target berhasil diproses.');
    } catch (\Exception $e) {
      Yii::$app->session->setFlash('failed', 'Target gagal diproses.');
    }

    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionAutoGenerateItems()
  {
    $request = Yii::$app->request;
    
    $taskId = $request->post('task_id');
    $task = UtTask::findOne($taskId);

    $pId = $request->get('participant_id');
    $drive = new DriveFileUt($pId);

    $transaction = UtTaskTarget::getDb()->beginTransaction();
    try {
      // delete all items for this task
      Item::deleteAll(['task_id' => $taskId]);
      
      // get targets with task_id
      $targets = UtTaskTarget::find()
        ->where([
          'task_id' => $taskId,
          'status' => 'valid'
        ])
        ->all();
      
      // populate order value
      $targetFreqSum = 0;
      foreach($targets as $target) {
        $targetFreqSum += $target->frequency;
      }
      $orders = range(1,$targetFreqSum);
      shuffle($orders);

      // populate items from targets
      foreach($targets as $target) {
        $file = $drive->getFileById($target->file_id);

        for ($i=0; $i < $target->frequency; $i++) { 
          $order = array_pop($orders);

          $item = new Item();
          $item->code = $task->code."-".$order;
          $item->file_id = $target->file_id;
          $item->file_name = $file['name'];
          $item->file_depth = $target->file_depth;
          $item->path_to_file = $target->path_to_file;
          $item->description = $target->description;
          $item->order = $order;
          $item->interface = $task->interface;
          $item->hint_visible = $task->hint_visible;
          $item->task_id = $taskId;
          $item->status = 'NOT_COMPLETE';
          $item->run_at = null;
          $item->save();
        }
      }
      
      $transaction->commit();
      Yii::$app->session->setFlash('success', 'Berhasil generate item.');
    } catch(\Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Gagal generate item.');
    } catch(\Throwable $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Gagal generate item.');
    }

    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionClearTarget()
  {
    $request = Yii::$app->request;
    $taskId = $request->post('task_id');

    $transaction = UtTaskTarget::getDb()->beginTransaction();
    try {
      UtTaskTarget::deleteAll(['task_id' => $taskId]);

      $transaction->commit();
      Yii::$app->session->setFlash('success', 'Target berhasil dikosongkan.');
    } catch(\Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Target gagal dikosongkan.');
    } catch(\Throwable $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Target gagal dikosongkan.');
    }

    return $this->redirect(Yii::$app->request->referrer);
  }

  public function actionCopyTaskTarget()
  {
    $request = Yii::$app->request;
    $sourceTaskId = $request->post('source_task_id');
    $destTaskId = $request->post('dest_task_id');

    $sourceTargets = UtTaskTarget::find()
      ->where([
        'task_id' => $sourceTaskId,
        'status' => 'valid',
      ])
      ->all();

    $transaction = UtTaskTarget::getDb()->beginTransaction();
    try {
      foreach($sourceTargets as $sourceTarget) {
        $destTarget = new UtTaskTarget();
        $destTarget->file_id = $sourceTarget->file_id;
        $destTarget->file_depth = $sourceTarget->file_depth;
        $destTarget->path_to_file = $sourceTarget->path_to_file;
        $destTarget->description = $sourceTarget->description;
        $destTarget->frequency = $sourceTarget->frequency;
        $destTarget->status = $sourceTarget->status;
        $destTarget->task_id = $destTaskId;
        $destTarget->save();
      }

      $transaction->commit();
      Yii::$app->session->setFlash('success', 'Berhasil copas target.');
    } catch(\Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Gagal copas target.');
    } catch(\Throwable $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Gagal copas target.');
    }

    return $this->redirect(Yii::$app->request->referrer);
  }

}
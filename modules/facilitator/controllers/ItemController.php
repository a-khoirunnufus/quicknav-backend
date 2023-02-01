<?php

namespace app\modules\facilitator\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\helpers\Url;
use app\modules\facilitator\models\UtParticipant as Participant;
use app\modules\facilitator\models\UtTask as Task;
use app\components\DriveFileUt as Drive;
use app\modules\facilitator\models\UtTaskItem as Item;
use app\modules\facilitator\models\UtTaskItemLog as Log;
use app\modules\facilitator\models\UtTaskItemLogFinal as LogFinal;
use app\modules\facilitator\models\UtTaskItemReport as ItemReport;
use yii2tech\csvgrid\CsvGrid;
use yii\data\ArrayDataProvider;

class ItemController extends Controller
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

  public function actionIndex()
  {
    $request = Yii::$app->request;
    $paramParticipantId = $request->get('participant_id');
    $participant = (new \yii\db\Query())
      ->select(['id', 'name'])
      ->from('ut_participant')
      ->where(['id' => $paramParticipantId])
      ->one();

    $paramTaskId = $request->get('task_id');
    $task = Task::findOne(intval($paramTaskId));

    $items = Item::find()
      ->where(['task_id' => intval($paramTaskId)])
      ->orderBy('order ASC')
      ->all();

    // items report data
    $itemReports = ItemReport::find()
      ->where(['task_id' => $paramTaskId])
      ->orderBy('order ASC')
      ->all();

    return $this->render('index', [
      'participant' => $participant,
      'task' => $task,
      'items' => $items,
      'itemReports' => $itemReports,
    ]);
  }

  public function actionValidation()
  {
    $request = Yii::$app->request;
    $paramParticipantId = $request->get('participant_id');
    $participant = (new \yii\db\Query())
      ->select(['id', 'name'])
      ->from('ut_participant')
      ->where(['id' => $paramParticipantId])
      ->one();

    $paramTaskId = $request->get('task_id');
    $task = Task::findOne(intval($paramTaskId));
    
    $paramTaskItemId = $request->get('task_item_id');
    $item = Item::findOne(intval($paramTaskItemId));

    $logs = Log::find()
      ->where(['task_item_id' => $paramTaskItemId])
      ->orderBy('id ASC')
      ->all();

    return $this->render('validation', [
      'participant' => $participant,
      'task' => $task,
      'item' => $item,
      'logs' => $logs,
    ]);
  }

  public function actionValidate()
  {
    $request = Yii::$app->request;
    $participantId = $request->get('participant_id');
    $taskId = $request->get('task_id');
    $taskItemId = $request->post('task_item_id');
    $taskItemLogIds = $request->post('task_item_log_id');

    $transaction = LogFinal::getDb()->beginTransaction();
    try {
      LogFinal::deleteAll(['task_item_id' => $taskItemId]);

      foreach($taskItemLogIds as $taskItemLogId) {
        $log = Log::findOne(intval($taskItemLogId));
        $logFinal = new LogFinal();
        $logFinal->task_item_id = $taskItemId;
        $logFinal->task_item_log_id = $taskItemLogId;
        $logFinal->time = $log->time;
        $logFinal->save();
      }

      $item = Item::findOne($taskItemId);
      $item->status = 'COMPLETED';
      $item->save();
      
      $transaction->commit();
      Yii::$app->session->setFlash('success', 'Berhasil menyimpan data.');
    } catch(\Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Gagal menyimpan data.');
    } catch(\Throwable $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Gagal menyimpan data.');
    }
    
    return $this->redirect(Url::toRoute([
      'item/index', 
      'participant_id' => $participantId,
      'task_id' => $taskId,
    ]));
  }

  public function actionUnvalidate()
  {
    $request = Yii::$app->request;
    $participantId = $request->get('participant_id');
    $taskId = $request->get('task_id');
    $taskItemId = $request->post('task_item_id');

    try {
      $item = Item::findOne($taskItemId);
      $item->status = 'NOT_COMPLETE';
      $item->save();
      Yii::$app->session->setFlash('success', 'Berhasil mengupdate data.');
    } catch(\Exception $e) {
      Yii::$app->session->setFlash('failed', 'Gagal mengupdate data.');
    }
    
    return $this->redirect(Url::toRoute([
      'item/index', 
      'participant_id' => $participantId,
      'task_id' => $taskId,
    ]));
  }

  public function actionGenerateReport()
  {
    $request = Yii::$app->request;
    $paramTaskId = $request->get('task_id');

    $task = Task::findOne($paramTaskId);
    $drive = new Drive($task->participant_id);

    // foreach task item that have status COMPLETED
    $items = Item::find()
      ->where([
        'task_id' => $paramTaskId,
        'status' => 'COMPLETED',
      ])->all();

    $transaction = ItemReport::getDb()->beginTransaction();
    try {
      ItemReport::deleteAll(['task_id' => $paramTaskId]);

      foreach($items as $item) {
        // get item logs
        $logs = (new \yii\db\Query())
          ->select(['l.id', 'l.action', 'l.object', 'l.time'])
          ->from('ut_task_item_log l')
          ->join('RIGHT JOIN', 'ut_task_item_log_final lf', 'l.id = lf.task_item_log_id')
          ->where(['l.task_item_id' => $item['id']])
          ->orderBy('l.time ASC')
          ->all();

        $useAdaptiveInterface = false;
        $details = '';
        
        $totalTime = 0; // in seconds
        $prevTime = strtotime($logs[0]['time']);
        for ($i=0; $i < count($logs); $i++) { 
          $timeDif = 0;
          if($i >= 1) {
            $time = strtotime($logs[$i]['time']);
            $timeDif = $time - $prevTime;
            $totalTime += $timeDif;
            $prevTime = $time;
          }

          if($item['interface'] == 'GOOGLE_DRIVE') {
            $path = parse_url($logs[$i]['object'], PHP_URL_PATH);
            $path = explode('/', $path);
            $file_id = end($path);
            $source = 'STATIC';
          }
          elseif($item['interface'] == 'QUICKNAV') {
            $url = $logs[$i]['object'];
            $url = parse_url($url, PHP_URL_QUERY);
            $output;
            parse_str($url, $output);
            $file_id = $output['folder_id'];
            $source = strtoupper($output['source']);
          }

          if($source == 'ADAPTIVE') {
            $useAdaptiveInterface = true;
          }

          if($i < 1) {
            $details .= $timeDif.';'.$file_id.';'.$source;
          } else {
            $details .= '/'.$timeDif.';'.$file_id.';'.$source;
          }
        }

        // check task success
        $isSuccess = false;
        if($item['interface'] == 'QUICKNAV') {
          $url = $logs[count($logs)-2]['object'];
          $url = parse_url($url, PHP_URL_QUERY);
          $output; parse_str($url, $output);
          $folder_id = $output['folder_id'];
          if($folder_id == 'my-drive' || $folder_id == 'root') $folder_id = $drive->driveRootId;
          $file = $drive->getFileById($item['file_id']);
          if($file['parent'] == $folder_id) {
            $isSuccess = true;
          }
        } elseif($item['interface'] == 'GOOGLE_DRIVE') {
          $path = parse_url($logs[count($logs)-2]['object'], PHP_URL_PATH);
          $path = explode('/', $path);
          $folder_id = end($path);
          if($folder_id == 'my-drive') $folder_id = $drive->driveRootId;
          $file = $drive->getFileById($item['file_id']);
          if($file['parent'] == $folder_id) {
            $isSuccess = true;
          }
        }
  
        $timeCompletion = $totalTime;
        $numberOfStep = count($logs);
  
        $itemReport = new ItemReport();
        $itemReport->interface = $item->interface;
        $itemReport->code = $item->code;
        $itemReport->file_id = $item->file_id;
        $itemReport->file_name = $item->file_name;
        $itemReport->file_depth = $item->file_depth;
        $itemReport->path_to_file = $item->path_to_file;
        $itemReport->hint_visible = $item->hint_visible;
        $itemReport->description = $item->description;
        $itemReport->order = $item->order;
        $itemReport->task_item_id = $item->id;
        $itemReport->task_id = $item->task_id;
        $itemReport->is_success = $isSuccess;
        $itemReport->time_completion = $timeCompletion;
        $itemReport->number_of_step = $numberOfStep;
        $itemReport->use_adaptive_interface = $useAdaptiveInterface;
        $itemReport->details = $details;
        $itemReport->generate_at = date('Y-m-d H:i:s', time());
        $itemReport->save();
      }

      $transaction->commit();
      Yii::$app->session->setFlash('success', 'Berhasil generate report.');
    } catch(\Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Gagal generate report.');
    } catch(\Throwable $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('failed', 'Gagal generate report.');
    }
    
    return $this->redirect(Yii::$app->request->referrer);
    
  }

  public function actionDownloadReport()
  {
    $request = Yii::$app->request;
    $paramTaskId = $request->get('task_id');

    $task = Task::findOne($paramTaskId);
    $participant = Participant::findOne($task->participant_id);

    $itemReports = (new \yii\db\Query())
      ->select([
        't.code AS task_code', 
        'ir.code AS task_item_code', 
        'ir.file_name AS file_name',
        'ir.file_depth AS file_depth',
        'ir.is_success AS success', 
        'ir.time_completion AS time_completion',
        'ir.number_of_step AS number_of_step', 
        'ir.use_adaptive_interface AS use_adaptive_interface', 
        'ir.generate_at AS generated_at'
      ])
      ->from('ut_task_item_report ir')
      ->join('LEFT JOIN', 'ut_task t', 't.id = ir.task_id')
      ->where(['ir.task_id' => $paramTaskId])
      ->orderBy('ir.order ASC')
      ->all();

    $exporter = new CsvGrid([
      'dataProvider' => new ArrayDataProvider([
          'allModels' => $itemReports,
      ]),
    ]);

    $filename = "User $participant->name Task $task->code Report.csv";
    return $exporter->export()->send($filename);
  }

  public function actionReportDetails()
  {
    $paramTaskId = Yii::$app->request->get('task_id');
    $paramDetails = Yii::$app->request->get('details');
    $details = explode('/', $paramDetails);
    $data = [];
    foreach ($details as $item) {
      $temp = explode(';', $item);
      $data[] = [
        'time' => $temp[0],
        'file_id' => $temp[1],
        'source' => $temp[2],
      ];
    }

    return $this->render('report-details', [
      'taskId' => $paramTaskId,
      'data' => $data,
    ]);
  }

}
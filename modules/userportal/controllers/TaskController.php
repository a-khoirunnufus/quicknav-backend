<?php

namespace app\modules\userportal\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\User;
use app\components\DriveFileUt as Drive;
use app\modules\facilitator\models\UtParticipant as Participant;
use app\modules\facilitator\models\UtTask as Task;
use app\modules\facilitator\models\UtTaskTarget as Target;
use app\modules\facilitator\models\UtTaskItem as Item;

class TaskController extends Controller
{
  public function behaviors()
  {
    return [
      'access' => [
        'class' => AccessControl::class,
        'only' => ['index'],
        'rules' => [
          [
            'allow' => true,
            'actions' => ['index'],
            'roles' => ['@'],
          ]
        ],
      ],
    ];
  }

  public function actionIndex()
  {
    $identity = \Yii::$app->user->identity;
    $participant = Participant::findOne(['user_id' => $identity->id]);
    if(!$participant) {
      return $this->redirect(Url::toRoute('user-testing/register', true));
    }

    $tasks = Task::find()
      ->where(['participant_id' => $participant->id])
      ->all();

    // if there is target that not describe yet
    // foreach($tasks as $task) {
    //   if(!Target::isDescribeComplete($task['id'])) {
    //     return $this->redirect(Url::toRoute([
    //       'task/describe-target', 
    //       'task_id' => $task['id']
    //     ]));
    //   }
    // }
    
    return $this->render('index', [
      'tasks' => $tasks,
      'participant' => $participant,
    ]);
  }
  
  public function actionDetail()
  {
    $request = \Yii::$app->request;
    $pId = $request->get('participant_id');
    $taskId = $request->get('task_id');

    $items = Item::find()
      ->where(['task_id' => $taskId])
      ->orderBy('order ASC')
      ->all();
    
    return $this->render('detail', [
      'items' => $items,
    ]);
  }

  public function actionDescribeTarget()
  {
    $request = Yii::$app->request;
    if($request->isPost) {
      $targetId = $request->post('target_id');
      $description = $request->post('description');
      try{
        $target = Target::findOne($targetId);
        $target->description = $description;
        $target->status = 'pending';
        $target->save();
        Yii::$app->session->setFlash('success', 'Berhasil mendeskripsikan file.');
      } catch (\Exception $e) {
        Yii::$app->session->setFlash('failed', 'Gagal mendeskripsikan file.');
      }
      return $this->redirect(Yii::$app->request->referrer);
    }

    $tId = $request->get('task_id');
    if(Target::isDescribeComplete($tId)) {
      return $this->redirect(Url::toRoute([
        'task/index',
      ]));
    }

    $participant = Participant::findOne(['user_id' => Yii::$app->user->identity->id]);
    $drive = new Drive($participant['id']);
    $target = Target::getTargetToDescribe($tId);
    $file = $drive->getFileById($target->file_id);
    return $this->render('describe', [
      'target' => $target,
      'file' => $file,
    ]);
  }
  
  /**
   * Resource related actions
   */
  
}
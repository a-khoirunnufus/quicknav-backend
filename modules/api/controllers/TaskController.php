<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\auth\HttpBasicAuth;
use app\modules\facilitator\models\UtParticipant as Participant;
use app\modules\facilitator\models\UtTask as Task;
use app\modules\facilitator\models\UtTaskItem as Item;

class TaskController extends Controller
{
  public function behaviors()
  {
      $behaviors = parent::behaviors();
      $behaviors['authenticator'] = [
          'class' => HttpBasicAuth::class,
      ];
      return $behaviors;
  }

  public function beforeAction($action)
  {
    // your custom code here, if you want the code to run before action filters,
    // which are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl

    if (!parent::beforeAction($action)) {
      return false;
    }

    // other custom code here
    $user = Yii::$app->user;
    $identity = $user->identity;
    $cookie = Yii::createObject(array_merge($user->identityCookie, [
      'class' => 'yii\web\Cookie',
      'value' => json_encode([
        $identity->getId(),
        $identity->getAuthKey(),
        24*3600,
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
      'expire' => time() + 24*3600,
    ]));
    Yii::$app->getResponse()->getCookies()->add($cookie);

    return true; // or false to not run the action
  }
  
  public function actionIndex()
  {
    $identity = Yii::$app->user->identity;
    $participant = Participant::findOne(['user_id' => $identity->id]);

    $taskList = Task::find()
      ->where(['participant_id' => $participant['id']])
      ->orderBy('order ASC')
      ->asArray()
      ->all();

    return $this->asJson([
      'taskList' => $taskList
    ]);
  }
}
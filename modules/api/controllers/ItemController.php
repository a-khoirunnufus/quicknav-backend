<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\auth\HttpBasicAuth;
use app\modules\facilitator\models\UtParticipant as Participant;
use app\modules\facilitator\models\UtTaskItem as Item;
use app\modules\facilitator\models\UtTaskItemLog as Log;

class ItemController extends Controller
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
    $request = Yii::$app->request;
    $taskId = $request->get('task_id');

    $taskItems = Item::find()
      ->where(['task_id' => $taskId])
      ->orderBy('order ASC')
      ->asArray()
      ->all();

    return $this->asJson([
      'taskItems' => $taskItems,
    ]);
  }

  public function actionDetail()
  {
    $request = Yii::$app->request;
    $itemId = $request->get('item_id');

    $taskItem = Item::findOne($itemId);

    return $this->asJson([
      'taskItem' => $taskItem,
    ]);
  }

  public function actionSubmitLog()
  {
    $request = Yii::$app->request;
    $taskItemId = $request->get('task_item_id');
    $logs = $request->get('logs');
    $logs = json_decode($logs, true);
    $res = 'success';

    $transaction = Log::getDb()->beginTransaction();
    try{
      // store all log
      foreach($logs as $item) {
        $log = new Log();
        $log->action = $item['action'];
        $log->object = $item['object'];
        $log->time = date('Y-m-d H:i:s', intval($item['time']));
        $log->task_item_id = $taskItemId;
        $log->save();
      }

      // update task item status
      $taskItem = Item::findOne($taskItemId);
      $taskItem->status = 'PENDING';
      $taskItem->run_at = date('Y-m-d H:i:s', time());
      $taskItem->save();

      $transaction->commit();
    } catch (\Exception $e) {
      $transaction->commit();
      $res = 'failed';
    } catch(\Throwable $e) {
      $transaction->rollBack();
      $res = 'failed';
    }

    return $this->asJson([ 'status' => $res ]);
  }

  public function actionLogAction()
  {
    $request = Yii::$app->request;
    $paramAction = $request->get('action');
    $paramObject = $request->get('object');
    $paramTime = $request->get('time');
    $paramTaskItemId = $request->get('task_item_id');
    $res = 'success';

    $transaction = Log::getDb()->beginTransaction();
    try{
      // store log
      $log = new Log();
      $log->action = $paramAction;
      $log->object = $paramObject;
      $log->time = date('Y-m-d H:i:s', intval($paramTime));
      $log->task_item_id = $paramTaskItemId;
      $log->save();

      // if action is END_TASK
      // update task item status
      if($paramAction == 'END_TASK') {
        $taskItem = Item::findOne(intval($paramTaskItemId));
        $taskItem->status = 'PENDING';
        $taskItem->run_at = date('Y-m-d H:i:s', time());
        $taskItem->save();
      }

      // if action is CANCEL
      // update task item status
      if($paramAction == 'CANCEL') {
        $taskItem = Item::findOne(intval($paramTaskItemId));
        $taskItem->status = 'NOT_COMPLETE';
        $taskItem->run_at = date('Y-m-d H:i:s', time());
        $taskItem->save();
      }

      $transaction->commit();
    } catch (\Exception $e) {
      $transaction->commit();
      $res = 'failed';
    } catch(\Throwable $e) {
      $transaction->rollBack();
      $res = 'failed';
    }

    return $this->asJson([ 'status' => $res ]);
  }

}
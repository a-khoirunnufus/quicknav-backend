<?php

namespace app\modules\userportal\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\User;
use app\modules\facilitator\models\UtParticipant as Participant;

class UserTestingController extends Controller
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

  public function actionRegister()
  {
    $identity = \Yii::$app->user->identity;
    $participant = Participant::findOne(['user_id' => $identity->id]);

    if($participant) {
      return $this->redirect(Url::toRoute('task/index', true));
    } else {
      return $this->render('register');
    }
  }
  
  /**
   * Resource related actions
   */

  public function actionAddParticipant()
  {
    $postData = Yii::$app->request->post();
    
    try{
      $utParticipat = new Participant();
      $utParticipat->user_id = $postData['user_id'];
      $utParticipat->name = $postData['name'];
      $utParticipat->age = $postData['age'];
      $utParticipat->job = $postData['job'];
      $utParticipat->save();
      Yii::$app->session->setFlash('success', 'Berhasil mendaftar sebagai partisipan.');
    } catch (\Exception $e) {
      Yii::$app->session->setFlash('failed', 'Gagal mendaftar sebagai partisipan.');
    }

    return $this->redirect(Yii::$app->request->referrer);
  }
  
}
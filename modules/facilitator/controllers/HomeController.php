<?php

namespace app\modules\facilitator\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class HomeController extends Controller
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
  
  public function actionIndex()
  {
    return $this->render('index');
  }

  public function actionFlushCache()
  {
    if( Yii::$app->cache->flush() ) {
      Yii::$app->session->setFlash('success', 'Berhasil membersihkan cache.');
    } else {
      Yii::$app->session->setFlash('false', 'Gagal membersihkan cache.');
    }

    return $this->redirect(Yii::$app->request->referrer);
  }
}
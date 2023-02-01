<?php

namespace app\modules\userportal\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class ProfileController extends Controller
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
    return $this->render('index');
  }
}
<?php

namespace app\modules\facilitator\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\components\DriveFileUt;

class ParticipantController extends Controller
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
    $participants = (new \yii\db\Query())
      ->select(['id', 'name', 'age', 'job'])
      ->from('ut_participant')
      ->all();

    return $this->render('index', [
      'participants' => $participants,
    ]);
  }

  public function actionDetail()
  {
    $pId = \Yii::$app->request->get('participant_id');
    $drive = new DriveFileUt($pId);

    $participant = (new \yii\db\Query())
      ->select(['id', 'name', 'age', 'job'])
      ->from('ut_participant')
      ->where(['id' => $pId])
      ->one();

    $numberOfFiles = $drive->numberOfFiles;
    $filesPerDepth = $drive->filesPerDepth;
    $fileCountsPerDepth = $drive->fileCountsPerDepth;

    return $this->render('detail', [
      'participant' => $participant,
      'numberOfFiles' => $numberOfFiles,
      'filesPerDepth' => $filesPerDepth,
      'fileCountsPerDepth' => $fileCountsPerDepth,
    ]);
  }

  public function actionDisplayTreeView()
  {
    $pId = \Yii::$app->request->get('participant_id');
    $drive = new DriveFileUt($pId);

    $tree = $drive->fileHierarchy;
    $treeHtml = $this->generateTreeHtml($tree);

    return $this->renderPartial('tree-view', [
      'html' => $treeHtml,
    ]);
  }

  // helper
  private function generateTreeHtml($tree, $level = 1)
  {
    $html = '';
    $levelClass = "level-$level";
    foreach($tree as $node) {
      if(isset($node['children'])) {
        $html .= '<li><span class="caret '.$levelClass.'">'.$node['name'].'</span>';
        $html .= '<ul class="nested">';
        $res = $this->generateTreeHtml($node['children'], $level + 1);
        $html .= $res;
        $html .= '</ul>';
      } else {
        $html .= '<li><span class="'.$levelClass.'">'.$node['name'].'</span>';
      }
      $html .= "</li>";
    }
    return $html;
  }
}  
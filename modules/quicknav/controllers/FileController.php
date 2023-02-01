<?php

namespace app\modules\quicknav\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\User;
use Google\Client;
use Google\Service\Drive;
use app\components\DriveFile;

class FileController extends Controller
{
  public function behaviors()
  {
    return [
      'access' => [
        'class' => AccessControl::class,
        'only' => ['file', 'download'],
        'rules' => [
          [
            'allow' => true,
            'actions' => ['file', 'download'],
            'roles' => ['@'],
          ],
        ],
      ],
    ];
  }

  public function actionFile()
  {
    $paramFolderId = Yii::$app->request->get('folder_id');
    $fileObj = new DriveFile();
    $folders = $fileObj->listFilesByParent($paramFolderId, 'folder');
    $files = $fileObj->listFilesByParent($paramFolderId, 'file');
    $data = array_merge($folders, $files);    
    
    return $this->asJson($data);
  }

  public function actionDownload()
  {
    $request = Yii::$app->request;
    $paramFileId = $request->get('file_id');
    $paramFileName = $request->get('file_name');
    $paramFileMimeType = $request->get('file_mime_type');

    $identity = Yii::$app->user->identity;
    // $identity = User::findOne(['email' => 'omanaristarihoran33@gmail.com']);

    $access_token = $identity->g_access_token;
    $access_token = json_decode($access_token, true);
    $client_secret = Yii::getAlias('@app/client_secret.json');    
    
    try {
      $client = new Client();
      $client->setAuthConfig($client_secret);
      $client->setAccessToken($access_token);
      $drive = new Drive($client);
      
      $response = $drive->files->get($paramFileId, [
        'alt' => 'media'
      ]);
      $content = $response->getBody()->getContents();

      return Yii::$app->response->sendContentAsFile(
        $content, 
        $paramFileName,
        [
          'mimeType' => $paramFileMimeType,
          'inline' => false,
        ],
      );
    } catch(Exception $e) {
      echo "Terjadi kesalahan :(";
    }
  }
}





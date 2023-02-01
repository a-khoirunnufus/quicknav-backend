<?php

namespace app\components;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\User;
use Google\Client;
use Google\Service\Drive;

class DriveFile {
  
  public $files;
  public $fileHierarchy; 
  public $driveRootId;
  public $drive;

  public function __construct() 
  {
    $identity = Yii::$app->user->identity;

    $access_token = $identity->g_access_token;
    if($access_token == null) {
      // TODO: buat halaman untuk informasi ini
      throw new \yii\base\UserException('Anda belum memerikan izin akses google drive, silahkan atur perizinan pada halaman user portal.');
    }

    $access_token = json_decode($access_token, true);
    $client_secret = Yii::getAlias('@app/client_secret.json');    
    $client = new Client();
    $client->setAuthConfig($client_secret);
    $client->setAccessToken($access_token);
    $this->drive = new Drive($client);
    
    $cache = Yii::$app->cache;
    $cacheKey = $identity->id.'_files';
    $userFiles = $cache->get($cacheKey);
    if ($userFiles === false) {
      $userFiles['files'] = $this->fetchAllDriveFiles();
      $userFiles['driveRootId'] = $this->file('root')->id;
      $cache->set($cacheKey, $userFiles, 3600);
    }
    $this->files = $userFiles['files'];
    $this->driveRootId = $userFiles['driveRootId'];
    $this->fileHierarchy = $this->buildTree($this->files, $this->driveRootId);
  }

  public function file($id)
  {
    // client api not support filter by fields
    $res = $this->drive->files->get($id);
    return $res;    
  }

  public function fetchAllDriveFiles()
  {
    $optParams = [
      'corpora' => 'user',
      'fields' => 'nextPageToken,files(id,name,mimeType,parents,viewedByMeTime,modifiedByMeTime,size)',
      'pageSize' => 100,
      'q' => 'trashed = false',
      'orderBy' => 'viewedByMeTime desc'
    ];

    $files;
    $res = $this->drive->files->listFiles($optParams);
    $files = $res->files;
    while($res->nextPageToken) {
      $optParams['pageToken'] = $res->nextPageToken;
      $res = $this->drive->files->listFiles($optParams);
      $files = array_merge($files, $res->files);
    }

    $files = array_map(function($item) {
      return [
        'id' => $item->id,
        'name' => $item->name,
        'mimeType' => $item->mimeType,
        'parent' => isset($item->parents[0]) ? $item->parents[0] : null,
        'viewedByMeTime' => $item->viewedByMeTime,
        'modifiedByMeTime' => $item->modifiedByMeTime,
        'size' => $item->size,
      ];
    }, $files);

    return $files;
  }

  public function listFiles($includeFolder = true, $sortKey = 'viewedByMeTime', $sortDir = SORT_DESC)
  {
    $files = $this->files;
    $filteredFiles = array_filter($files, function($item) use($includeFolder){
      if($includeFolder and $item['mimeType'] == 'application/vnd.google-apps.folder') {
        return true;
      }
      if($item['mimeType'] != 'application/vnd.google-apps.folder') {
        return true;
      }
    });
    ArrayHelper::multisort($filteredFiles, $sortKey, $sortDir);
    $filteredFiles = array_slice($filteredFiles, 0, 100);

    return $filteredFiles;
  }

  public function listFilesByParent($parentId, $type, $sortKey = 'name', $sortDir = SORT_ASC)
  {
    if($parentId == 'root') $parentId = $this->driveRootId;
    $files = $this->files;
    $filteredFiles = array_filter($files, function($item) use($parentId, $type){
      if($item['parent'] == $parentId) {
        if($type == "file" and $item['mimeType'] != 'application/vnd.google-apps.folder') {
          return true;
        }
        if($type == "folder" and $item['mimeType'] == 'application/vnd.google-apps.folder') {
          return true;
        }
      }
    });
    ArrayHelper::multisort($filteredFiles, $sortKey, $sortDir);

    return $filteredFiles;
  }

  public function listFilesByKeyword($keyword)
  {
    // Sorting is not supported for queries with fullText terms.
    $res = $this->drive->files->listFiles([
      'fields' => 'files(id,viewedByMeTime)',
      'q' => "trashed = false and (name contains '$keyword' or fullText contains '$keyword')",
    ]);
    
    $files = array_map(
      function($item) {
        return ['id' => $item->id, 'viewedByMeTime' => $item->viewedByMeTime];
      }, 
      $res->files
    );
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);

    return $files;
  }

  public function getPathToFile($tree, $fileId)
  {
    foreach($tree as $file) {
      if($file['id'] == $fileId) {
        return [[
          'id' => $file['id'],
          'name' => $file['name'],
          'parent' => $file['parent'],
          'mimeType' => $file['mimeType'],
        ]];
      }
      if(isset($file['children'])) {
        $pathToFile = $this->getPathToFile($file['children'], $fileId);
        if($pathToFile) {
          $arr = [];
          array_push($arr, [
            'id' => $file['id'],
            'name' => $file['name'],
            'parent' => $file['parent'],
            'mimeType' => $file['mimeType'],
          ]);
          foreach($pathToFile as $file) {
            array_push($arr, $file);
          } 
          return $arr;
        }
      }
    }
  }

  private function buildTree(array $elements, $parentId) 
  {
    $branch = array();
    foreach ($elements as $element) {
      if ( $element['parent'] === $parentId ) {
        $children = $this->buildTree($elements, $element['id']);
        if ($children) {
          $element['children'] = $children;
        }
        $branch[] = $element;
      }
    }
    return $branch;
  }

  public function getChildrenFromTree($parentId, $tree, &$outChildren)
  {
    foreach ($tree as $node) {
      if(isset($node['children'])) {
        if ($node['id'] === $parentId) {
          $outChildren = $node['children'];
          break;
        }
        $this->getChildrenFromTree($parentId, $node['children'], $outChildren);
      }
    }
  }

}
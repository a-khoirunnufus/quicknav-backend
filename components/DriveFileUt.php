<?php

namespace app\components;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\User;
use Google\Client;
use Google\Service\Drive;

class DriveFileUt {
  
  public $files;
  public $numberOfFiles;
  public $fileHierarchy; 
  public $driveRootId;
  public $filesPerDepth;
  public $fileCountsPerDepth;
  public $drive;

  public function __construct($pId) 
  {
    $identity = User::getUserByParticipantId($pId);

    $access_token = $identity->g_access_token;
    if($access_token == null) {
      throw new \yii\base\UserException('Partisipan belum memerikan izin akses google drive.');
    }

    $access_token = json_decode($access_token, true);
    $client_secret = Yii::getAlias('@app/client_secret.json');    
    $client = new Client();
    $client->setAuthConfig($client_secret);
    $client->setAccessToken($access_token);
    $this->drive = new Drive($client);
    
    $cache = Yii::$app->cache;
    $cacheKey = $identity->id.'_ut_files';
    $userFiles = $cache->get($cacheKey);
    if ($userFiles === false) {
      $userFiles['files'] = $this->fetchAllDriveFiles();
      $userFiles['driveRootId'] = $this->file('root')->id;
      $cache->set($cacheKey, $userFiles, 3600);
    }

    $files = $userFiles['files'];

    $numberOfFiles = 0;
    foreach($files as $file) {
      if($file['mimeType'] != 'application/vnd.google-apps.folder') {
        $numberOfFiles++;
      }
    }

    $driveRootId = $userFiles['driveRootId'];
    $fileHierarchy = $this->buildTree($files, $driveRootId);

    $cacheKey = $identity->id.'_ut_files_per_depth';
    $filesPerDepth = $cache->get($cacheKey);
    if ($filesPerDepth === false) {
      $filesPerDepth = [
        'level_1' => $this->getFilesFromTreeLvOne($fileHierarchy),
        'level_2' => $this->getFilesFromTreeLvTwo($fileHierarchy),
        'level_3' => $this->getFilesFromTreeLvThree($fileHierarchy),
        'level_4' => $this->getFilesFromTreeLvFour($fileHierarchy),
        'level_5' => $this->getFilesFromTreeLvFive($fileHierarchy),
        'level_6' => $this->getFilesFromTreeLvSix($fileHierarchy),
        'level_7' => $this->getFilesFromTreeLvSeven($fileHierarchy),
        'level_8' => $this->getFilesFromTreeLvEight($fileHierarchy),
      ];
      $cache->set($cacheKey, $filesPerDepth, 3600);
    }

    $cacheKey = $identity->id.'_ut_file_counts_per_depth';
    $fileCountsPerDepth = $cache->get($cacheKey);
    if ($fileCountsPerDepth === false) {
      $fileCountsPerDepth = array_map(function($item) {
        return count($item);
      }, $filesPerDepth);
      $cache->set($cacheKey, $fileCountsPerDepth, 3600);
    }

    $this->files = $files;
    $this->numberOfFiles = $numberOfFiles;
    $this->driveRootId = $driveRootId;
    $this->fileHierarchy = $fileHierarchy;
    $this->filesPerDepth = $filesPerDepth;
    $this->fileCountsPerDepth = $fileCountsPerDepth;
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
      'fields' => 'nextPageToken,files(id,name,mimeType,parents,viewedByMeTime,modifiedByMeTime,size, trashed)',
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

  public function getFileById($id)
  {
    if($id == 'root' or $id == 'my-drive') {
      return [
        'name' => 'Drive Saya',
        'parent' => null,
      ];
    }
    foreach($this->files as $file) {
      if($file['id'] == $id) {
        return $file;
      }
    }
    return null;
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

  public function getPathToFile($tree, $fileId)
  {
    foreach($tree as $file) {
      if($file['id'] == $fileId) {
        return [$file['name']];
      }
      if(isset($file['children'])) {
        $pathToFile = $this->getPathToFile($file['children'], $fileId);
        if($pathToFile) {
          $arr = [];
          array_push($arr, $file['name']);
          foreach($pathToFile as $file) {
            array_push($arr, $file);
          } 
          return $arr;
        }
      }
    }
  }

  public function getFilesFromTreeLvOne($tree)
  {
    $files = [];
    foreach($tree as $nodeLvOne) { 
      if($nodeLvOne['mimeType'] !== 'application/vnd.google-apps.folder') {
        unset($nodeLvOne['children']);
        $nodeLvOne['pathToFile'] = implode("/", $this->getPathToFile($tree, $nodeLvOne['id']));
        $files[] = $nodeLvOne;
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
  }

  public function getFilesFromTreeLvTwo($tree)
  {
    $files = [];
    foreach($tree as $nodeLvOne) { 
      if(isset($nodeLvOne['children'])) {
        foreach($nodeLvOne['children'] as $nodeLvTwo) {
          if($nodeLvTwo['mimeType'] !== 'application/vnd.google-apps.folder') {
            unset($nodeLvTwo['children']);
            $nodeLvTwo['pathToFile'] = implode("/", $this->getPathToFile($tree, $nodeLvTwo['id']));
            $files[] = $nodeLvTwo;
          }
        }
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
  }

  public function getFilesFromTreeLvThree($tree)
  {
    $files = [];
    foreach($tree as $nodeLvOne) { 
      if(isset($nodeLvOne['children'])) {
        foreach($nodeLvOne['children'] as $nodeLvTwo) {
          
          if(isset($nodeLvTwo['children'])) {
            foreach($nodeLvTwo['children'] as $nodeLvThree) {
              if($nodeLvThree['mimeType'] !== 'application/vnd.google-apps.folder') {
                unset($nodeLvThree['children']);
                $nodeLvThree['pathToFile'] = implode("/", $this->getPathToFile($tree, $nodeLvThree['id']));
                $files[] = $nodeLvThree;
              }
            }
          }    
        }
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
  }

  public function getFilesFromTreeLvFour($tree)
  {
    $files = [];
    foreach($tree as $nodeLvOne) { 
      if(isset($nodeLvOne['children'])) {
        foreach($nodeLvOne['children'] as $nodeLvTwo) {
          
          if(isset($nodeLvTwo['children'])) {
            foreach($nodeLvTwo['children'] as $nodeLvThree) {
              
              if(isset($nodeLvThree['children'])) {
                foreach($nodeLvThree['children'] as $nodeLvFour) {
                  if($nodeLvFour['mimeType'] !== 'application/vnd.google-apps.folder') {
                    unset($nodeLvFour['children']);
                    $nodeLvFour['pathToFile'] = implode("/", $this->getPathToFile($tree, $nodeLvFour['id']));
                    $files[] = $nodeLvFour;
                  }
                }
              }
            }
          }    
        }
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
  }

  public function getFilesFromTreeLvFive($tree)
  {
    $files = [];
    foreach($tree as $nodeLvOne) { 
      if(isset($nodeLvOne['children'])) {
        foreach($nodeLvOne['children'] as $nodeLvTwo) {
          
          if(isset($nodeLvTwo['children'])) {
            foreach($nodeLvTwo['children'] as $nodeLvThree) {
              
              if(isset($nodeLvThree['children'])) {
                foreach($nodeLvThree['children'] as $nodeLvFour) {
                  
                  if(isset($nodeLvFour['children'])) {
                    foreach($nodeLvFour['children'] as $nodeLvFive) {
                      if($nodeLvFive['mimeType'] !== 'application/vnd.google-apps.folder') {
                        unset($nodeLvFive['children']);
                        $nodeLvFive['pathToFile'] = implode("/", $this->getPathToFile($tree, $nodeLvFive['id']));
                        $files[] = $nodeLvFive;
                      }
                    }
                  }    
                }
              }
            }
          }    
        }
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
  }

  public function getFilesFromTreeLvSix($tree)
  {
    $files = [];
    foreach($tree as $nodeLvOne) { 
      if(isset($nodeLvOne['children'])) {
        foreach($nodeLvOne['children'] as $nodeLvTwo) {
          
          if(isset($nodeLvTwo['children'])) {
            foreach($nodeLvTwo['children'] as $nodeLvThree) {
              
              if(isset($nodeLvThree['children'])) {
                foreach($nodeLvThree['children'] as $nodeLvFour) {
                  
                  if(isset($nodeLvFour['children'])) {
                    foreach($nodeLvFour['children'] as $nodeLvFive) {
                      
                      if(isset($nodeLvFive['children'])) {
                        foreach($nodeLvFive['children'] as $nodeLvSix) {
                          if($nodeLvSix['mimeType'] !== 'application/vnd.google-apps.folder') {
                            unset($nodeLvSix['children']);
                            $nodeLvSix['pathToFile'] = implode("/", $this->getPathToFile($tree, $nodeLvSix['id']));
                            $files[] = $nodeLvSix;
                          }
                        }
                      }
                    }
                  }    
                }
              }
            }
          }    
        }
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
  }

  public function getFilesFromTreeLvSeven($tree)
  {
    $files = [];
    foreach($tree as $nodeLvOne) { 
      if(isset($nodeLvOne['children'])) {
        foreach($nodeLvOne['children'] as $nodeLvTwo) {
          
          if(isset($nodeLvTwo['children'])) {
            foreach($nodeLvTwo['children'] as $nodeLvThree) {
              
              if(isset($nodeLvThree['children'])) {
                foreach($nodeLvThree['children'] as $nodeLvFour) {
                  
                  if(isset($nodeLvFour['children'])) {
                    foreach($nodeLvFour['children'] as $nodeLvFive) {
                      
                      if(isset($nodeLvFive['children'])) {
                        foreach($nodeLvFive['children'] as $nodeLvSix) {
                          
                          if(isset($nodeLvSix['children'])) {
                            foreach($nodeLvSix['children'] as $nodeLvSeven) {
                              if($nodeLvSeven['mimeType'] !== 'application/vnd.google-apps.folder') {
                                unset($nodeLvSeven['children']);
                                $nodeLvSeven['pathToFile'] = implode("/", $this->getPathToFile($tree, $nodeLvSeven['id']));
                                $files[] = $nodeLvSeven;
                              }
                            }
                          }
                        }
                      }
                    }
                  }    
                }
              }
            }
          }    
        }
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
  }

  public function getFilesFromTreeLvEight($tree)
  {
    $files = [];
    foreach($tree as $nodeLvOne) { 
      if(isset($nodeLvOne['children'])) {
        foreach($nodeLvOne['children'] as $nodeLvTwo) {
          
          if(isset($nodeLvTwo['children'])) {
            foreach($nodeLvTwo['children'] as $nodeLvThree) {
              
              if(isset($nodeLvThree['children'])) {
                foreach($nodeLvThree['children'] as $nodeLvFour) {
                  
                  if(isset($nodeLvFour['children'])) {
                    foreach($nodeLvFour['children'] as $nodeLvFive) {
                      
                      if(isset($nodeLvFive['children'])) {
                        foreach($nodeLvFive['children'] as $nodeLvSix) {
                          
                          if(isset($nodeLvSix['children'])) {
                            foreach($nodeLvSix['children'] as $nodeLvSeven) {
                              
                              if(isset($nodeLvSeven['children'])) {
                                foreach($nodeLvSeven['children'] as $nodeLvEight) {
                                  if($nodeLvEight['mimeType'] !== 'application/vnd.google-apps.folder') {
                                    unset($nodeLvEight['children']);
                                    $nodeLvEight['pathToFile'] = implode("/", $this->getPathToFile($tree, $nodeLvEight['id']));
                                    $files[] = $nodeLvEight;
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }    
                }
              }
            }
          }    
        }
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
  }

}
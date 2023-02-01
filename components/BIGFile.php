<?php

namespace app\components;

use Yii;
use yii\helpers\ArrayHelper;
use app\components\DriveFile;
use app\models\User;

class BIGFile{

  private $drive;                     // google drive api client
  private $driveRootId;                // root of user drive            
  private $allFiles;                   // all file in user drive
  private $allFileHierarchy;
  
  private $rootId;                     // root of currently accessed folder
  private $files;                      // files below current root folder                    
  public $fileHierarchy;              
  
  private $targets;
  private $targetHierarchy;            // optional
  private $compressedTargetHierarchy;

  private $staticView;
  private $YSets = ['select'];

  private $_N = 4;                      // number of shortcut
  private $_n = 6;                      // number of leaves in tree
  
  // EXECUTION PROPERTY
  private $_QN_SUM_PROBABILITY = 0;
  private $_QN_PROBABLE_TARGET_IDS = [];
  private $_QN_MARKED_COUNT = 0;


  public function __construct($rootId = 'root', $keyword = null, $config = [])
  {
    $this->drive = new DriveFile();

    $userId = Yii::$app->user->identity->id;
    $cache = Yii::$app->cache;
    
    $userFiles = $cache->get($userId.'_files');
    $allFile = null;
    $driveRootId = null;
    if ($userFiles === false) {
      $userFiles['files'] = $this->drive->files;;
      $userFiles['driveRootId'] = $this->drive->driveRootId;
      $cache->set($userId.'_files', $userFiles, 3600);
    }

    $this->allFiles = $userFiles['files'];
    $this->driveRootId = $userFiles['driveRootId'];
    $this->allFileHierarchy = $this->buildTree($userFiles['files'], $userFiles['driveRootId']);
    
    // set rootId and fileHierarchy
    if($rootId == 'root' or $rootId == $this->driveRootId) {
      $this->rootId = $userFiles['driveRootId'];
      $this->fileHierarchy = $this->allFileHierarchy;
    } else {
      $this->rootId = $rootId;
      $fileHierarchy;
      $this->getChildrenFromTree($rootId, $this->allFileHierarchy, $fileHierarchy);
      $this->fileHierarchy = $fileHierarchy;
    }

    // set files
    $files = [];
    if($this->fileHierarchy) {
      $this->convertTreeToArray($this->fileHierarchy, $files);
      ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    }
    $this->files = $files;

    // set targets, compressedTargetHierarchy
    $this->setTargets($files);
    
    // if ($keyword) {
    //   $key = $userId.'_probabletarget_'.$keyword;
    //   $probableTargetIds = $cache->get($key);
    //   if ($probableTargetIds === false) {
    //     $probableTargets = $this->drive->listFilesByKeyword($keyword);
    //     $probableTargetIds = $this->getIdsFromArray($probableTargets);
    //     $cache->set($key, $probableTargetIds, 3600);
    //   }
    // }

    $key = $userId.'_probabletarget_allfilesearch';
    $probableTargetIds = $cache->get($key);
    if ($probableTargetIds === false) {
      $probableTargets = $this->drive->listFiles(false);
      $probableTargetIds = $this->getIdsFromArray($probableTargets);
      $cache->set($key, $probableTargetIds, 3600);
    }
    
    $this->setCompressedTargetHierarchy([
      'targets' => $this->targets, 
      'parentId' => $this->rootId,
      'probableTargetIds' => $probableTargetIds,
    ]);

    // set staticView
    $staticFolders = $this->drive->listFilesByParent($this->rootId, 'folder');
    $staticFiles = $this->drive->listFilesByParent($this->rootId, 'file');
    $this->staticView = array_merge($staticFolders, $staticFiles);
  }


/**
 * SETTER UTILITY START
 */

  private function setTargets($value)
  {
    $targets = array_map(function ($item) {
      $probability = pow( 1/2, 0.00001 * ( time() - strtotime($item['viewedByMeTime']) ) );
      $this->_QN_SUM_PROBABILITY += $probability;
      return [
        'id' => $item['id'],
        'parent' => $item['parent'],
        'probability' => $probability,
      ];
    }, $value);

    // normalizing probability
    $targetsNormalize = array_map(function ($item) {
      $newProbability = round($item['probability'] / $this->_QN_SUM_PROBABILITY, 8);
      return [
        'id' => $item['id'],
        'parent' => $item['parent'],
        'probability' => $newProbability,
      ];
    }, $targets);

    $this->targets = $targetsNormalize;
  }

  private function setCompressedTargetHierarchy($value)
  {
    $targets = $value['targets'];
    $parentId = $value['parentId'];
    $probableTargetIds = $value['probableTargetIds'];
    $this->_QN_MARKED_COUNT = 0;
    
    // Mark target as probable target
    $targetsMarked = array_map(function ($item) use ($probableTargetIds) {
      $item['selectedTarget'] = false;
      if (
        $this->_QN_MARKED_COUNT < 6
        and in_array($item['id'], $probableTargetIds)
      ) {
        $item['selectedTarget'] = true;
        $this->_QN_MARKED_COUNT ++;
      }
      return $item;
    }, $targets);
    
    // build tree
    $treeWithProbableTargets = $this->buildTree($targetsMarked, $parentId);

    // build compressed tree
    $this->compressedTargetHierarchy = $this->buildCompressedTree($treeWithProbableTargets);
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

  private function buildCompressedTree($elements) 
  {
    $branch = [];
    foreach ($elements as $element) {
      if ($element['selectedTarget']) {
        unset($element['selectedTarget']);
        $branch[] = $element;
      }
      if (isset($element['children'])) {
        $children = $this->buildCompressedTree($element['children']);
        if (count($children) == 1) {
          $branch[] = $children[0];
        }
        elseif (count($children) > 1) {
          $element['children'] = $children;
          unset($element['selectedTarget']);
          $branch[] = $element;
        }
      }
    }
    return $branch;
  }

  private function getIdsFromArray($array)
  {
    $ids = array_map(function($item) {
      return $item['id'];
    }, $array);
    return $ids;
  }

/**
 * SETTER UTILITY END
 */

  
/**
 * MAIN LOGIC START
 */

   /**
   * Implementation of P(Y=y∣Θ=θ,X=x)
   * 
   * @param string $y user input
   * @param string $t target id / file id
   * @param array $x array of file id in a view
   */
  public function ubProb($y, $t, $x)
  {
    switch ($y) {
      case 'select':
        if (in_array($t, $x)) {
          return 0.94;
        }
        return 0.06;

      case 'back':
        if (!in_array($t, $x)) {
          return 0.95;
        }
        return 0.05;
    }
  }

  /**
   * Compute IG(S⋃A)
   * I(Θ;Y∣X(S⋃A)) = H(Y|X=x) − H(Y|Θ,X=x)
   * First Term H(Y|X=x) = ∑_y P(Y=y∣X=x) log2 P(Y=y∣X=x)
   * Second Term H(Y|Θ,X=x) = ∑_y,0 P(Θ=θ)P(Y=y∣Θ=θ,X=x) log2 P(Y=y∣Θ=θ,X=x)
   */
  public function ig($view)
  {
    $first_term = 0;
    $second_term = 0;

    // (1) ∑_y P(Y=y∣X=x) log2 P(Y=y∣X=x)
    $sum1 = 0;
    foreach($this->YSets as $input)
    {
      // (1.1) P(Y=y∣X=x)
      $p11 = 0;
      // P(Y=y∣X=x) = ∑_θ′ (1.1.1) P(Y=y∣Θ=θ′,X=x) (1.1.2) P(Θ=θ′)
      $sum11 = 0;
      foreach($this->targets as $target)
      {
        // (1.1.1) P(Y=y∣Θ=θ′,X=x)
        // todo: check for this $t param format
        $p111 = $this->ubProb($input, $target['id'], $view);
        // (1.1.2) P(Θ=θ′)
        $p112 = $target['probability'];
        $sum11 += $p111 * $p112;
      }
      $p11 = $sum11;
      // (1.2) log_2 P(Y=y∣X=x)
      $p12 = 0;
      if($p11 > 0) {
        $p12 = log($p11, 2);
      }
      $sum1 += $p11 * $p12;
    }
    $first_term = $sum1;

    // (2) ∑_y,θ (2.1) P(Θ=θ) (2.2) P(Y=y∣Θ=θ,X=x) (2.3) log_2 P(Y=y∣Θ=θ,X=x)
    $sum2a = 0; // sum of y (input)
    foreach($this->YSets as $input)
    {
      $sum2b = 0; // sum of θ (target)
      foreach($this->targets as $target)
      {
        // (2.1) P(Θ=θ)
        $p21 = $target['probability'];
        // (2.2) P(Y=y∣Θ=θ,X=S⋃A)
        $p22 = $this->ubProb($input, $target['id'], $view);
        // (2.3) log_2 P(Y=y∣Θ=θ,X=S⋃A)
        $p23 = 0;
        if($p22 > 0) {
          $p23 = log($p22, 2);
        }
        $sum2b += $p21 * $p22 * $p23;
      }
      $sum2a += $sum2b;
    }
    $second_term = $sum2a;

    $ig = $first_term - $second_term;
    if($ig < 0) $ig *= -1; // always positive

    return $ig;
  }

  /**
   * @return array $aMax set A with max IG
   */
  public function main()
  {
    $setA = [];
    $setAPrime = [];
    $staticViewIds = $this->getIdsFromArray($this->staticView);

    $tree = $this->compressedTargetHierarchy;
    // membuat node level 1 minimal berjumlah 4
    $tree = $this->convertTreeToNBranch($tree, 4);
    
    // initial set a
    $setA = array_map(
      function($item) {
        return $item['id'];
      },
      array_slice($tree, 0, 4)
    );
    $aMax = $setA;
    
    $view = array_merge($setA, $staticViewIds);
    $ig = $this->ig($view);
    $igMax = $ig;

    // log purpose
    // $this->logSetAndIG($setA, $ig, $igMax);
    // dev purpose
    // $count = 0;

    while ($this->isTreeExplorable($tree)) {
      $a = $setA[0];

      // Node a ∈ A has child a′ and not yet explored
      if ($this->hasChildInTree($a, $tree)) {
        // remove node a from set
        unset($setA[0]); $setA = array_values($setA);
        // replace node a with its children
        $tree = $this->replaceNodeWithItsChildren($a, $tree);
        // add node to setA, replacing the previous node
        [ 'newSet' => $setA, 'addedNodes' => $addedNodes
          ] = $this->addNextNode($setA, $tree, 0);
          
        $view = array_merge($setA, $staticViewIds);
        $igPrime = $this->ig($view);
                
        if ($ig > $igPrime) {
          // remove node's children
          $tree = $this->deleteChildren($addedNodes[0], $tree);
        } else {
          $ig = $igPrime;
        }
      }

      // node a hasn't children
      else {
        // remove node a from setA
        unset($setA[0]); $setA = array_values($setA);
        // remove node a from tree
        $tree = $this->deleteNode($a, $tree);

        if (count($tree) < 4) {
          // check for all remaining node (three) in setA weather its have children
          $replaceNodeId = $this->getReplaceableNodeId($setA, $tree);
          // remove this replaceable node from setA
          unset($setA[array_search($replaceNodeId, $setA)]); $setA = array_values($setA);
          // replace this node (replaceable) with its children
          $tree = $this->replaceNodeWithItsChildren($replaceNodeId, $tree);
        }
        
        if(count($tree) == 4) {
          $match = true;
          foreach($setA as $key => $a) {
            if($tree[$key]['id'] != $a) {
              $match = false;
              break;
            }
          }
          if ($match) {
            // add node to setA, replacing the previous unset (one or more node)
            ['newSet' => $setA] = $this->addNextNode($setA, $tree, -1);
          } else {
            // masukkan node baru setelah node terakhir yang tidak mempunyai children
            // biasanya berurutan, setelah node ini seharusnya mempunyai children semua
            $idx = $this->getLastNodeIdxWithoutChildren($setA, $tree);
            ['newSet' => $setA] = $this->addNextNode($setA, $tree, $idx);
          }
        } elseif(count($tree) > 4) {
          ['newSet' => $setA] = $this->addNextNode($setA, $tree, 0);
        }
        
        $view = array_merge($setA, $staticViewIds);
        $ig = $this->ig($view);
      }

      if($ig > $igMax) {
        $igMax = $ig;
        $aMax = $setA;
      }

      // log purpose
      // $this->logSetAndIG($setA, $ig, $igMax);      
      // dev purpose
      // $count++;
      // if ($count > 14) break;
    }

    // Amax is available
    return $this->getFilesFromFileIds($aMax);
  }

/**
 * MAIN LOGIC END
 */

/**
 * BIGFILE UTILITY START
 */

  // make tree level 1 nodes atleast $n or higher
  private function convertTreeToNBranch($tree, $n = 4)
  {    
    while (count($tree) < $n and $this->isTreeExplorable($tree)) { 
      foreach ($tree as $key => $branch) {
        if (isset($branch['children'])) {
          array_splice($tree, $key, 1, $branch['children']);
          break;
        }
      }
    }
    return $tree;
  }

  private function isTreeExplorable($tree)
  {
    foreach($tree as $node) {
      if(isset($node['children'])) {
        return true;
      }
    }
    return false;
  }

  private function hasChildInTree($a, $tree)
  {
    foreach ($tree as $node) {
      if ($node['id'] === $a and isset($node['children'])) {
        return $node['children'][0]['id'];
      }
    }
    return false;
  }

  private function deleteNode($nodeId, $tree)
  {
    foreach ($tree as $key => $node) {
      if ($node['id'] == $nodeId) {
        unset($tree[$key]);
        break;
      }
    }
    return array_values($tree);
  }

  private function deleteChildren($nodeId, $tree)
  {
    foreach ($tree as $key => $node) {
      if ($node['id'] == $nodeId) {
        unset($tree[$key]['children']);
        break;
      }
    }
    return array_values($tree);
  }

  // add one or mode node to setA
  // default: insert at beginning of array
  private function addNextNode($setA, $tree, $pos = 0)
  {
    $newNodes = [];
    while(count($setA) + count($newNodes) < 4) {
      foreach($tree as $node) {
        if(!in_array($node['id'], $setA) and !in_array($node['id'], $newNodes)) {
          $newNodes[] = $node['id'];
          break;
        }
      }
    }
    // insert as first element of array
    if($pos == 0) { $setA = array_merge($newNodes, $setA); } 
    // insert as last element of array
    elseif($pos == -1) { $setA = array_merge($setA, $newNodes); }
    else { array_splice($setA, $pos, 0, $newNodes); }

    return [
      'newSet' => $setA,
      'addedNodes' => $newNodes,
    ];
  }

  private function getReplaceableNodeId($setA, $tree)
  {
    foreach($setA as $a2) {
      if ($this->hasChildInTree($a2, $tree)) {
        return $a2;
      }
    }
    return null;
  }

  private function replaceNodeWithItsChildren($nodeId, $tree)
  {
    foreach($tree as $key => $node) {
      if($node['id'] == $nodeId) {
        $children = $node['children'];
        array_splice($tree, $key, 1, $children);
        return $tree;
      }
    }
  }

  private function getLastNodeIdxWithoutChildren($setA, $tree)
  {
    foreach($setA as $key => $a) {
      // check node a, is it have a children
      if($this->hasChildInTree($a, $tree)) {
        return intval($key); 
      }
    }
    return -1;
  }

  private function getFilesFromFileIds($ids)
  {
    $files = [];
    foreach($ids as $id) {
      foreach($this->files as $file) {
        if($file['id'] == $id) {
          $files[] = $file;
        }
      }
    }
    ArrayHelper::multisort($files, ['viewedByMeTime'], [SORT_DESC]);
    return $files;
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

  public function convertTreetoArray($tree, &$outArray)
  {
    foreach($tree as $node) {
      if(isset($node['children'])) {
        $this->convertTreetoArray($node['children'], $outArray);
        unset($node['children']);
      }
      $outArray[] = $node;
    }
  }

/**
 * BIGFILE UTILITY END
 */


/**
 * DEVELOPMENT UTILITY
 */

  private function logSetAndIG($setA, $ig, $igMax)
  {
    echo "A = [ <br>";
    foreach($setA as $a) {
      echo " $a, <br>";
    }
    echo "] <br>";
    echo "ig = $ig, igmax = $igMax <br><br><br>";
  }
}
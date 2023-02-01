<?php

namespace app\modules\facilitator\models;

use yii\db\ActiveRecord;

class UtTaskTarget extends ActiveRecord
{    
  /**
   * @return string the name of the table associated with this ActiveRecord class.
   */
  public static function tableName()
  {
    return 'ut_task_target';
  }

  public static function isDescribeComplete($taskId)
  {
    $targets = self::find()
      ->where(['task_id' => $taskId])
      ->all();
    foreach($targets as $target) {
      if(
          !boolval($target['description'])
          or $target['status'] == 'not_valid' 
        ) {
        return false;
      }
    }
    return true;
  }

  public static function getTargetToDescribe($taskId)
  {
    $target = self::find()
      ->where(['task_id' => $taskId])
      ->andWhere(new \yii\db\conditions\OrCondition([
        ['=', 'description', null],
        ['=', 'description', ''],
        ['=', 'status', 'init'],
        ['=', 'status', 'not_valid'],
      ]))
      ->one();
    return $target;
  }
}
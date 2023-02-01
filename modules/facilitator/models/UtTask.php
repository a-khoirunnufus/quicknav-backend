<?php

namespace app\modules\facilitator\models;

use yii\db\ActiveRecord;

class UtTask extends ActiveRecord
{    
  /**
   * @return string the name of the table associated with this ActiveRecord class.
   */
  public static function tableName()
  {
    return 'ut_task';
  }
}
<?php

namespace app\modules\facilitator;

class Module extends \yii\base\Module
{
  public function init()
  {
    parent::init();
    
    $this->defaultRoute = 'facilitator/home/index';
    $this->layout = '@app/modules/facilitator/views/layouts/main.php';
  }
}
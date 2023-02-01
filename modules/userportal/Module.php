<?php

namespace app\modules\userportal;

class Module extends \yii\base\Module
{
  public function init()
  {
    parent::init();
    
    $this->defaultRoute = 'userportal/home/index';
    $this->layout = '@app/modules/userportal/views/layouts/main.php';
  }
}
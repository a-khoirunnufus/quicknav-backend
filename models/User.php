<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
  public static function tableName()
  {
    return 'user';
  }

  /**
   * Finds an identity by the given ID.
   *
   * @param string|int $id the ID to be looked for
   * @return IdentityInterface|null the identity object that matches the given ID.
   */
  public static function findIdentity($id)
  {
    return static::findOne($id);
  }

  /**
   * Finds an identity by the given token.
   *
   * @param string $token the token to be looked for
   * @return IdentityInterface|null the identity object that matches the given token.
   */
  public static function findIdentityByAccessToken($token, $type = null)
  {
    return static::findOne(['access_token' => $token]);
  }

  /**
   * @return int|string current user ID
   */
  public function getId()
  {
      return $this->id;
  }

  /**
   * @return string|null current user auth key
   */
  public function getAuthKey()
  {
    return $this->auth_key;
  }

  /**
   * @param string $authKey
   * @return bool|null if auth key is valid for current user
   */
  public function validateAuthKey($authKey)
  {
    return $this->getAuthKey() === $authKey;
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {
      if ($this->isNewRecord) {
        $this->auth_key = Yii::$app->getSecurity()->generateRandomString();
        $this->access_token = Yii::$app->getSecurity()->generateRandomString();
      }
      return true;
    }
    return false;
  }

  /**
   * @return boolean Whether the saving succeeded (i.e. no validation errors occurred).
   */
  public static function registerWithGoogleAccount($payload)
  {
    $user = new self();
    $user->name = $payload['name'];
    $user->email = $payload['email'];
    
    return $user->save();
  }

  public static function isUTParticipant($userId)
  {
    return (new \yii\db\Query())
      ->select(['id'])
      ->from('ut_participant')
      ->where(['user_id' => $userId])
      ->exists();
  }

  public static function getUserByParticipantId($pId)
  {
    $res = (new \yii\db\Query())
      ->select(['user_id'])
      ->from('ut_participant')
      ->where(['id' => $pId])
      ->one();
    $userId = $res['user_id'];
    return self::findOne($userId);
  }
}

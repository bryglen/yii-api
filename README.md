Bryan Tan Yii API
=================

INSTALLATION
------------

### Usage

create a class called ApiController or any name you want but must extend the AApiController

```php
class AccountController extends AApiController
{
	public function actionArray()
	{
		 $model = User::model()->find();

		 $response = $this->toArray($model,array(
		 	'user_id' => 'id',
			'username',
			'fullname' => function($model) {
				return $model->first_name . ' ' . $model->last_name;
			}
		 ));

		 $this->sendResponse($response);
	}
}
```

### Enable Audit Logging

in your configuration file add this if you want to enable audit logging in your api call

```php
'components' => array(
	'auditTrail'=>array(
	'class'=>'ApiAuditTrail',
	'enableCreateTable'=>true,
 ),
```


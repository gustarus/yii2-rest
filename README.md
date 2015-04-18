Webulla Yii2 rest actions
=========================

Component library contains standalone actions for processing rest requests.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ composer require "webulla/yii2-rest":"*"
```

## Configuration


In my project I use [Backbone](http://backbonejs.org/) to work with models on the client side. Therefore, all *ajax* requests from the model (method *Backbone.sync()*) are divided into four types: *GET*, *POST*, *PUT* and *DELETE*. To process these requests, I realized standalone actions.

I immediately give an example, on the basis of which it will be easier to understand how to work with the component. This model of the controller, which is used for working with posts.
```php
namespace app\controllers;

use Yii;
use app\models\Post;
use webulla\rest\actions\RestAction;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends \app\components\web\Controller {

	/**
	 * @inheritdoc
	 */
	public function actions() {
		return [
			'rest' => [
				'class' => RestAction::className(),
				'modelClass' => Post::className(),
			],
		];
	}
}
```

A detailed description of the settings:
```php
...
'rest' => [
    // action class
    'class' => RestAction::className(),

    // model class
    'modelClass' => Post::className(),

    // allowed methods for request
    // default: ['get', 'post', 'put', 'delete']
    'methods' => ['get', 'post', 'put', 'delete'],
],
...
```

Allowed the following types of queries:
* *get* - fetch existing model;
* *post* - create new model;
* *put* - update existing model;
* *delete* - delete existing model.

If you transfer data using one of the following methods: get, put, delete - you must also pass the primary key (id attribute) of the model:
```
yoursite.com/post/rest?id=1
```
You can also use short URL for your rest api. To do this, configure the component *urlManager* in your configuration file:
```php
...
'urlManager' => [
    // request query "/controller/action?id=1" instead of "?r=controller/action&id=1"
    'enablePrettyUrl' => true,

    // request query "/controller/action" instead of "index.php/controller/action"
    'showScriptName' => false,

    'rules' => [
        // default rule
        'post/rest' => '/post/rest',

        // allow "/post/rest/1" request instead of "/post/rest?id=1"
        'post/rest/<id:\d+>' => '/post/rest',
    ]
],
```

## Usage

A simple example, which receives data through the model rest api:
```javascript
$(function() {
    $.ajax({
        url: '/post/rest?id=23',
        type: 'get',
        dataType: 'json',

        success: function(attributes) {
            console.log(attributes);
        }
    });
});
```

Response:
```json
{
	"id":"23",
	"user_id":"1",
	"content":"Text here",
	"created_at":"2015-04-04 14:08:10",
	"updated_at":"2015-04-04 14:08:20"
}
```

## Security

Rest api as used in my project through javascript, I added the ability to protect your data: you can explicitly specify which security attributes for each type of request. There are two ways for the configuration of safe attributes:
* When you save any data via rest api (*post*, *put*), the model is created with the scenario *rest-save*.
* When you get some data via rest api (*get*), the model is created with the scenario *rest-fetch*.

This is my Post model which I use in rest request:
```php
class Post extends \yii\db\ActiveRecord {
    ...
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			...
			// this validator boosted sets the form while preserving the model through the rest api
			[['user_id'], ForceValueValidator::className(), 'value' => Yii::$app->user->getId(), 'on' => 'rest-save'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios() {
		return array_merge(parent::scenarios(), [
		    // these attributes are writable by rest api
			'rest-save' => ['user_id', 'content'],

			// these attributes can be read through the rest api
			'rest-fetch' => ['id', 'user_id', 'content', 'created_at', 'updated_at'],
		]);
	}
    ...
}
```
<?php
/**
 * Created by:  Itella Connexions ©
 * Created at:  19:04 01.04.15
 * Developer:   Pavel Kondratenko
 * Contact:     gustarus@gmail.com
 */

namespace webulla\rest\actions;

use Yii;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;

/**
 * Class RestModelAction
 * @package webulla\rest\actions
 */
class RestAction extends \yii\base\Action {

	/**
	 * Model class name.
	 * @var ActiveRecord
	 */
	public $modelClass;

	/**
	 * Allowed request methods.
	 * @var array
	 */
	public $methods = ['get', 'post', 'put', 'delete'];


	/**
	 * @inheritdoc
	 */
	public function init() {
		parent::init();

		// configure verbs behaviour
		if($this->methods) {
			if(!$this->controller->getBehavior('verbs')) {
				$this->controller->attachBehavior('verbs', new VerbFilter());
			}

			$this->controller->getBehavior('verbs')->actions[$this->id] = $this->methods;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function run() {
		/** @var ActiveRecord $model */

		// call reqest handler method
		$model = call_user_func([$this, 'handle' . Yii::$app->getRequest()->getMethod()]);

		// set response format to json
		Yii::$app->response->format = Response::FORMAT_JSON;

		// get only safe attributes for current scenario
		$model->scenario = 'rest-fetch';
		$names = $model->safeAttributes();
		$names[] = 'id';
		$attributes = $model->getAttributes($names);

		return $attributes;
	}

	/**
	 * @return ActiveRecord
	 */
	public function handleGet() {
		return $this->loadModelFromRequest();
	}

	/**
	 * @return ActiveRecord
	 */
	public function handlePost() {
		return $this->chainSave($this->loadModel());
	}

	/**
	 * @return ActiveRecord
	 */
	public function handlePut() {
		return $this->chainSave($this->loadModelFromRequest());
	}

	/**
	 * @return ActiveRecord
	 */
	public function handleDelete() {
		return $this->chainDelete($this->loadModelFromRequest());
	}

	/**
	 * @param ActiveRecord $model
	 * @return ActiveRecord
	 * @throws \yii\web\ServerErrorHttpException
	 */
	protected function chainSave($model) {
		$model->scenario = 'rest-save';
		$model->attributes = Yii::$app->getRequest()->post();
		if(!$model->save()) {
			throw new ServerErrorHttpException('Do not able to save the model.');
		}

		$model->refresh();

		return $model;
	}

	/**
	 * @param ActiveRecord $model
	 * @return ActiveRecord
	 * @throws \yii\web\ServerErrorHttpException
	 */
	protected function chainDelete($model) {
		if(!$model->delete()) {
			throw new ServerErrorHttpException('Do not able to delete the model.');
		}

		return $model;
	}

	/**
	 * @return ActiveRecord::
	 */
	protected function loadModel() {
		return new $this->modelClass();
	}

	/**
	 * @return ActiveRecord
	 * @throws \yii\web\NotFoundHttpException
	 * @throws \yii\web\BadRequestHttpException
	 */
	protected function loadModelFromRequest() {
		$class = $this->modelClass;
		if(!$id = Yii::$app->getRequest()->get('id')) {
			throw new BadRequestHttpException('Parameter "id" is required.');
		}

		/** @var ActiveRecord $model */
		if(!$model = $class::findOne($id)) {
			throw new NotFoundHttpException('Model was not found.');
		}

		return $model;
	}
} 
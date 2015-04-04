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
use yii\web\UnauthorizedHttpException;

/**
 * Class RestModelAction
 * @package webulla\rest\actions
 */
class RestUserAction extends \webulla\rest\actions\RestAction {

	/**
	 * @inheritdoc
	 */
	public function init() {
		parent::init();

		// set default model class from user component configuration
		if(!$this->modelClass) {
			$this->modelClass = Yii::$app->getUser()->identityClass;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function handleGet() {
		// return authorized user
		if(!Yii::$app->getRequest()->get('id')) {
			if($identity = Yii::$app->getUser()->getIdentity()) {
				// create new model
				$model = new $this->modelClass(['scenario' => 'rest']);

				// copy all attributes
				$model->setAttributes($identity->getAttributes(), false);

				return $model;
			} else {
				throw new UnauthorizedHttpException();
			}
		}

		return parent::handleGet();
	}
} 
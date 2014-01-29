<?php
/**
 * based class for our api controller
 * the purpose of this is we can easily migrate it to another project
 *
 * you must set auditTrail component to enable auditTrail
 * in your main.php
 *
 * 'components' => array(
 *    'auditTrail'=>array(
 *    'class'=>'ApiAuditTrail',
 *    'enableCreateTable'=>true,
 * ),
 *
 * @author Bryan Jayson Tan <admin@bryantan.info>
 * @link http://bryantan.info
 * @date 1/29/13
 * @time 2:49 PM
 * @version 2.0.2
 */
class AApiController extends CController
{
    public $auditTrailComponentName='auditTrail';
    /**
     * @var null|AApiAuditTrail
     */
    protected $auditTrail=null;

    /**
     * Send json encode HTTP response
     * @param string $body The body of the HTTP response
     * @param int $status HTTP status code
     */
    protected function sendResponse($body = '',$status = 200)
    {
        if ($this->beforeSendResponse($body)){
            // Set the status
            $statusHeader = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
            header($statusHeader);
            // Set the content type
            header('Content-type: application/json');

            echo CJSON::encode($body);
        }
        $this->afterSendResponse();
    }

    /**
     * send json encode response error, default status code is 400
     * @param string $body The body of the HTTP response
     * @param int $status HTTP status code, default to 400
     */
    public function sendError($body,$status=400) {
        if ($this->beforeSendError($body)){
            // Set the status
            $statusHeader = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
            header($statusHeader);
            // Set the content type
            header('Content-type: application/json');

            echo CJSON::encode($body);
        }
        $this->afterSendError();
    }

    /**
     * check if we have a component of auditTrail
     * attached error and exception handle to the response
     * @throws CException
     */
    public function init() {
        $auditTrail = Yii::app()->getComponent($this->auditTrailComponentName);
        if ($auditTrail instanceof AApiAuditTrail){
            $this->auditTrail=$auditTrail;
            // attached an event handler if there is a component of audit trail
            Yii::app()->onEndRequest = array($this,'executeAuditTrail');
        }
        Yii::app()->attachEventHandler('onError',array($this,'handleError'));
        Yii::app()->attachEventHandler('onException',array($this,'handleError'));
    }

    /**
     * handle error exception
     * @param CEvent $event
     */
    public function handleError(CEvent $event)
    {
        if ($event instanceof CExceptionEvent)
        {
            $code=$event->exception->getCode() ? $event->exception->getCode() : 500;

            $this->sendError($event->exception->getMessage(),$code);
        }
        elseif($event instanceof CErrorEvent)
        {
            $code=$event->code ? $event->code : 500;
            $this->sendError($event->message,$code);
        }

        $event->handled = TRUE;
    }

    /**
     * event handler for executing audit trail
     */
    public function executeAuditTrail()
    {
        if ($this->auditTrail){
            $this->auditTrail->execute();
        }
    }

    /**
     * raise an event handler before send response
     * @param $event
     */
    public function onBeforeSendResponse($event)
    {
        $this->raiseEvent('onBeforeSendResponse',$event);
    }

    /**
     * raise an event handler after send response
     * @param $event
     */
    public function onAfterSendResponse($event)
    {
        $this->raiseEvent('onAfterSendResponse',$event);
    }

    /**
     * raise an event handler before send response
     * @param $event
     */
    public function onBeforeSendError($event)
    {
        $this->raiseEvent('onBeforeSendError',$event);
    }

    /**
     * raise an event handler after send response
     * @param $event
     */
    public function onAfterSendError($event)
    {
        $this->raiseEvent('onAfterSendResponse',$event);
    }

    /**
     * event handler before execute method was being called
     * @return bool
     */
    public function beforeSendResponse($body)
    {
        if ($this->hasEventHandler('onBeforeSendResponse')){
            $event = new AApiEvent();
            $this->onBeforeSendResponse($event);
            return $event->isValid;
        }else{
            return true;
        }
    }

    /**
     * event handler after execute method was being called
     */
    public function afterSendResponse()
    {
        if($this->hasEventHandler('onAfterSendResponse'))
            $this->onAfterSendResponse(new CEvent($this));
        Yii::app()->end();
    }

    /**
     * event handler before execute method was being called
     * @return bool
     */
    public function beforeSendError($body)
    {
        if ($this->hasEventHandler('onBeforeSendError')){
            $event = new AApiEvent();
            $this->onBeforeSendError($event);
            return $event->isValid;
        }else{
            return true;
        }
    }

    /**
     * event handler after execute method was being called
     */
    public function afterSendError()
    {
        if($this->hasEventHandler('onAfterSendError'))
            $this->onAfterSendError(new CEvent($this));
        Yii::app()->end();
    }

    /**
     * Return the http status message based on integer status code
     * @param int $status HTTP status code
     * @return string status message
     */
    protected function _getStatusCodeMessage($status)
    {
        $codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',

        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    /*** GLOBAL RESPONSE FOR MODEL  ***/

    /**
     * Converts an object into an array.
     * <code>
     * $this->toArray($object,array(
     *    // display the 'id' attribute
     *    'id',
     *    // display the 'name' attribute
     *    'name',
     *     // display the 'first_name' attribute of the 'auto' relation
     *    'author.first_name',
     *    // display the author_first_name as key and the value of model
     *    'author_first_name'=>'author.first_name',
     *    // the key name in array result => property name
     *    'createTime' => 'create_time',
     *    'length' => function ($post) {
     *         return strlen($post->content);
     *    },
     * ));
     * </code>
     *
     * if properties is empty. we will use CActiveRecord attributes
     * <code>
     * $this->toArray($object);
     * </code>
     *
     * @param CActiveRecord $object
     * @param array $columns the columns to be displayed on the response
     * @throws CException
     * @return array
     */
    protected function toArray($object,$columns=array())
    {
        if (!$object instanceof CActiveRecord)
        {
            throw new CException('Object must be an instance of CActiveRecord');
        }
        $result=array();
        // if properties is empty. we will use the object attributes
        if (empty($columns)){
            $columns = $object->attributes;
        }
        foreach($columns as $key=>$column) {
            if (is_int($key)) {
                $result[$column] = CHtml::value($object,$column);
            } else if (is_string($key) && $object->hasAttribute($key) && !$column instanceof Closure) {
                $result[$key] = CHtml::value($object,$key);
            } else if (is_string($key)) {
                $result[$key] = CHtml::value($object,$column);
            }
        }

        return $result;
    }

    /**
     * global function to display single model response
     * @param CActiveRecord $model
     * @param $columns
     */
    public function sendSingleModelResponse($model, $columns=array())
    {
        if (!is_object($model)) {
            $this->sendError(sprintf("Error: Didn't find any model %s.", get_class($model)), 400);
        }

        $response=$this->toArray($model,$columns);

        $this->sendResponse($response);
    }

    /*
     * global function do display single model validation messages
     * @param $model
     */
    public function sendSingleModelErrorResponse($model,$status=500)
    {
        $this->sendMultipleModelErrorResponse(array($model),$status);
    }

    /**
     * global function do display multiple model validation messages
     *
     * it will return a list of errors
     * <code>
     * array(
     *    'Username cannot be blank',
     *    'Password cannot be blank'
     * )
     * </code>
     * @param $models
     */
    public function sendMultipleModelErrorResponse($models,$status=500)
    {
        if (!is_array($models)) {
            $this->sendError('Model is not an array.');
        }
        $error = array();
        foreach($models as $model) {
            foreach ($model->errors as $attribute => $attr_errors) {
                foreach ($attr_errors as $attr_error) {
                    $error[] = $attr_error;
                }
            }
        }
        $this->sendError($error, $status);
    }

    /**
     * global function for basic listing
     * inherit the logic for CActiveDataProvider
     *
     * <code>
     * $this->basicList(Post::model(),
     *    array(
     *       'pagination'=>array(
     *          'pageVar'=>'page',
     *          'pageSize'=>100,
     *    ),
     *    array(
     *       'id',
     *       'author_name'=>'author.name',
     *       'created_at'=> function($post) {
     *           strtotime($post->created_at)
     *       },
     *    ),
     * ))
     * </code>
     * @param $modelClass
     * @param array $columns the properties to be rendered
     * @param array $config
     * @see CActiveDataProvider
     */
    public function basicList($modelClass, $columns=array(), $config=array())
    {
        $dataProvider = new CActiveDataProvider($modelClass,$config);

        $response=array();
        foreach($dataProvider->getData() as $data) {
            $response[]=$this->toArray($data,$columns);
        }
        $this->sendResponse($response);
    }

    /**
     * global function for basic view
     *
     * Usage 1:
     * <code>
     * $this->basicView('User',1,$columns);
     * </code>
     *
     * Usage 2:
     * <code>
     * $this->basicView(User::model()->active()->findByPk(1),null,$columns);
     * </code>
     * @param string|CActiveRecord $class
     * @param int $id
     * @param array $columns
     */
    public function basicView($class,$id,$columns=array())
    {
        // Check if id was submitted
        if (!$id) {
            $this->sendError("Error: Parameter 'id' is missing", 500);
        }

        $model=null;
        if (is_string($class)) {
            // use default find by pk
            $model=$class::model()->findByPk($id);
        }else if (is_object($class)) {
            // if the class is already a object. pass it to variable
            $model=$class;
        }

        if (is_null($model)) {
            $this->sendError(sprintf("Error: Didn't find any model %s with ID '%s'.", $class, $id), 400);
        }

        $this->sendSingleModelResponse($model,$columns);
    }

    /**
     * @param $class
     * @param null $scenario
     * @param array $columns
     */
    public function basicCreate($class,$scenario=null,$columns=array())
    {
        /* @var $model CActiveRecord */
        $model=null;
        if (is_string($class)) {
            // use default create
            $model=new $class;
        }else if (is_object($class)) {
            // if the class is already a object. pass it to variable
            $model=$class;
        }
        // set a scenario if $scenario is not null
        if (!is_null($scenario))
            $model->setScenario($scenario);
        foreach($_POST as $var => $value) {
            // Does the model have this attribute?
            if ($model->hasAttribute($var) || $model->isAttributeSafe($var)) {
                $model->$var = $value;
            }
        }
        // Try to save the model
        if ($model->save()) {
            // Saving was OK
            $model->refresh();

            $this->sendSingleModelResponse($model,$columns);
        } else {
            $this->sendSingleModelErrorResponse($model);
        }
    }

    /**
     * @param CActiveRecord $class
     * @param int $id
     * @param null $scenario
     * @param array $columns
     */
    public function basicUpdate($class,$id,$scenario=null,$columns=array())
    {
        /* @var $model CActiveRecord */
        $model=null;
        if (is_string($class)) {
            // use default find all
            $model=$class::model()->findByPk($id);
        }else if (is_object($class)) {
            // if the class is already a object. pass it to variable
            $model=$class;
        }

        // set a scenario if $scenario is not null
        if (!is_null($scenario))
            $model->setScenario($scenario);

        if (is_null($model)) {
            $this->sendError(sprintf("Error: Didn't find any model %s with ID '%s'.", $class, $id), 400);
        }
        // Try to assign PUT parameters to attributes
        //foreach ($put_vars as $var => $value) {
        foreach ($_POST as $var => $value) {
            // Does model have this attribute?
            if ($model->hasAttribute($var) || $model->isAttributeSafe($var)) {
                // skip it if the $value is empty or null, retain the old value
                if ($value) {
                    $model->$var = $value;
                }
            }
        }

        // Try to save the model
        if ($model->save()) {
            //$this->set('messages', sprintf('The model "%s" with id %s has been updated.', $class, $id));
            $model->refresh();

            $this->sendSingleModelResponse($model,$columns);
        } else {
            $this->sendSingleModelErrorResponse($model);
        }
    }

    /**
     * basic function for delete
     * @param $class
     * @param $id
     */
    public function basicDelete($class,$id)
    {
        /* @var $model CActiveRecord */
        $model=$class::model()->findByPk($id);

        // Was a model found?
        if (is_null($model)) {
            // No, raise an error
            $this->sendError(sprintf("Error: Didn't find any model %s with ID %s.", $class, $id), 400);
        }

        // Delete the model
        try{
            $num = $model->delete();
            if ($num > 0) {
                $this->sendResponse(sprintf("Model %s with ID %s has been deleted.", get_class($model), is_array($id) ? implode(', ',$id) : $id));
            } else {
                $this->sendError(sprintf("Error: Couldn't delete model %s with ID %s.", get_class($model), $id), 500);
            }
        }catch(Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}

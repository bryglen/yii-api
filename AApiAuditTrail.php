<?php
/** *
 * a base class for our audit trail
 * a child class must override this and set a component in a config file
 *
 * in your main.php
 * 'components' => array(
 *    ......
 *    'auditTrail'=>array(
 *       'class'=>'ApiAuditTrail',
 *       'enableCreateTable'=>true,
 *       //'tableName'=>'tbl_audit_trail',
 * ),
 *
 * by default the child class may use 4 property
 *
 * <code>
 * $executionTime = the total execution time of the request
 * $memoryUsage = the total memory usage of the app
 * $controllerId = determine what is the current controller being used. this value can be null
 * $actionId = determine what is the current action being used. this value can be null
 * </code>
 *
 * @author Bryan Jayson Tan <admin@bryantan.info>
 * @link http://bryantan.info
 * @date 7/5/13
 * @time 1:53 AM
 * @version 2.0.0
 */
abstract class AApiAuditTrail extends CApplicationComponent
{
    public $enableCreateTable=true;
    public $tableName='tbl_api_audit_trail';

    protected $executionTime;
    protected $memoryUsage;
    protected $controllerId;
    protected $actionId;

    /**
     * init function.
     * by default we set enableCreateTable = true
     * in production mode. you must set this turn off but you must make sure that the table is being created on production
     */
    public function init()
    {
        if ($this->enableCreateTable===true){
            $this->createTable();
        }
    }

    /**
     * execute audit trail logging
     * this method is being called when the request is done or in afterAction() method
     */
    public function execute()
    {
        if ($this->beforeExecute()){
            $this->executionTime=Yii::getLogger()->getExecutionTime();
            $this->memoryUsage=Yii::getLogger()->getMemoryUsage();

            $controller = Yii::app()->controller;
            $this->controllerId = $controller ? $controller->id : null;
            $this->actionId = $controller && $controller->getAction() ? $controller->getAction()->getId() : null;

            $this->insertTrailing();
        }
        $this->afterExecute();
    }

    /**
     * an sql script or a model that will insert data to the audit trail table
     * @note a child class must override this
     *
     * Usage 1
     * @note make sure you have a model for audit trail
     * <code>
     * $model=new ApiAuditTrail();
     * $model->api_user_id=Yii::app()->user->id;
     * $model->execution_time=$this->execution_time;
     * $model->memory_usage=$this->memory_usage;
     * $model->controller=$controllerId;
     * $model->action=$actionId;
     * $model->params=CJSON::encode($_REQUEST);
     * $model->save(false);
     * </code>
     *
     * Usage 2
     * <code>
     * $sqlStatement = "INSERT INTO {api_audit_trail} (api_user_id,execution_time,memory_usage,controller,action,params,status,status_message)
     *          VALUES (:api_user_id,:execution_time,:memory_usage,:controller,:action,:params,:status,:status_message)";
     * $command = $connection->createCommand($sqlStatement);
     * $command->bindParam(':api_user_id',Yii::app()->user->id);
     * ....
     * $command->execute();
     * </code>
     *
     * Usage 3
     * <code>
     * $connection = Yii::app()->db;
     * $connection->createCommand()->insert($this->tableName,array(
     *     'api_user_id'=>Yii::app()->user->id,
     *     'execution_time'=>$this->executionTime,
     *     'memory_usage'=>$this->memoryUsage,
     *     'controller'=>$this->controllerId,
     *     'action'=>$this->actionId,
     *     'params'=>CJSON::encode($_REQUEST),
     * ));
     * </code>
     */
    public function insertTrailing()
    {

    }

    /**
     * a method for creating a table
     * a child class must override this
     *
     * <code>
     * $connection=Yii::app()->db;
     * $sql="CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
     *     `id` int(11) NOT NULL AUTO_INCREMENT,
     *     `api_user_id` int(11) DEFAULT NULL,
     *     `controller` varchar(100) NOT NULL,
     *     `action` varchar(100) NOT NULL,
     *     `params` longtext NOT NULL,
     *     `execution_time` varchar(15) DEFAULT NULL,
     *     `memory_usage` varchar(15) DEFAULT NULL,
     *     `create_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     *     PRIMARY KEY (`id`)
     *     ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
     * $command=$connection->createCommand($sql);
     * $command->execute();
     * </code>
     *
     * @note you must make sure that you will use $tableName property, by default the name of the table is 'tbl_api_audit_trail'
     */
    public function createTable()
    {

    }

    /**
     * raise an event handler before executing the audit trail
     * @param $event
     */
    public function onBeforeExecute($event)
    {
        $this->raiseEvent('onBeforeExecute',$event);
    }

    /**
     * raise an event handler after executing the audit trail
     * @param $event
     */
    public function onAfterExecute($event)
    {
        $this->raiseEvent('onAfterExecute',$event);
    }

    /**
     * event handler before execute method was being called
     * @return bool
     */
    public function beforeExecute()
    {
        if ($this->hasEventHandler('onBeforeExecute')){
            $event = new AApiEvent();
            $this->onBeforeExecute($event);
            return $event->isValid;
        }else{
            return true;
        }
    }

    /**
     * event handler after execute method was being called
     */
    public function afterExecute()
    {
        if($this->hasEventHandler('onAfterExecute'))
            $this->onAfterExecute(new CEvent($this));
    }
}

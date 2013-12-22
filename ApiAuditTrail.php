<?php
/**
 * @author Bryan Jayson Tan <admin@bryantan.info>
 * @link http://bryantan.info
 * @date 7/5/13
 * @time 1:53 AM
 */
class ApiAuditTrail extends AApiAuditTrail
{
    public function insertTrailing()
    {
        if (!$this->controllerId || !$this->actionId){
            return null;
        }
        $connection = Yii::app()->db;
        $connection->createCommand()->insert($this->tableName,array(
            'api_user_id'=>Yii::app()->user->id,
            'execution_time'=>$this->executionTime,
            'memory_usage'=>$this->memoryUsage,
            'controller'=>$this->controllerId,
            'action'=>$this->actionId,
            'params'=>CJSON::encode($_REQUEST),
        ));
    }

    public function createTable($params=array())
    {
        $connection=Yii::app()->db;
        $sql="CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `api_user_id` int(11) DEFAULT NULL,
          `controller` varchar(100) NOT NULL,
          `action` varchar(100) NOT NULL,
          `params` longtext NOT NULL,
          `execution_time` varchar(255) DEFAULT NULL,
          `memory_usage` varchar(255) DEFAULT NULL,
          `create_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $command=$connection->createCommand($sql);
        $command->execute();
    }
}

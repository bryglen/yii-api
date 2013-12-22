<?php
/**
 *
 * an based event class for handling the ApiAuditTrail
 * a child property must override this if needed to customize
 *
 * @author Bryan Jayson Tan <admin@bryantan.info>
 * @link http://bryantan.info
 * @date 7/5/13
 * @time 2:10 AM
 * @version 2.0.0
 */
class AApiEvent extends CEvent
{
    public $isValid=true;
}

<?php
/**
 * Class for using a RabbitMQ Exchange for queueing
 *
 * @category AMQP
 * @package AMQP_Queue
 * @author Ritesh Jha
 * @copyright Copyright (c) mailrkj(at)gmail(dot)com
 * @see http://riteshsblog.blogspot.com/2011/03/rabbitmq-adapter-for-zend-queue-using.html
 */

class AMQP_Queue_Exchange extends AMQPExchange
{
    public function __construct(AMQPConnection $connection, $exchange_name, $type = AMQP_EX_TYPE_DIRECT, $flags = AMQP_AUTODELETE)
    {
        parent::__construct($connection);
        $this->declare($exchange_name, $type, $flags);
    }
}

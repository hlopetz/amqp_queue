<?php
class Custom_Queue_Exchange extends AMQPExchange
{
    public function __construct(AMQPConnection $connection, $exchange_name, $type = AMQP_EX_TYPE_DIRECT, $flags = AMQP_AUTODELETE)
    {
        parent::__construct($connection);
        $this->declare($exchange_name, $type, $flags);
    }
}

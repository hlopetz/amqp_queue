<?php
/**
 * Class for using a Rabbitmq as a queue
 *
 * @category Custom
 * @package Custom_Queue
 * @subpackage Adapter
 * @author Ritesh Jha
 * @copyright Copyright (c) (http://mailrkj(at)gmail(dot)com)
 */

class Custom_Queue extends Zend_Queue
{
    var $_instance = null;

    public function __construct($adapter, $options = array())
    {
        if ($adapter instanceof Custom_Queue_Adapter_Rabbitmq) {
            parent::__construct($adapter, $options);

            #declare new queue
            $queueName = (array_key_exists('name', $options)) ? $options['name'] : 'queue';
            if (array_key_exists('flag', $options)) $adapter->setQueueFlag($options['flag']);
            $queue = $adapter->create($queueName);
            $this->_setName($queueName);
            #declare exchange
            $routingKey = (array_key_exists('routingKey', $options)) ? $options['routingKey'] : '*';
            $exchangeName = (array_key_exists('exchange', $options)) ? $options['exchange'] : 'exchange';
            $ex = $adapter->setExchange($exchangeName, $routingKey);
            $this->setOptions($options);
        }
        else
        {
            throw new Zend_Queue_Exception("Invalid Rabbitmq adapter");
        }
    }

    /**
     * Create a new queue
     * @param string $name queue name
     * @param int $flag A bitmask of any of the flags: AMQP_AUTODELETE, AMQP_PASSIVE, AMQP_DURABLE, AMQP_NOACK.
     * @return int (message count)
     */
    public function createQueue($name, $flag = AMQP_DURABLE)
    {
        $this->getAdapter()->setQueueFlag($flag);
        parent::createQueue($name);
    }


    /**
     * Delete a queue and all of it's messages
     * Returns false if the queue is not delete, true if the queue deleted
     * @param string $name queue name
     * @return boolean
     */
    public function deleteQueue($name)
    {
        return $this->getAdapter()->delete($name);
    }

    /**
     * Send a message to the queue
     *
     * @param array|string $message message
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Exception
     */
    public function send($message)
    {
        return $this->getAdapter()->send($message);
    }

    /**
     * Consume message
     *
     * @param array $options
     * @param int $timeout
     * @return array
     */
    public function receive($options = null, $timeout = null)
    {
        return $this->getAdapter()->receive($options, $timeout);
    }
}

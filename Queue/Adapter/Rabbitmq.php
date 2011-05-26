<?php
/**
 * Class for using a Rabbitmq as a queue
 *
 * @category Custom
 * @package Custom_Queue_Rabbitmq
 * @subpackage Adapter
 * @author Ritesh Jha
 * @copyright Copyright (c) (http://mailrkj(at)gmail(dot)com)
 */

class Custom_Queue_Adapter_Rabbitmq extends Zend_Queue_Adapter_AdapterAbstract
{
    /**
     * @var object AMQP connection object
     */
    protected $_cnn = array();

    /**
     *
     * @var object AMQP excahnge object
     */
    protected $_exchange = null;

    /**
     *
     * @var object AMQP queue object
     */
    protected $Queue = null;


    /**
     *
     * @var object AMQP queue object
     */
    protected $QueueFlag = AMQP_DURABLE;

    /**
     * Constructor
     *
     * @param array|Zend_Config $options
     * options (host,port,login,password)
     * @return AMQPConnection instance
     */
    public function __construct($options, Zend_Queue $queue = null)
    {
        parent::__construct($options, $queue);

        if (is_array($options)) {
            try
            {
                $cnn = new AMQPConnection($options);
                $cnn->connect();

                if (!$cnn->isConnected()) {
                    throw new Zend_Queue_Exception("Unable to connect RabbitMQ server");
                }
                else
                {
                    $this->_cnn = $cnn;
                    $this->Queue = new AMQPQueue($this->_cnn);
                }
            } catch (Exception $e) {
                throw new Zend_Queue_Exception($e->getMessage());
            }
        }
        else
        {
            throw new Zend_Queue_Exception("The options must be an associative array of host,port,login, password ...");
        }
    }

    /**
     * Get AMQPConnection object
     * @return object
     */
    public function getConnection()
    {
        return $this->_cnn;
    }


    /**
     * Set exchange for sending message to queue
     * @param string $name
     * @param string $type (AMQP_EX_TYPE_DIRECT, AMQP_EX_TYPE_FANOUT, AMQP_EX_TYPE_TOPIC or AMQP_EX_TYPE_HEADER)
     * @param int $flags (AMQP_PASSIVE, AMQP_DURABLE, AMQP_AUTODELETE)
     * @return boolean
     */
    public function setExchange($exchange, $routingKey = "*", $type = AMQP_EX_TYPE_DIRECT, $flags = AMQP_DURABLE)
    {
        if ($exchange instanceof Custom_Queue_Exchange) {
            $this->_exchange = $exchange;
        }
        else
        {
            $exchange = new Custom_Queue_Exchange($this->_cnn, $exchange, $type, $flags);
            $this->_exchange = $exchange;
        }
        $this->setRoutingKey($routingKey);

        return $exchange;
    }

    /**
     * Set routing key for queu
     * @param string $routing_key
     * @param Custom_Queue $queue
     * @return bool
     */
    public function setRoutingKey($routingKey, Custom_Queue $queue = null)
    {
        if ($queue)
            $queueName = $queue->getName();
        else
            $queueName = $this->_queue->getName();

        return $this->_exchange->bind($queueName, $routingKey);
    }

    /**
     * get AMQPQueue object
     * @return
     */
    public function setQueueFlag($flag)
    {
        return $this->QueueFlag = $flag;
    }

    /**
     * create queue
     * @param $name
     * @param $timeout
     */
    public function create($name, $timeout = null)
    {
        return $this->Queue->declare($name, $this->QueueFlag);
    }

    /**
     * delete queue
     * @param $name
     * @param $timeout
     */
    public function delete($name)
    {
        return $this->Queue->delete($name);
    }

    /**
     * Publish message to queue
     * @param mixed $message (array or string)
     * @param Custom_Queue $queue
     * @return boolean
     */
    public function send($message, Zend_Queue $queue = null)
    {
        if (is_array($message)) {
            $message = Zend_Json_Encoder::encode($message);
        }

        if ($queue)
            $routingKey = $queue->getOption('routingKey');
        else
            $routingKey = $this->_queue->getOption('routingKey');

        if ($this->_exchange) {
            return $this->_exchange->publish($message, $routingKey, AMQP_MANDATORY, array('delivery_mode' => 2));
        }
        else
        {
            throw new Zend_Queue_Exception("Rabbitmq exchange not found");
        }
    }

    /**
     *
     * @param array $options (min, max. ack)
     * @param int $timeout
     * @param Zend_Queue $queue
     * @return
     */
    public function receive($options = null, $timeout = null, Zend_Queue $queue = null)
    {
        $messages = $this->Queue->get();
        return $messages;
    }

    public function getCapabilities()
    {
        return array(
            'create' => true,
            'delete' => true,
            'send' => true,
        );
    }

    public function isExists($name)
    {
    }

    public function getQueues()
    {
    }

    public function count(Zend_Queue $queue = null)
    {
    }

    public function deleteMessage(Zend_Queue_Message $message)
    {
    }
}

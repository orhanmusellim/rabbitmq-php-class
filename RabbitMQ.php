<?php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Exchange\AMQPExchangeType;
class RabbitMQ {
    public $host;
    public $port;
    public $username;
    public $password;
    public $queue;
    public $queueArgs;
    public $exchange;
    public $exchangeType;
    public $exchangeArgs;
    public $routingKey;
    public $channel;
    public $connection;

    public function __construct($config) {
        $this->host = (isset($config['host'])) ? $config['host'] : 'localhost';
        $this->port = (isset($config['port'])) ? $config['port'] : '5672';
        $this->username = (isset($config['username'])) ? $config['username'] : 'guest';
        $this->password = (isset($config['password'])) ? $config['password'] : 'guest';
        $this->queue = (isset($config['queue'])) ? $config['queue'] : 'default';
        $this->queueArgs = (isset($config['queueArgs'])) ? $config['queueArgs'] : array();
        $this->exchange = (isset($config['exchange'])) ? $config['exchange'] : 'default';
        $this->exchangeType = (isset($config['exchangeType'])) ? $config['exchangeType'] : 'default';
        $this->routingKey = (isset($config['routingKey'])) ? $config['routingKey'] : '';
        $this->exchangeArgs = (isset($config['exchangeArgs'])) ? $config['exchangeArgs'] : array();
        $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password);
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->queue, false, true, false, false, false, new AMQPTable($this->queueArgs));
        $this->channel->exchange_declare($this->exchange, $this->exchangeType, false, true, false, false, false, new AMQPTable($this->exchangeArgs));
        $this->channel->queue_bind($this->queue, $this->exchange, $this->routingKey);
        function shutdown($channel, $connection) {
            $channel->close();
            $connection->close();
        }

        register_shutdown_function('shutdown', $this->channel, $this->connection);
    }

    public function __destruct() {
        $this->channel->close();
        $this->connection->close();
    }

    public function sendMessage($payload) {
        $msg = new AMQPMessage(json_encode($payload, JSON_UNESCAPED_SLASHES), array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $response = $this->channel->basic_publish($msg, $this->exchange);
        return $response;
    }

    public function sendDelayedMessage($payload, $delay) {
        $headers = new AMQPTable();
        $headers->set('x-delay', $delay, AMQPTable::T_INT_LONG);
        $msg = new AMQPMessage(json_encode($payload, JSON_UNESCAPED_SLASHES), array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $msg->set('application_headers', $headers);
        try {
            $this->channel->basic_publish($msg, $this->exchange, $this->routingKey, true);
            $this->channel->wait_for_pending_acks();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function getMessages() {
        $messages = array();
        while ($message = $this->channel->basic_get($this->queue)) {
            $messages[] = $message;
        }
        return $messages;
    }

    public function ack($delivery_tag) {
        $this->channel->basic_ack($delivery_tag);
    }
}
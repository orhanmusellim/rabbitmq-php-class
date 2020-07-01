<!-- PROJECT LOGO -->
<br />
<p align="center">

  <div align="center">
  <img src="assets/om-logo.png" alt="Logo" width="180" height="180">
  </div>

  <h2 align="center">PHP - RabbitMQ Class</h2>
     <br />
<div align="center">
  <img src="assets/rabbitmq-logo.png" alt="Logo" width="180">
  </div>
  <br />
  <p align="center">
    PHP Projelerinize dahil ederek kullanabileceğiniz RabbitMQ Entegrasyonu için hazırlanmış basit bir Class'dır.
    <br />
    
  </p>

</p>


## Kurulum

1. Repoyu Klonlayın

2. Composer üzerindeki bağımlılıkları kurun.

```sh
composer install
```

4. Kullanmaya başlayın.

## Kullanım

1. Sınıfı sayfanıza dahil edin.

2. RabbitMQ sunucunuza kurulu ve çalışır halde olduğundan emin olun.

3. RabbitMQ üzerine yeni bir mesaj göndermek için aşağıdaki kod bloğunu kullanabilirsiniz. Mesaj gönderimi için gerekli olan queue ve exchange'i classı türetirken parametreler ile oluşturacaktır. 

```sh
$rabbitMqConfig = array(
    "queue" => "Normal-Mesajlar",
    "exchange" => "normal",
    "exchangeType" => "direct",
);
$rabbitMq = new rabbitmq(rabbitMqConfig);
$response = $rabbitMq->sendMessage(array(
    'Mesaj' => 'Bu Bir RabbitMQ Mesajıdır.'
));
print_r($response);
```

4. RabbitMQ ile istediğiniz bir queue üzerindeki mesajları çekmek için aşağıdaki kod bloğunu kullanabilirsiniz. Ayrıca `ack` fonksiyonu ile işlem yaptığınız mesajı kuyruktan kaldırabilirsiniz.
```sh
$rabbitMqConfig = array(
"queue" => "Normal-Mesajlar",
"exchange" => "normal",
"exchangeType" => "direct"
);
$rabbitMq = new rabbitmq(rabbitMqConfig);
$response = $rabbitMq->getMessages();
foreach ($response as $message) {
    preprint(json_decode($message->body));
    $rabbitMq->ack($message->delivery_info['delivery_tag']);
}
```

## Zamanlayarak Kuyruğa Ekleme Yapmak !

RabbitMQ kuyruğa ekleme yaparken işlem yaptığınız an kuyruğa ekleme işlemi yapacaktır. İstediğiniz bir zaman dilimini belirterek kuyruğa ekleme işlemini gecikmeli olarak yaptırabilirsiniz. Öncelikle bu işlemi yapabilmek için “RabbitMQ Delayed Message” eklentisini sunucunuza kurarak RabbitMQ için aktif etmeniz gerekir. http://www.rabbitmq.com/community-plugins.html adresinden eklenti ile ilgili işlemleri yapabilirsiniz.

1. Zamanlanmış Mesaj Ekleme için aşağıdaki kod bloğunu kullanabilirsiniz.
```sh
$rabbitMqConfig = array(
    "queue" => "queue-name",
    "queueArgs" => array("x-queue-type" => "classic"),
    "exchange" => "queue-exchange",
    "exchangeType" => "x-delayed-message",
    "exchangeArgs" => array("x-delayed-type" => "topic"),
    "routingKey" => "queue-routing-key",
);
$rabbitMq = new rabbitmq(rabbitMqConfig);
$response = $rabbitMq->sendDelayedMessage(array(
    'Mesaj' => 'Bu Bir Beklemeli RabbitMQ Mesajıdır.!'
), 60000); #Milisaniye cinsinden 
var_export($response);
```

2. Zamanlanarak gönderilen mesajları okumak için aşağıdaki kod bloğunu kullanabilirsiniz.
```sh
$rabbitMqConfig = array(
    "queue" => "queue-name",
    "exchange" => "queue-exchange",
    "exchangeType" => "x-delayed-message",
    "routingKey" => "queue-routing-key",
);
$rabbitMq = new rabbitmq(rabbitMqConfig);
$response = $rabbitMq->getMessages();
foreach ($response as $message) {
    preprint(json_decode($message->body));
    $this->rabbitmq->ack($message->delivery_info['delivery_tag']);
}
```

***
[![Issues - Bug](https://badge.waffle.io/usabilitydynamics/wp-repository.png?label=bug&title=Bugs)](http://waffle.io/usabilitydynamics/wp-repository)
[![Issues - Backlog](https://badge.waffle.io/usabilitydynamics/wp-repository.png?label=backlog&title=Backlog)](http://waffle.io/usabilitydynamics/wp-repository/)
[![Issues - Active](https://badge.waffle.io/usabilitydynamics/wp-repository.png?label=in progress&title=Active)](http://waffle.io/usabilitydynamics/wp-repository/)
***
[![Dependency Status](https://gemnasium.com/usabilitydynamics/wp-repository.svg)](https://gemnasium.com/usabilitydynamics/wp-repository)
[![Scrutinizer Quality](http://img.shields.io/scrutinizer/g/usabilitydynamics/wp-repository.svg)](https://scrutinizer-ci.com/g/usabilitydynamics/wp-repository)
[![Scrutinizer Coverage](http://img.shields.io/scrutinizer/coverage/g/usabilitydynamics/wp-repository.svg)](https://scrutinizer-ci.com/g/usabilitydynamics/wp-repository)
[![Packagist Vesion](http://img.shields.io/packagist/v/usabilitydynamics/wp-repository.svg)](https://packagist.org/packages/usabilitydynamics/wp-repository)
[![CircleCI](https://circleci.com/gh/usabilitydynamics/wp-repository.png)](https://circleci.com/gh/usabilitydynamics/wp-repository)
***

### URLs

* /api/repository/v1/packages.json

### Filters

* wpr::includes_url
* wpr::single_release
* wpr::single_package
* wpr::main_package
* wpr::installer_name


### AMQP WebHook Publishing Example
```php
connection = new \AMQP\Connection( 'amqp://user:pass@hostname:port/vhost',  array(
  'insist' => false,
  'login_method' => \AMQP\Connection::AMQP_AUTH_PLAIN,
  'login_response' => null,
  'locale' => 'en_US',
  'connection_timeout' => 3,
  'read_write_timeout' => 3,
  'context' => null,
  'ssl_options' => array()
));

$channel = $connection->channel();

$message = new Message( 'blah blah', array(
  'content_type' => 'application/json', 
  'delivery_mode' => 2
));

$channel->basicPublish( $message, 'DiscoDonniePresents/www.discodonniepresents.com');

$channel->close();
$connection->close();
```
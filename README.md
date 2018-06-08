# Analyzer plugin for CakePHP 3.X

This Analyzer Plugin is a plugin that tracks and reports website traffic.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require jeffersonsimaogoncalves/cakephp-analyzer
```

Now load the plugin via your shell:
```
$ bin/cake plugin load -b -r Analyzer

```
Or put the following in your `config/bootstrap.php`:
```
Plugin::load('Analyzer', ['bootstrap' => true]);

```

Run the migrations:
```
$ bin/cake migrations migrate -p Analyzer
```

From now on all requests will be reported!

## Usage

### Ignore
You can ignore to register request by putting the following code in your `config/bootstrap.php`:
```
Configure::write('Analyzer.Ignore.key', [
    'plugin' => 'DebugKit'
    'controller' => 'UsersController',
    'action' => 'index',
    'prefix' => 'admin',
]);
```
So, for example; this code will ignore all `DebugKit` requests:
```
Configure::write('Analyzer.Ignore.debug_kit', [
    'plugin' => 'DebugKit'
]);
```

### Finders
The `RequestsTable` has the following finders:

#### Between
Set start- and end-date:
```
    $query->find('Between', [
        'start' => '-3 days',
        'end' => 'now',
    ]);
```

#### UniqueVisitors
> Not valid yet!

Find only unique visitors:
```
    $query->find('UniqueVisitors');
```

## Credits

This work is based on the [code by CakeManager](https://github.com/cakemanager/cakephp-analyzer).

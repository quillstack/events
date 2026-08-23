# Quillstack Events

[![Tests](https://github.com/quillstack/events/actions/workflows/tests.yml/badge.svg)](https://github.com/quillstack/events/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/quillstack/events.svg)](https://packagist.org/packages/quillstack/events)
[![Downloads](https://img.shields.io/packagist/dt/quillstack/events.svg)](https://packagist.org/packages/quillstack/events)
[![PHP Version](https://img.shields.io/packagist/php-v/quillstack/events)](https://packagist.org/packages/quillstack/events)
[![StyleCI](https://github.styleci.io/repos/1342146261/shield?branch=main)](https://github.styleci.io/repos/1342146261?branch=main)
[![CodeFactor](https://www.codefactor.io/repository/github/quillstack/events/badge)](https://www.codefactor.io/repository/github/quillstack/events)
[![Quality Gate](https://sonarcloud.io/api/project_badges/measure?project=quillstack_events&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=quillstack_events)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=quillstack_events&metric=coverage)](https://sonarcloud.io/summary/new_code?id=quillstack_events)
[![Maintainability](https://sonarcloud.io/api/project_badges/measure?project=quillstack_events&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_events)
[![Reliability](https://sonarcloud.io/api/project_badges/measure?project=quillstack_events&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_events)
[![Security](https://sonarcloud.io/api/project_badges/measure?project=quillstack_events&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_events)
[![License](https://img.shields.io/packagist/l/quillstack/events)](https://github.com/quillstack/events/blob/main/LICENSE)

An event dispatcher based on PSR-14: Event Dispatcher.

## Installation

```shell
composer require quillstack/events
```

## Usage

An event is any object. A listener is anything callable which takes it:

```php
use Quillstack\Events\EventDispatcher;
use Quillstack\Events\ListenerProvider;

$listeners = new ListenerProvider();
$listeners->listen(UserRegistered::class, function (UserRegistered $event) {
    $this->mailer->welcome($event->email);
});

$dispatcher = new EventDispatcher($listeners);
$dispatcher->dispatch(new UserRegistered('radek@quillstack.com'));
```

`dispatch()` gives the event back, so a listener can answer through it.

## Order

Listeners with a higher priority are called first, and two of the same priority are called
in the order they were registered:

```php
$listeners->listen(UserRegistered::class, $writeToTheLog, 10);
$listeners->listen(UserRegistered::class, $sendTheEmail);
$listeners->listen(UserRegistered::class, $cleanUpAfterwards, -10);
```

## Listening for a family of events

A listener registered for a class is called for that class and for anything extending or
implementing it, so listening for an interface catches every event carrying it:

```php
$listeners->listen(HasUser::class, $recordWhoDidIt);
```

## Calling an end to an event

An event extending `StoppableEvent` can be stopped by a listener, and the ones after it are
not called:

```php
final class OrderPlaced extends StoppableEvent
{
}

$listeners->listen(OrderPlaced::class, function (OrderPlaced $event) {
    if ($this->alreadyHandled($event)) {
        $event->stopPropagation();
    }
});
```

## Tests

```shell
composer test
```

Coverage needs phpdbg:

```shell
composer test:coverage
```

## License

MIT. See [LICENSE](LICENSE).

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

## Why this exists

PSR-14 splits dispatching in two: something that decides which listeners an event has, and
something that calls them. Most implementations hand you one object doing both, which is
convenient until you want to know why a listener did or did not run.

Here they are the two classes the standard describes — a provider you register with, a
dispatcher that calls what it is given — and a listener is any callable. There is no subscriber
interface to implement, no compiler pass, and no event name to keep in step with a class name:
**an event is its class**, and listening for a parent class or an interface catches everything
that is one.

## Requirements

- PHP 8.1 or newer

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

### Order

Listeners with a higher priority are called first, and two of the same priority are called
in the order they were registered:

```php
$listeners->listen(UserRegistered::class, $writeToTheLog, 10);
$listeners->listen(UserRegistered::class, $sendTheEmail);
$listeners->listen(UserRegistered::class, $cleanUpAfterwards, -10);
```

### Listening for a family of events

A listener registered for a class is called for that class and for anything extending or
implementing it, so listening for an interface catches every event carrying it:

```php
$listeners->listen(HasUser::class, $recordWhoDidIt);
```

### Calling an end to an event

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

## Benchmark

Measured with [quillstack/benchmark](https://github.com/quillstack/benchmark) on a thousand
events, each passing through five listeners. All three call the same five in the same order.
Runs are interleaved and unconcurrent, each figure is the median of five, and PHP is 8.5.7.

| | Version |
| --- | --- |
| quillstack/events | 0.6.0 |
| symfony/event-dispatcher | v7.4.17 |
| league/event | 3.0.3 |

| | Per event | Relative |
| --- | --- | --- |
| symfony/event-dispatcher | 0.34 µs | 0.39× |
| league/event | 0.47 µs | 0.53× |
| **quillstack/events** | **0.88 µs** | — |

**This one is the slowest of the three**, and the reason is a decision rather than an oversight:
the provider works out which listeners match an event every time it is asked, by testing the
event against each registered class. That is what makes listening for a parent class or an
interface work without anybody registering the subclass. Symfony looks the event name up in an
array and sorts once.

Half a microsecond an event is not a thing to choose on. A request dispatching a hundred events
spends fifty microseconds more here than on Symfony's, and if that is the shape of your
application, Symfony's dispatcher is excellent and this is not trying to replace it.

## Tests

```shell
composer test
```

Coverage needs phpdbg:

```shell
composer test:coverage
```

## The rest of Quillstack

This is one component of [Quillstack](https://github.com/quillstack), a PHP framework which is
as simple to use as it is strict about what it does.

- [quillstack/framework](https://github.com/quillstack/framework) — where listeners are registered
- [quillstack/queue](https://github.com/quillstack/queue) — for work that should not happen now
- [quillstack/di](https://github.com/quillstack/di) — what builds a listener that is a class

## License

MIT. See [LICENSE](LICENSE).

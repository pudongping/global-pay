<?php
/**
 * document link: http://www.symfonychina.com/doc/current/components/event_dispatcher.html#catalog11
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 21:36
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Listeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pudongping\GlobalPay\Events;
use Pudongping\GlobalPay\Log;

class KernelLogSubscriber implements EventSubscriberInterface
{

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * The code must not depend on runtime state as it will only be called at compile time.
     * All logic depending on runtime state must be put into the individual methods handling the events.
     *
     * @return array<string, mixed> The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            Events\PayStartingEvent::class => ['writePayStartingLog', 256],
            Events\PayStartedEvent::class => ['writePayStartedLog', 256],
            Events\ApiRequestingEvent::class => ['writeApiRequestingLog', 256],
            Events\ApiRequestedEvent::class => ['writeApiRequestedLog', 256],
            Events\SignFailedEvent::class => ['writeSignFailedLog', 256],
            Events\RequestReceivedEvent::class => ['writeRequestReceivedLog', 256],
            Events\MethodCalledEvent::class => ['writeMethodCalledLog', 256],
        ];
    }

    /**
     * @param Events\PayStartingEvent $event
     */
    public function writePayStartingLog(Events\PayStartingEvent $event)
    {
        Log::debug("Starting To {$event->driver}", [$event->gateway, $event->params]);
    }

    /**
     * @param Events\PayStartedEvent $event
     */
    public function writePayStartedLog(Events\PayStartedEvent $event)
    {
        Log::info("{$event->driver} {$event->gateway} Has Started", [$event->endpoint, $event->payload]);
    }

    /**
     * @param Events\ApiRequestingEvent $event
     */
    public function writeApiRequestingLog(Events\ApiRequestingEvent $event)
    {
        Log::debug("Requesting To {$event->driver} Api", [$event->endpoint, $event->payload]);
    }

    /**
     * @param Events\ApiRequestedEvent $event
     */
    public function writeApiRequestedLog(Events\ApiRequestedEvent $event)
    {
        Log::debug("Result Of {$event->driver} Api", $event->result);
    }

    /**
     * @param Events\SignFailedEvent $event
     */
    public function writeSignFailedLog(Events\SignFailedEvent $event)
    {
        Log::warning("{$event->driver} Sign Verify FAILED", $event->data);
    }

    /**
     * @param Events\RequestReceivedEvent $event
     */
    public function writeRequestReceivedLog(Events\RequestReceivedEvent $event)
    {
        Log::info("Received {$event->driver} Request", $event->data);
    }

    /**
     * @param Events\MethodCalledEvent $event
     */
    public function writeMethodCalledLog(Events\MethodCalledEvent $event)
    {
        Log::info("{$event->driver} {$event->gateway} Method Has Called", [$event->endpoint, $event->payload]);
    }

}
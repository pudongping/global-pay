<?php
/**
 * document link: https://symfony.com/doc/current/components/event_dispatcher.html
 * zh-cn link: http://www.symfonychina.com/doc/current/components/event_dispatcher.html
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 20:54
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @method static Event dispatch(Event $event)                                Dispatches an event to all registered listeners
 * @method static array getListeners($eventName = null)                       Gets the listeners of a specific event or all listeners sorted by descending priority.
 * @method static int|void getListenerPriority($eventName, $listener)         Gets the listener priority for a specific event.
 * @method static bool hasListeners($eventName = null)                        Checks whether an event has any registered listeners.
 * @method static void addListener($eventName, $listener, $priority = 0)      Adds an event listener that listens on the specified events.
 * @method static removeListener($eventName, $listener)                       Removes an event listener from the specified events.
 * @method static void addSubscriber(EventSubscriberInterface $subscriber)    Adds an event subscriber.
 * @method static void removeSubscriber(EventSubscriberInterface $subscriber)
 */
class Events
{

    /**
     * dispatcher.
     *
     * @var EventDispatcher
     */
    protected static $dispatcher;

    /**
     * Forward call
     *
     * @param $method
     * @param $args
     * @return false|mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array([self::getDispatcher(), $method], $args);
    }

    /**
     * Forward call
     *
     * @param $method
     * @param $args
     * @return false|mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([self::getDispatcher(), $method], $args);
    }

    /**
     * setDispatcher
     *
     * @param EventDispatcher $dispatcher
     */
    public static function setDispatcher(EventDispatcher $dispatcher)
    {
        self::$dispatcher = $dispatcher;
    }

    /**
     * getDispatcher
     *
     * @return EventDispatcher
     */
    public static function getDispatcher(): EventDispatcher
    {
        if (self::$dispatcher) {
            return self::$dispatcher;
        }

        return self::$dispatcher = self::createDispatcher();
    }

    /**
     * createDispatcher
     *
     * @return EventDispatcher
     */
    public static function createDispatcher(): EventDispatcher
    {
        return new EventDispatcher();
    }

}
<?php


namespace ChengYi\facade;


use ChengYi\util\LeakyBucket;
use think\Container;
use think\Facade;
use think\facade\Config;

class RateLimit extends Facade
{
    protected static function getFacadeClass(): string {
        return LeakyBucket::class;
    }

    /**
     * 创建Facade实例
     * @static
     * @access protected
     * @param string $class 类名或标识
     * @param array $args 变量
     * @param bool $newInstance 是否每次创建新的实例
     * @return object
     */
    protected static function createFacade(string $class = '', array $args = [], bool $newInstance = false) {
        $class = static::getFacadeClass();
        $args = [
            Config::get('rete_limit')
        ];
        $newInstance = false;
        if (static::$alwaysNewInstance) {
            $newInstance = true;
        }
        return Container::getInstance()->make($class, $args, $newInstance);
    }
}

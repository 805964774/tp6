<?php


namespace ChengYi\facade;

use think\Container;
use think\Facade;
use think\facade\Env;

/**
 * Class SnowFlake
 * @package app\common\facade
 * @method static nextId() 获取新的id
 * @method static getCurrentId() 获取当前的id
 * @method static setCurrentId(string $id) 设置当前的id
 */
class SnowFlake extends Facade
{
    protected static $alwaysNewInstance = false;

    protected static function getFacadeClass(): string {
        return \ChengYi\util\SnowFlake::class;
    }

    /**
     * 创建Facade实例
     * @static
     * @access protected
     * @param string $class
     * @param array $args
     * @return object
     */
    protected static function createFacade(string $class = '', array $args = [], bool $newInstance = false) {
        $class = static::getFacadeClass();
        $args = [
            Env::get('work_id', 1),
        ];
        $newInstance = false;
        if (static::$alwaysNewInstance) {
            $newInstance = true;
        }
        return Container::getInstance()->make($class, $args, $newInstance);
    }
}

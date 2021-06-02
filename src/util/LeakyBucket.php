<?php


namespace ChengYi\util;


use ChengYi\constant\ErrorNums;
use ChengYi\exception\ChengYiException;
use ChengYi\exception\RateLimitException;
use think\facade\Cache;
use think\facade\Config;

/**
 * 令牌桶限流
 * Class LeakyBucket
 * @package ChengYi\util
 */
class LeakyBucket
{

    /**
     * @var array
     */
    private static $_instances;

    /**
     * @var mixed 令牌桶的容量
     */
    private $capacity;

    /**
     * @var mixed 添加token的速率，单位是s
     */
    private $incRate;

    /**
     * @var mixed 单次获取token的数量
     */
    private $decNum;

    /**
     * @var mixed 缓存失效时间
     */
    private $cacheExpire;

    private function __construct($conf) {
        $this->capacity = $conf['capacity'];
        $this->incRate = $conf['inc_rate'];
        $this->decNum = $conf['dec_num'];
        $this->cacheExpire = $conf['cache_expire'];
    }

    public function rateLimit($key): bool {
        $curTime = time();
        $oldData = Cache::get($key);
        // 缓存没有上次的token_num就默认是桶的容量
        $lastTokenNum = $oldData['token_num'] ?? $this->capacity;
        // 缓存没有上次的时间，就是当前时间
        $lastTime = $oldData['last_time'] ?? $curTime;
        // 获取时间间隔
        $interval = $curTime - $lastTime;
        // 计算添加token的数量
        $incTokenNum = $interval * $this->incRate;
        // 添加完token后，获取最终的值，但是不能大于桶容量
        $tokenNum = min($lastTokenNum + $incTokenNum, $this->capacity);
        // 计算获取token的数量
        // 如果token数量小于获取的token，则不放行
        if ($tokenNum < $this->decNum || $tokenNum == 0) {
            // 没有token了
            throw new RateLimitException('too many request', ErrorNums::TOO_MANY_REQUEST);
        }
        // 计算放行之后的token
        $tokenNum -= $this->decNum;
        // 将当前的数据存入缓存，供下次使用
        $data['token_num'] = $tokenNum;
        $data['last_time'] = $curTime;
        Cache::set($key, $data, $this->getCacheExpire());
        return true;
    }

    /**
     * 防止缓存雪崩，修改缓存失效时间为非固定值
     * @return int
     */
    private function getCacheExpire(): int {
        $randomMax = 60;
        return mt_rand(10, $randomMax) + $this->cacheExpire;
    }

    private function __clone() {
    }

    /**
     * 获取实例
     * @param string $scene
     * @param array $config
     * @return \ChengYi\util\LeakyBucket
     * @throws \ChengYi\exception\ChengYiException
     */
    public static function getInstance(string $scene = 'default', array $config = []): LeakyBucket {
        if (!(self::$_instances[$scene] instanceof LeakyBucket)) {
            if ('default' != $scene && empty($config)) {
                throw new ChengYiException('非默认场景，需要配置信息');
            }
            if (!empty($config)) {
                $config = Config::get('rete_limit');
            }
            self::$_instances[$scene] = new self($config);
        }
        return self::$_instances[$scene];
    }
}

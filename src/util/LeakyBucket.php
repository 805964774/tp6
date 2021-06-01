<?php


namespace ChengYi\util;


use think\facade\Cache;

/**
 * 令牌桶限流
 * Class LeakyBucket
 * @package ChengYi\util
 */
class LeakyBucket
{
    /**
     * @var mixed 令牌桶的容量
     */
    private $capacity;

    /**
     * @var mixed 添加token的速率，单位是s
     */
    private $incRate;

    /**
     * @var mixed 获取token的速率，单位是s
     */
    private $decRate;

    /**
     * @var mixed 缓存失效时间
     */
    private $cacheExpire;

    public function __construct($conf) {
        $this->capacity = $conf['capacity'];
        $this->incRate = $conf['inc_rate'];
        $this->decRate = $conf['dec_rate'];
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
        $decTokenNum = $interval * $this->decRate;
        // 如果token数量小于获取的token，则不放行
        if ($tokenNum < $decTokenNum) {
            return false;
        }
        // 计算放行之后的token
        $tokenNum -= $decTokenNum;
        // 将当前的数据存入缓存，供下次使用
        $data['token_num'] = $tokenNum;
        $data['last_time'] = $curTime;
        Cache::set($key, $data, $this->getCacheExpire());
        return true;
    }

    /**
     * 重置配置，应对一些特殊的限流
     * return $this是为了方便链式调用
     * @param $conf
     * @return $this
     */
    public function resetConf($conf): LeakyBucket {
        $this->capacity = $conf['capacity'];
        $this->incRate = $conf['inc_rate'];
        $this->decRate = $conf['dec_rate'];
        $this->cacheExpire = $conf['cache_expire'];
        return $this;
    }

    /**
     * 防止缓存雪崩，修改缓存失效时间为非固定值
     * @return int
     */
    private function getCacheExpire(): int {
        $randomMax = 60;
        return mt_rand(10, $randomMax) + $this->cacheExpire;
    }
}

<?php


namespace ChengYi\util;


use think\facade\Log;

class SnowFlake
{
    const TWEPOCH = 1609430400000; // 时间起始标记点，作为基准，一般取系统的最近时间（一旦确定不能变动）

    //机器标识占的位数
    const WORKERID_BITS = 10;

    //毫秒内自增数点的位数
    const SEQUENCE_BITS = 12;

    protected $workId = 0;

    //要用静态变量
    static $lastTimestamp = -1;
    static $sequence = 0;

    protected $currentId = 0;


    function __construct($workId) {
        //机器ID范围判断
        $maxWorkerId = -1 ^ (-1 << self::WORKERID_BITS);
        if ($workId > $maxWorkerId || $workId < 0) {
            Log::warning("workerId can't be greater than " . $maxWorkerId . " or less than 0");
        }
        //赋值
        $this->workId = $workId;
    }

    //生成一个ID
    public function nextId() {
        $timestamp = $this->timeGen();
        $lastTimestamp = self::$lastTimestamp;
        //判断时钟是否正常
        if ($timestamp < $lastTimestamp) {
            Log::warning("Clock moved backwards.  Refusing to generate id for " . ($lastTimestamp - $timestamp) . " milliseconds", );
        }
        //生成唯一序列
        if ($lastTimestamp == $timestamp) {
            $sequenceMask = -1 ^ (-1 << self::SEQUENCE_BITS);
            self::$sequence = (self::$sequence + 1) & $sequenceMask;
            if (self::$sequence == 0) {
                $timestamp = $this->tilNextMillis($lastTimestamp);
            }
        } else {
            self::$sequence = 0;
        }
        self::$lastTimestamp = $timestamp;
        //
        //时间毫秒/数据中心ID/机器ID,要左移的位数
        $timestampLeftShift = self::SEQUENCE_BITS + self::WORKERID_BITS;
        $workerIdShift = self::SEQUENCE_BITS;
        //组合3段数据返回: 时间戳.工作机器.序列
        $nextId = (($timestamp - self::TWEPOCH) << $timestampLeftShift) | ($this->workId << $workerIdShift) | self::$sequence;
        $traceId = date('YmdHis') .'_'. $nextId;
        $this->currentId = $traceId;
        return $traceId;
    }

    //取当前时间毫秒
    protected function timeGen() {
        $timestramp = (float)sprintf("%.0f", microtime(true) * 1000);
        return $timestramp;
    }

    //取下一毫秒
    protected function tilNextMillis($lastTimestamp) {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }
        return $timestamp;
    }

    public function getCurrentId() {
        return $this->currentId;
    }

    public function setCurrentId($currentId) {
        $this->currentId = $currentId;
    }
}

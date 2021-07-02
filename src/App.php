<?php
declare(strict_types=1);

namespace ChengYi;

use ChengYi\constant\ErrorNums;

/**
 * 框架不支持场景配置，继承重写getConfigPath
 * 依赖config_scene环境变量，读取对应场景的配置，方便开发
 * Class App
 * @package ChengYi
 */
class App extends \think\App
{
    /**
     * 获取应用配置目录
     * @access public
     * @return string
     */
    public function getConfigPath(): string {
        $scene = $this->env->get('config_scene', 'prod');
        $configPath = $this->rootPath . 'config' . DIRECTORY_SEPARATOR . $scene . DIRECTORY_SEPARATOR;
        if (is_dir($configPath)) {
            return $configPath;
        }
        header("Content-type:application/json");
        die(json_encode(['code' => ErrorNums::DIRECTORY_NOT_EXISTS, 'msg' => '[' . $scene . '] scene config directory not exist!']));
    }
}

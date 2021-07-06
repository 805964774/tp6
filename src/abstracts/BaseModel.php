<?php
declare(strict_types=1);

namespace ChengYi\abstracts;


use ChengYi\constant\ErrorNums;
use ChengYi\exception\ModelException;
use think\Collection;
use think\db\exception\DbException;
use think\Model;
use think\Paginator;

abstract class BaseModel extends Model
{
    /**
     * @var array 获取列表字段
     */
    protected $getListField;
    /**
     * @var array 添加被允许的字段
     */
    protected $addAllowField;
    /**
     * @var array 获取单条数据的字段
     */
    protected $getDataField;
    /**
     * @var array 编辑被允许的字段
     */
    protected $editAllowField;
    /**
     * @var array 翻页配置
     */
    private $pageConf = [
        'query'     => [], //url额外参数
        'fragment'  => '', //url锚点
        'var_page'  => 'current_page', //分页变量
        'list_rows' => 15, //每页数量
    ];

    /**
     * 获取列表
     * @param int $pageNum
     * @param array $where
     * @param array $order
     * @return Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList(int $pageNum = 10, array $where = [], array $order = []): Paginator {
        $this->pageConf['list_rows'] = $pageNum;
        return $this->field($this->getListField)->where($where)->order($order)->paginate($this->pageConf);
    }

    /**
     * 设置翻页配置
     * @param array $conf 翻页配置
     */
    public function setPageConf(array $conf) {
        $this->pageConf = $conf;
    }

    /**
     * 不分页查询所有
     * @param array $where 查询条件
     * @param array $field 查询的字段
     * @param array $order 排序
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAll(array $where, array $field = [], array $order = []): Collection {
        if (empty($field)) {
            $field = $this->getListField;
        }
        return $this->field($field)->where($where)->order($order)->select();
    }

    /**
     * 添加单条数据
     * @param $inputData
     * @param array $allowField
     * @return int
     * @throws \ChengYi\exception\ModelException
     */
    public function addOneData($inputData, array $allowField = []): int {
        if (empty($allowField)) {
            $allowField = $this->addAllowField;
        }
        foreach ($inputData as $key => $value) {
            if (!in_array($key, $allowField)) {
                unset($inputData[$key]);
            }
        }
        $res = $this->allowField($allowField)->create($inputData);
        if (!$res) {
            throw new ModelException(ErrorNums::ADD_FAIL);
        }
        $pk = $this->getPk();
        return $res->$pk;
    }

    /**
     * 获取单条数据by 主键
     * @param int $id 查询的主键
     * @param array $field 查询字段
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read(int $id, array $field = []) {
        $where[$this->getPk()] = $id;
        return $this->getOneData($where, $field);
    }

    /**
     * 获取单条数据
     * @param array $where 查询条件
     * @param array $field 查询字段
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOneData(array $where, array $field = []) {
        $field = empty($field) ? $this->getDataField : $field;
        return $this->field($field)->where($where)->find();
    }

    /**
     * 修改数据
     * @param $where
     * @param $inputData
     * @param array $allowField
     * @return \ChengYi\abstracts\BaseModel
     */
    public function modifyOneData($where, $inputData, array $allowField = []) {
        if (empty($allowField)) {
            $allowField = $this->editAllowField;
        }
        foreach ($inputData as $key => $value) {
            if (!in_array($key, $allowField)) {
                unset($inputData[$key]);
            }
        }
        return $this->allowField($allowField)->where($where)->update($inputData);
    }

    /**
     * 软删除
     * @param $id
     * @return bool
     */
    public function deleteOne($id): bool {
        $this->destroy($id);
        return true;
    }

    /**
     * 批量删除
     * @param array $idArr
     * @return bool
     */
    public function batchDelete(array $idArr): bool {
        $this->destroy($idArr);
        return true;
    }
}

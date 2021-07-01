<?php


namespace ChengYi\abstracts;


use ChengYi\constant\ErrorNums;
use ChengYi\exception\ModelException;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;
use think\Paginator;

abstract class BaseModel extends Model
{
    protected $getListField;
    protected $addAllowField;
    protected $getDataField;
    protected $editAllowField;

    /**
     * 获取列表
     * @param int $pageNum
     * @param array $where
     * @param array $order
     * @return Paginator
     * @throws DbException
     */
    public function getList(int $pageNum = 10, array $where = [], array $order = []): Paginator {
        return $this->field($this->getListField)->where($where)->order($order)->paginate($pageNum);
    }

    /**
     * 获取所有
     * @param $where
     * @param array $field
     * @return Collection
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAll($where, array $field = []): Collection {
        if (empty($field)) {
            $field = $this->getListField;
        }
        return $this->field($field)->where($where)->select();
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
     * 获取单条数据by id
     * @param $id
     * @param array $field
     * @return array|Model
     */
    public function read($id, array $field = []) {
        $where['id'] = $id;
        $field = empty($field) ? $this->getDataField : $field;
        return $this->field($field)->where($where)->findOrFail();
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

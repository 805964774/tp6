<?php


namespace ChengYi\util;

use ChengYi\constant\ErrorNums;
use ChengYi\exception\FileImportException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * 导入
 * Class Import
 * @package app\common\util
 */
class FileImport
{
    /**
     * excel
     * @param string $fileName 文件名
     * @param array $headFiles 表头映射
     * @return array
     * @throws Exception|\ChengYi\exception\FileImportException
     */
    public function excel(string $fileName, array $headFiles): array {
        $reader = IOFactory::createReader('Xlsx');
        // 设置读取的表名称
        $spreadsheet = $reader->load($fileName);
        $excelData = $spreadsheet->getActiveSheet()->toArray();
        $headData = array_shift($excelData);// 弹出表头数据
        $arrayIntersect = array_intersect($headData, array_keys($headFiles));
        if ($headData != $arrayIntersect) {
            throw new FileImportException('表头数据缺失,[' . implode(',', array_diff($arrayIntersect, array_keys($headFiles))) . ']', ErrorNums::PARAM_ILLEGAL);
        }
        $dataArr = [];
        foreach ($excelData as $k => $subData) {
            foreach ($subData as $ke => $value) {
                if (!array_key_exists($ke, $headData)) {
                    continue;
                }
                if (!isset($headFiles[$headData[$ke]])) {
                    continue;
                }
                $fieldName = $headFiles[$headData[$ke]];
                $dataArr[$k][$fieldName] = $value ?? '';
            }
        }
        return $dataArr;
    }

    /**
     * 大文件获取方式，但是由于都放在数组中返回，所以还是不支持大文件读取
     * @param string $filePath
     * @param string $separator
     * @param int $limit
     * @param array $headFiles
     * @return array
     * @throws \ChengYi\exception\FileImportException
     */
    public function txt(string $filePath, string $separator, int $limit, array $headFiles): array {
        $fileHandle = $this->readFile($filePath);
        $data = [];
        $dataArr = [];
        while ($fileHandle->valid()) {
            // 当前行文本
            $line = $fileHandle->current();
            // 逐行处理数据
            $tmp = explode($separator, $line, $limit);
            if (count($tmp) != $limit) {
                throw new FileImportException('数据不合法', ErrorNums::PARAM_ILLEGAL);
            }
            $filterData = [];
            $data[] = $tmp;
            foreach ($tmp as $ke => $value) {
                if (!isset($data[0])) {
                    continue;
                }
                if (!array_key_exists($ke, $data[0])) {
                    continue;
                }
                if (!isset($headFiles[$data[0][$ke]])) {
                    continue;
                }
                $fieldName = $headFiles[$data[0][$ke]];
                $filterData[$fieldName] = $value;
            }
            $dataArr[] = $filterData;
            // 指向下一行
            $fileHandle->next();
        }
        return array_values(array_filter($dataArr));
    }

    private function readFile($filePath): \Generator {
        if ($handle = fopen($filePath, 'r')) {
            while (!feof($handle)) {
                yield trim(fgets($handle));
            }
            fclose($handle);
        }
    }
}

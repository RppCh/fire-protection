<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/18 0018
 * Time: 14:11
 */
/**

 * excel表格导出

 * @param string $fileName 文件名称

 * @param array $headArr 表头名称

 * @param array $data 要导出的数据

 * @author static7  */

function excelExport($fileName = '', $headArr = [], $data = []) {

    $fileName .= "_" . date("Y_m_d", Request::instance()->time()) . ".xls";

    $objPHPExcel = new \PHPExcel();

    $objPHPExcel->getProperties();

    $key = ord("A"); // 设置表头

    foreach ($headArr as $v) {

        $colum = chr($key);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

        $key += 1;

    }

    $column = 2;

    $objActSheet = $objPHPExcel->getActiveSheet();

    foreach ($data as $key => $rows) { // 行写

        $span = ord("A")

        foreach ($rows as $keyName => $value) { // 列写入

            $objActSheet->setCellValue(chr($span) . $column, $value);

            $span++;

        }

        $column++;

    }

    $fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表

    $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表

    header('Content-Type: application/vnd.ms-excel');

    header("Content-Disposition: attachment;filename='$fileName'");

    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $objWriter->save('php://output'); // 文件通过浏览器下载

    exit();

}
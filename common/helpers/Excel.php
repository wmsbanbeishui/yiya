<?php

namespace common\helpers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class Excel
{
    /**
     * 导出
     * @param $data
     * @param $title
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    static function exportExcel($data, $title, $file_name)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        //设置工作表标题名称
        $worksheet->setTitle('工作表格1');

        //表头 设置单元格内容
        foreach ($title as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($key + 1, 1, $value);
        }

        $row = 2; //从第二行开始
        foreach ($data as $item) {

            $column = 1; //从第一列设置并初始化

            foreach ($item as $value) {
                $worksheet->setCellValueByColumnAndRow($column, $row, $value); //哪一列哪一行设置哪个值
                $column++; //列数加1
            }

            $row++; //行数加1
        }

        //输出到浏览器
        $file_name = $file_name. '_' . date('YmdHis', time());
        $file_type = 'Xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $result = self::excelBrowserExport($file_name, $file_type);

        if ($result['code'] == 0) {
            $writer->save('php://output');
            exit();
        }
    }

    static public function excelBrowserExport($file_name, $file_type)
    {
        //文件名称校验
        if (!$file_name) {
            return [
                'code' => 101,
                'msg' => '文件名不能为空'
            ];
        }

        //Excel文件类型校验
        $type = ['Excel2007', 'Xlsx', 'Excel5', 'xls'];
        if (!in_array($file_type, $type)) {
            return [
                'code' => 102,
                'msg' => '未知文件类型'
            ];
        }

        if ($file_type == 'Excel2007' || $file_type == 'Xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
            header('Cache-Control: max-age=0');
        } else {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
            header('Cache-Control: max-age=0');
        }

        return [
            'code' => 0,
            'msg' => ''
        ];
    }

}
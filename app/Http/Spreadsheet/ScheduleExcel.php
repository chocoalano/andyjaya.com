<?php
namespace App\Http\Spreadsheet;

use App\Models\UserAttGroupSchedule;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
final class ScheduleExcel
{
    public function format_download()
    {
        $dataTeam = DB::table("user_att_groups")->get();
        $dataTeamCount = DB::table('user_att_groups')->count('*');
        
        $dataTime = DB::table("att_times")->get();
        $dataTimeCount = DB::table('att_times')->count('*');

        $spreadsheet = new Spreadsheet();
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        // sheet worksheet :: Started
        $ws = $spreadsheet->getActiveSheet();
        $wsLabel = [
            'user_att_group_id',
            'att_time_id',
            'date_work',
        ];
        $wsColumn = 'A';
        foreach ($wsLabel as $row) {
            $ws->setCellValue($wsColumn."1", $row);
            $wsColumn ++;
        }
        $opsTeam = [];
        $opsTime = [];
        foreach ($dataTeam as $key =>$value) {
            array_push($opsTeam, (String)$value->id);
        }
        foreach ($dataTime as $key =>$value) {
            array_push($opsTime, (String)$value->id);
        }
        $strTeam1="='Data Team'!";
        $strTeam2='$A$2:$A$'.count($opsTeam)+1;
        $strTime1="='Data Time'!";
        $strTime2='$A$2:$A$'.count($opsTime)+1;

        $ws->getCell('A2')->getDataValidation()->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_STOP)->setAllowBlank(false)->setShowInputMessage(true)->setShowErrorMessage(true)->setShowDropDown(true)->setErrorTitle('Input error')->setError('Value is not in list.')->setPromptTitle('Pick from list')->setPrompt('Please pick a value from the drop-down list.')->setFormula1("$strTeam1$strTeam2");
        $ws->getCell('B2')->getDataValidation()->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_STOP)->setAllowBlank(false)->setShowInputMessage(true)->setShowErrorMessage(true)->setShowDropDown(true)->setErrorTitle('Input error')->setError('Value is not in list.')->setPromptTitle('Pick from list')->setPrompt('Please pick a value from the drop-down list.')->setFormula1("$strTime1$strTime2");
        $ws->setCellValue('C2', '');
        $ws->setTitle('IMPORT SCHEDULE');
        // sheet worksheet :: Ended
        // ===================================
        // sheet ke satu information :: Started
        $sheet1 = $spreadsheet->createSheet();
        $sheet1->mergeCells('A1:B1');
        $sheet1->setCellValue('A1', 'PERHATIKAN ATURAN PENGISIAN ROW IMPORT DATA.');
        $sheet1->setCellValue('A2', 'COLUMNAME');
        $sheet1->setCellValue('B2', 'INFORMATION');
        $sheet1Label = [ 
            ['colname'=>'user_att_group_id', 'info'=>'(integer), isi dengan id team yang terdapat pada sheet "data team"'],
            ['colname'=>'att_time_id', 'info'=>'(integer), isi dengan id time yang terdapat pada sheet "data time"'],
            ['colname'=>'date_work', 'info'=>'(date), isi dengan format "YYYY-MM-DD"']
        ];
        $sheet1LabelRow = 3;
        foreach ($sheet1Label as $k) {
            $sheet1->setCellValue('A'.$sheet1LabelRow, $k['colname']);
            $sheet1->setCellValue('B'.$sheet1LabelRow, $k['info']);
            $sheet1LabelRow++;
        }
        $styleSheet1 = $sheet1->getStyle('A1:B1');
        $styleSheet1->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $styleSheet1->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $styleSheet1 = $sheet1->getStyle('A1:B1');
        $font = $styleSheet1->getFont();
        $font->setBold(true);
        $sheet1->getStyle('A1:B1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('d1ebff');
        $sheet1->getStyle('A2:B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A2:B2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('7c9288');
        $sheet1->getStyle('A3:B5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('dafffb');
        $sheet1->getStyle('A1:B5')->applyFromArray($styleArray);
        $sheet1->setTitle('INFORMATION');
        // sheet ke satu information :: Ended
        // ===================================
        // sheet ke dua data Teams :: Started
        $sheet2 = $spreadsheet->createSheet();
        $sheet2Header = ['ID', 'NAME', 'CREATED', 'UPDATED'];
        $sheet2cellName = 'A';
        foreach ($sheet2Header as $header) {
            $sheet2->setCellValue($sheet2cellName . '1', $header);
            $sheet2cellName++;
        }
        $row = 2; // Start from row 2 (after the headers)
        foreach ($dataTeam as $item => $v) {
            $sheet2->setCellValue('A' . $row, $v->id);
            $sheet2->setCellValue('B' . $row, $v->name);
            $sheet2->setCellValue('C' . $row, $v->created_at);
            $sheet2->setCellValue('D' . $row, $v->updated_at);
            $row++;
        }
        $tableRangeSheet2 = 'A1:D'.((int)$dataTeamCount + 1);
        $sheet2->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('079246');
        $sheet2->getStyle($tableRangeSheet2)->applyFromArray($styleArray);
        $sheet2->setTitle('Data Team');
        // sheet ke dua data Teams :: Ended
        // ===================================
        // sheet ke tiga data Time :: Started
        $sheet3 = $spreadsheet->createSheet();
        $sheet3Header = ['ID', 'TYPE', 'IN', 'OUT', 'CREATED', 'UPDATED'];
        $sheet3cellName = 'A';
        foreach ($sheet3Header as $header) {
            $sheet3->setCellValue($sheet3cellName . '1', $header);
            $sheet3cellName++;
        }
        $row = 2; // Start fr3m row 2 (after the headers)
        foreach ($dataTime as $item => $v) {
            $sheet3->setCellValue('A' . $row, $v->id);
            $sheet3->setCellValue('B' . $row, $v->type);
            $sheet3->setCellValue('C' . $row, $v->in);
            $sheet3->setCellValue('D' . $row, $v->out);
            $sheet3->setCellValue('E' . $row, $v->created_at);
            $sheet3->setCellValue('F' . $row, $v->updated_at);
            $row++;
        }
        $tableRangeSheet3 = 'A1:F'.((int)$dataTimeCount + 1);
        $sheet3->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet3->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('079246');
        $sheet3->getStyle($tableRangeSheet3)->applyFromArray($styleArray);
        $sheet3->setTitle('Data Time');
        // sheet ke tiga data Time :: Ended
        // ===================================

        $writer = new Xlsx($spreadsheet);
        $filename = date('YmdHis').'_schedule.xlsx';
        $writer->save($filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function import_from_excel($datarow){
        DB::beginTransaction();
        try {
            foreach ($datarow as $k) {
                UserAttGroupSchedule::updateOrCreate($k, $k);
            }
            DB::commit();
            return 'success import!';
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }
}

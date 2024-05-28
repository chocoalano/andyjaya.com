<?php

namespace App\Http\Controllers;

use App\Http\Repositories\Spreadsheet\FormatSchedule;
use App\Http\Spreadsheet\ScheduleExcel;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ImportFormatController extends Controller
{
    public function index($id){
        if($id === 'schedule-attendance'){
            $sp = new ScheduleExcel();
            return $sp->format_download();
        }
    }
}

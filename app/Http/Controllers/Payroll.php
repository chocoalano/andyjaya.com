<?php

namespace App\Http\Controllers;

use App\Models\PermissionForm;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class Payroll extends Controller
{
    public function slip($id){
        $data = \App\Models\Payroll::findOrFail($id);
        $user = $data->user;
        $permissionForm = PermissionForm::where(function($q)use($data){
            $q
            ->where('from_date', '>=', $data->start_periode)
            ->where('to_date', '<=', $data->end_periode);
        })
        ->where('user_id', $data->user_id)
        ->get();
        $component = $data->component;
        $pdf = PDF::loadView('pdf.slip_gaji_pdf',[
            'data'=>$data, 
            'user'=>$user,
            'permissionForm'=>$permissionForm,
            'component'=>$component
        ]);
        return $pdf->download('slip-gaji.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class Payroll extends Controller
{
    public function slip($id){
        $data = \App\Models\Payroll::findOrFail($id);
        $user = $data->user;
        $component = $data->component;
        $pdf = PDF::loadView('pdf.slip_gaji_pdf',[
            'data'=>$data, 
            'user'=>$user,
            'component'=>$component
        ]);
        return $pdf->download('slip-gaji.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InformeHallazgoExport;
use Illuminate\Http\Request;
use App\Models\wbInformeCampo;

class ExportController extends Controller
{
    // public function index()
    // {
    //     return view(
    //         'exporth',
    //         [
    //             'wbInformeCampo' => wbInformeCampo::all()
    //         ]
    //     );
    // }

    public function export_excel_hallazgos()
    {
        $wbInformeCampos = wbInformeCampo::all();
        return Excel::download(new InformeHallazgoExport($wbInformeCampos), 'Informe_hallazgos.xlsx');
    }
}

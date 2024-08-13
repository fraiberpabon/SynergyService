<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Facades\Excel;

class InformeHallazgoExport implements FromView, WithDrawings, WithColumnWidths, WithEvents
{
    protected $wbInformeCampos;
    //use RegistersEventListeners;

    public function __construct($wbInformeCampos)
    {
        $this->wbInformeCampos = $wbInformeCampos;
    }

    public function __destruct()
    {
        // Eliminar fotos temporales
        $fotos = [
            'foto_uno_path', 'foto_dos_path', 'foto_tres_path',
            'foto_cuatro_path', 'foto_cinco_path', 'foto_seis_path', 'foto_cierre1_path', 'foto_cierre2_path'
        ];

        foreach ($this->wbInformeCampos as $item) {
            foreach ($fotos as $fotoPath) {
                if (isset($item->$fotoPath)) {
                    // Verificar si el archivo existe antes de intentar eliminarlo
                    if (file_exists($item->$fotoPath)) {
                        unlink($item->$fotoPath); // Eliminar el archivo
                    }
                }
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet;

                // Ajustar el alto para todas las filas con fotos
                foreach (range(3, count($this->wbInformeCampos) + 3) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(100);
                }
            },
        ];
    }

    public function view(): View
    {
        // Obtener las imágenes procesadas y agregarlas a la colección
        $wbInformeCampos = $this->wbInformeCampos->map(function ($item, $index) {
            $fotos = [
                'foto_uno', 'foto_dos', 'foto_tres', 'foto_cuatro', 'foto_cinco', 'foto_seis', 'foto_cierre1', 'foto_cierre2'
            ];

            foreach ($fotos as $foto) {
                if (!empty($item->$foto)) {
                    // Decodificar la imagen base64 y guardarla temporalmente
                    $imageData = base64_decode($item->$foto);
                    $filePath = storage_path("app/{$foto}_{$index}.png");
                    file_put_contents($filePath, $imageData);

                    // Agregar la ruta de la imagen a la colección para el dibujo
                    $item->{$foto . '_path'} = $filePath;
                }
            }

            return $item;
        });



        return view('informe_hallazgos_excel', ['wbInformeCampo' => $wbInformeCampos]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 23,
            'C' => 23,
            'D' => 42,
            'E' => 34,
            'F' => 35,
            'G' => 12,
            'H' => 35,
            'I' => 32,
            'J' => 32,
            'K' => 32,
            'L' => 32,
            'M' => 32,
            'N' => 32,
            'O' => 12,
            'P' => 30,
            'Q' => 38,
            'R' => 38,
            'S' => 38
        ];
    }

    public function drawings()
    {
        $drawings = [];

        foreach ($this->wbInformeCampos as $index => $item) {
            $fotoFields = [
                ['field' => 'foto_uno_path', 'coordinate' => 'I'],
                ['field' => 'foto_dos_path', 'coordinate' => 'J'],
                ['field' => 'foto_tres_path', 'coordinate' => 'K'],
                ['field' => 'foto_cuatro_path', 'coordinate' => 'L'],
                ['field' => 'foto_cinco_path', 'coordinate' => 'M'],
                ['field' => 'foto_seis_path', 'coordinate' => 'N'],
                ['field' => 'foto_cierre1_path', 'coordinate' => 'Q'],
                ['field' => 'foto_cierre2_path', 'coordinate' => 'R'],
            ];

            foreach ($fotoFields as $field) {
                $fieldName = $field['field'];
                $coordinate = $field['coordinate'];

                if (isset($item->$fieldName)) {
                    $drawing = new Drawing();
                    $drawing->setName("Foto " . ($index + 1));
                    $drawing->setDescription("Foto desde base64");
                    $drawing->setPath($item->$fieldName);
                    $drawing->setHeight(90);
                    $drawing->setCoordinates("{$coordinate}" . ($index + 3));
                    $drawings[] = $drawing;
                }
            }
        }

        return $drawings;
    }
}

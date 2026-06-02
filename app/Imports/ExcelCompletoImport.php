<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelCompletoImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Dim_Cliente' => new HojasExcelImport('dim_cliente'),
            'Dim_Producto' => new HojasExcelImport('dim_producto'),
            'Dim_Tiempo' => new HojasExcelImport('dim_tiempo'),
            'Dim_Ubicacion' => new HojasExcelImport('dim_ubicacion'),
            'Fact_Ventas' => new HojasExcelImport('fact_ventas'),
            'Fact_Opiniones' => new HojasExcelImport('fact_opiniones'),
        ];
    }
}
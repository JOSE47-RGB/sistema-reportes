<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class HojasExcelImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private string $tabla;

    public function __construct(string $tabla)
    {
        $this->tabla = $tabla;
    }

    public function collection(Collection $rows)
    {
        $datos = [];

        foreach ($rows as $row) {
            $row = $row->toArray();

            switch ($this->tabla) {

                case 'dim_cliente':
                    $datos[] = [
                        'Nombre' => $row['nombre'] ?? 'N/A',
                        'Genero' => $row['genero'] ?? 'N/A',
                        'Edad' => $row['edad'] ?? 0,
                        'Ciudad' => $row['ciudad'] ?? 'N/A',
                    ];
                    break;

                case 'dim_producto':
                    $datos[] = [
                        'Producto' => $row['producto'] ?? 'N/A',
                        'Categoria' => $row['categoria'] ?? 'General',
                        'Precio' => $row['precio'] ?? 0,
                    ];
                    break;

                case 'dim_tiempo':
                    $datos[] = [
                        'Fecha' => $row['fecha'] ?? now(),
                        'Anio' => $row['anio'] ?? date('Y'),
                        'Mes' => $row['mes'] ?? date('m'),
                        'Dia' => $row['dia'] ?? date('d'),
                    ];
                    break;

                case 'dim_ubicacion':
                    $datos[] = [
                        'Region' => $row['region'] ?? 'N/A',
                        'Pais' => $row['pais'] ?? 'N/A',
                    ];
                    break;

                case 'fact_ventas':
                    $datos[] = [
                        'ClienteID' => $row['clienteid'] ?? null,
                        'ProductoID' => $row['productoid'] ?? null,
                        'FechaID' => $row['fechaid'] ?? null,
                        'UbicacionID' => $row['ubicacionid'] ?? null,
                        'Cantidad' => $row['cantidad'] ?? 0,
                        'Total' => $row['total'] ?? 0,
                    ];
                    break;

                case 'fact_opiniones':
                    $datos[] = [
                        'ClienteID' => $row['clienteid'] ?? null,
                        'ProductoID' => $row['productoid'] ?? null,
                        'Comentario' => $row['comentario'] ?? 'Sin comentario',
                    ];
                    break;
            }

            if (count($datos) >= 500) {
                DB::table($this->tabla)->insert($datos);
                $datos = [];
            }
        }

        if (!empty($datos)) {
            DB::table($this->tabla)->insert($datos);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
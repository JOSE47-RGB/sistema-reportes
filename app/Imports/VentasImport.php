<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class VentasImport implements ToArray, WithChunkReading, WithStartRow
{
    private array $headers = [];

    public function startRow(): int
    {
        return 1;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function array(array $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {

                if ($index === 0 && empty($this->headers)) {
                    $this->headers = array_map(function ($header) {
                        return trim((string) $header);
                    }, $row);

                    continue;
                }

                if (empty($this->headers)) {
                    continue;
                }

                if (count($this->headers) !== count($row)) {
                    continue;
                }

                $data = array_combine($this->headers, $row);

                $clienteID = DB::table('dim_cliente')
                    ->where('Nombre', $data['Nombre'] ?? '')
                    ->where('Ciudad', $data['Ciudad'] ?? '')
                    ->value('ClienteID');

                if (!$clienteID) {
                    $clienteID = DB::table('dim_cliente')->insertGetId([
                        'Nombre' => $data['Nombre'] ?? 'N/A',
                        'Genero' => $data['Genero'] ?? 'N/A',
                        'Edad' => $data['Edad'] ?? 0,
                        'Ciudad' => $data['Ciudad'] ?? 'N/A',
                    ]);
                }

                $productoID = DB::table('dim_producto')
                    ->where('Producto', $data['Producto'] ?? '')
                    ->value('ProductoID');

                if (!$productoID) {
                    $productoID = DB::table('dim_producto')->insertGetId([
                        'Producto' => $data['Producto'] ?? 'N/A',
                        'Categoria' => $data['Categoria'] ?? 'General',
                        'Precio' => $data['Precio'] ?? 0,
                    ]);
                }

                $fechaID = DB::table('dim_tiempo')
                    ->where('Fecha', $data['Fecha'] ?? '')
                    ->value('FechaID');

                if (!$fechaID) {
                    $fechaID = DB::table('dim_tiempo')->insertGetId([
                        'Fecha' => $data['Fecha'] ?? now(),
                        'Anio' => $data['Anio'] ?? date('Y'),
                        'Mes' => $data['Mes'] ?? date('m'),
                        'Dia' => $data['Dia'] ?? date('d'),
                    ]);
                }

                $ubicacionID = DB::table('dim_ubicacion')
                    ->where('Region', $data['Region'] ?? '')
                    ->where('Pais', $data['Pais'] ?? '')
                    ->value('UbicacionID');

                if (!$ubicacionID) {
                    $ubicacionID = DB::table('dim_ubicacion')->insertGetId([
                        'Region' => $data['Region'] ?? 'N/A',
                        'Pais' => $data['Pais'] ?? 'N/A',
                    ]);
                }

                DB::table('fact_ventas')->insert([
                    'ClienteID' => $clienteID,
                    'ProductoID' => $productoID,
                    'FechaID' => $fechaID,
                    'UbicacionID' => $ubicacionID,
                    'Cantidad' => $data['Cantidad'] ?? 0,
                    'Total' => $data['Total'] ?? 0,
                ]);

                if (!empty($data['Comentario'])) {
                    DB::table('fact_opiniones')->insert([
                        'ClienteID' => $clienteID,
                        'ProductoID' => $productoID,
                        'Comentario' => $data['Comentario'],
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
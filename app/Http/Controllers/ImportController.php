<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        // ✅ VALIDACIÓN (sin tabla)
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:20480'
        ]);

        $file = $request->file('archivo');
        $ruta = $file->getRealPath();

        $handle = fopen($ruta, "r");
        $headers = fgetcsv($handle);

        DB::beginTransaction();

        try {

            while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {

                $data = array_combine($headers, $row);

                // ===================== CLIENTE
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

                // ===================== PRODUCTO
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

                // ===================== TIEMPO
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

                // ===================== UBICACION
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

                // ===================== FACT VENTAS
                DB::table('fact_ventas')->insert([
                    'ClienteID' => $clienteID,
                    'ProductoID' => $productoID,
                    'FechaID' => $fechaID,
                    'UbicacionID' => $ubicacionID,
                    'Cantidad' => $data['Cantidad'] ?? 0,
                    'Total' => $data['Total'] ?? 0,
                ]);

                // ===================== OPINIONES
                if (!empty($data['Comentario'])) {
                    DB::table('fact_opiniones')->insert([
                        'ClienteID' => $clienteID,
                        'ProductoID' => $productoID,
                        'Comentario' => $data['Comentario'],
                    ]);
                }
            }

            fclose($handle);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Carga automática completada correctamente');
    }
}
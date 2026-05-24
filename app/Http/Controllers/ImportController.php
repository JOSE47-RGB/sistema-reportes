<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function import(Request $request)
    {
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

                    $this->registrarBitacora(
                        'IMPORTAR',
                        'dim_cliente',
                        $clienteID,
                        'Se importó un cliente desde archivo CSV'
                    );
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

                    $this->registrarBitacora(
                        'IMPORTAR',
                        'dim_producto',
                        $productoID,
                        'Se importó un producto desde archivo CSV'
                    );
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

                    $this->registrarBitacora(
                        'IMPORTAR',
                        'dim_tiempo',
                        $fechaID,
                        'Se importó una fecha desde archivo CSV'
                    );
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

                    $this->registrarBitacora(
                        'IMPORTAR',
                        'dim_ubicacion',
                        $ubicacionID,
                        'Se importó una ubicación desde archivo CSV'
                    );
                }

                $ventaID = DB::table('fact_ventas')->insertGetId([
                    'ClienteID' => $clienteID,
                    'ProductoID' => $productoID,
                    'FechaID' => $fechaID,
                    'UbicacionID' => $ubicacionID,
                    'Cantidad' => $data['Cantidad'] ?? 0,
                    'Total' => $data['Total'] ?? 0,
                ]);

                $this->registrarBitacora(
                    'IMPORTAR',
                    'fact_ventas',
                    $ventaID,
                    'Se importó una venta desde archivo CSV'
                );

                if (!empty($data['Comentario'])) {
                    $opinionID = DB::table('fact_opiniones')->insertGetId([
                        'ClienteID' => $clienteID,
                        'ProductoID' => $productoID,
                        'Comentario' => $data['Comentario'],
                    ]);

                    $this->registrarBitacora(
                        'IMPORTAR',
                        'fact_opiniones',
                        $opinionID,
                        'Se importó una opinión desde archivo CSV'
                    );
                }
            }

            fclose($handle);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Carga automática completada correctamente con bitácora.');
    }

    private function registrarBitacora($accion, $tabla, $idRegistro, $descripcion)
    {
        DB::table('bitacora_auditoria')->insert([
            'user_id' => auth()->id(),
            'usuario' => auth()->user()->name ?? 'Sistema',
            'accion' => $accion,
            'tabla_afectada' => $tabla,
            'id_registro' => $idRegistro,
            'descripcion' => $descripcion,
            'fecha' => now(),
        ]);
    }
}
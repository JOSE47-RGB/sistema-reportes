<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt,xlsx,xls,xml|max:102400'
        ]);

        $file = $request->file('archivo');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'xlsx' || $extension === 'xls') {
                $carpetaProcesada = $this->procesarConPython($file);
                $totalFilas = $this->importarExcelPorHojasProcesadas($carpetaProcesada);

                $this->registrarBitacora(
                    'IMPORTAR',
                    'archivo_excel',
                    0,
                    'Se importó archivo Excel procesado por hojas con Python. Filas: ' . $totalFilas
                );

                return back()->with('success', 'Archivo Excel cargado por hojas correctamente. Filas: ' . $totalFilas);
            }

            if ($extension === 'xml') {
                $carpetaProcesada = $this->procesarConPython($file);
                $csvXml = $carpetaProcesada . '/datos.csv';

                if (!file_exists($csvXml)) {
                    throw new \Exception('Python no generó el archivo XML procesado: ' . $csvXml);
                }

                $totalFilas = $this->importarCsvRapido($csvXml);

                $this->registrarBitacora(
                    'IMPORTAR',
                    'archivo_xml',
                    0,
                    'Se importó archivo XML procesado con Python. Filas: ' . $totalFilas
                );

                return back()->with('success', 'Archivo XML cargado correctamente. Filas: ' . $totalFilas);
            }

            if ($extension === 'csv' || $extension === 'txt') {
                $totalFilas = $this->importarCsvRapido($file->getRealPath());

                $this->registrarBitacora(
                    'IMPORTAR',
                    'archivo_csv',
                    0,
                    'Se importó archivo CSV/TXT mediante staging. Filas: ' . $totalFilas
                );

                return back()->with('success', 'Archivo CSV/TXT cargado correctamente. Filas: ' . $totalFilas);
            }

            return back()->withErrors(['archivo' => 'Formato no permitido.']);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function procesarConPython($file)
    {
        $nombreArchivo = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());

        $rutaImports = storage_path('app/imports');
        $rutaEntrada = $rutaImports . '/' . $nombreArchivo;
        $carpetaSalida = $rutaImports . '/procesados_' . time();

        if (!file_exists($rutaImports)) {
            mkdir($rutaImports, 0775, true);
        }

        if (!file_exists($carpetaSalida)) {
            mkdir($carpetaSalida, 0775, true);
        }

        $file->move($rutaImports, $nombreArchivo);

        $script = base_path('scripts/etl_import.py');

        if (!file_exists($script)) {
            throw new \Exception('No existe el script Python: ' . $script);
        }

        $comando = 'py ' .
            escapeshellarg($script) . ' ' .
            escapeshellarg($rutaEntrada) . ' ' .
            escapeshellarg($carpetaSalida) . ' 2>&1';

        exec($comando, $salida, $codigo);

        if ($codigo !== 0) {
            throw new \Exception('Error ejecutando Python: ' . implode("\n", $salida));
        }

        return $carpetaSalida;
    }

    private function importarExcelPorHojasProcesadas($carpeta)
    {
        $total = 0;

        $archivos = [
            'dim_cliente' => $carpeta . '/dim_cliente.csv',
            'dim_producto' => $carpeta . '/dim_producto.csv',
            'dim_tiempo' => $carpeta . '/dim_tiempo.csv',
            'dim_ubicacion' => $carpeta . '/dim_ubicacion.csv',
            'fact_ventas' => $carpeta . '/fact_ventas.csv',
            'fact_opiniones' => $carpeta . '/fact_opiniones.csv',
        ];

        DB::beginTransaction();

        try {
            foreach ($archivos as $tabla => $ruta) {
                if (file_exists($ruta)) {
                    $total += $this->cargarCsvTablaDirecta($ruta, $tabla);
                }
            }

            DB::commit();

            return $total;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function cargarCsvTablaDirecta($ruta, $tabla)
    {
        $handle = fopen($ruta, "r");

        if (!$handle) {
            throw new \Exception("No se pudo abrir el archivo: " . $ruta);
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            return 0;
        }

        $headers = array_map(function ($header) {
            return trim((string) $header);
        }, $headers);

        $lote = [];
        $tamanoLote = 100;
        $total = 0;

        while (($row = fgetcsv($handle, 10000, ",")) !== false) {
            if (count($headers) !== count($row)) {
                continue;
            }

            $data = array_combine($headers, $row);
            $fila = $this->limpiarFilaPorTabla($data, $tabla);

            if ($fila === null) {
                continue;
            }

            $lote[] = $fila;
            $total++;

            if (count($lote) >= $tamanoLote) {
                DB::table($tabla)->insertOrIgnore($lote);
                $lote = [];
            }
        }

        if (!empty($lote)) {
            DB::table($tabla)->insertOrIgnore($lote);
        }

        fclose($handle);

        return $total;
    }

    private function limpiarFilaPorTabla($data, $tabla)
    {
        switch ($tabla) {
            case 'dim_cliente':
                return [
                    'ClienteID' => $this->entero($data['ClienteID'] ?? null),
                    'Nombre' => $this->texto($data['Nombre'] ?? null) ?? 'N/A',
                    'Genero' => $this->texto($data['Genero'] ?? null) ?? 'N/A',
                    'Edad' => $this->entero($data['Edad'] ?? null) ?? 0,
                    'Ciudad' => $this->texto($data['Ciudad'] ?? null) ?? 'N/A',
                ];

            case 'dim_producto':
                return [
                    'ProductoID' => $this->entero($data['ProductoID'] ?? null),
                    'Producto' => $this->texto($data['Producto'] ?? null) ?? 'N/A',
                    'Categoria' => $this->texto($data['Categoria'] ?? null) ?? 'General',
                    'Precio' => $this->decimal($data['Precio'] ?? null) ?? 0,
                ];

            case 'dim_tiempo':
                $fecha = $this->fechaMysql($data['Fecha'] ?? null);

                return [
                    'FechaID' => $this->entero($data['FechaID'] ?? null),
                    'Fecha' => $fecha ?? date('Y-m-d'),
                    'Anio' => $this->entero($data['Anio'] ?? $data['Año'] ?? null) ?? (int) date('Y'),
                    'Mes' => $this->entero($data['Mes'] ?? null) ?? (int) date('m'),
                    'Dia' => $this->entero($data['Dia'] ?? null) ?? (int) date('d'),
                ];

            case 'dim_ubicacion':
                return [
                    'UbicacionID' => $this->entero($data['UbicacionID'] ?? null),
                    'Region' => $this->texto($data['Region'] ?? null) ?? 'N/A',
                    'Pais' => $this->texto($data['Pais'] ?? null) ?? 'N/A',
                ];

            case 'fact_ventas':
                return [
                    'VentaID' => $this->entero($data['VentaID'] ?? null),
                    'ClienteID' => $this->entero($data['ClienteID'] ?? null),
                    'ProductoID' => $this->entero($data['ProductoID'] ?? null),
                    'FechaID' => $this->entero($data['FechaID'] ?? null),
                    'UbicacionID' => $this->entero($data['UbicacionID'] ?? null),
                    'Cantidad' => $this->entero($data['Cantidad'] ?? null) ?? 0,
                    'Total' => $this->decimal($data['Total'] ?? null) ?? 0,
                ];

            case 'fact_opiniones':
                return [
                    'OpinionID' => $this->entero($data['OpinionID'] ?? null),
                    'ClienteID' => $this->entero($data['ClienteID'] ?? null),
                    'ProductoID' => $this->entero($data['ProductoID'] ?? null),
                    'Comentario' => $this->texto($data['Comentario'] ?? null) ?? 'Sin comentario',
                ];
        }

        return null;
    }

    private function importarCsvRapido($ruta)
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS staging_importacion (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                Nombre VARCHAR(150) NULL,
                Genero VARCHAR(20) NULL,
                Edad INT NULL,
                Ciudad VARCHAR(100) NULL,
                Producto VARCHAR(150) NULL,
                Categoria VARCHAR(100) NULL,
                Precio DECIMAL(10,2) NULL,
                Fecha DATE NULL,
                Anio INT NULL,
                Mes INT NULL,
                Dia INT NULL,
                Region VARCHAR(100) NULL,
                Pais VARCHAR(100) NULL,
                Cantidad INT NULL,
                Total DECIMAL(10,2) NULL,
                Comentario TEXT NULL
            )
        ");

        $this->crearIndicesStaging();

        DB::table('staging_importacion')->truncate();

        $handle = fopen($ruta, "r");

        if (!$handle) {
            throw new \Exception('No se pudo abrir el archivo CSV procesado.');
        }

        fgetcsv($handle);

        $lote = [];
        $tamanoLote = 100;
        $totalFilas = 0;

        while (($row = fgetcsv($handle, 10000, ",")) !== false) {
            if (count($row) < 16) {
                continue;
            }

            $lote[] = $this->prepararFilaStaging($row);
            $totalFilas++;

            if (count($lote) >= $tamanoLote) {
                DB::table('staging_importacion')->insert($lote);
                $lote = [];
            }
        }

        if (!empty($lote)) {
            DB::table('staging_importacion')->insert($lote);
        }

        fclose($handle);

        $this->pasarStagingADimensionesYHechos();

        return $totalFilas;
    }

    private function prepararFilaStaging($row)
    {
        $fecha = $this->fechaMysql($row[7] ?? null);

        $anio = null;
        $mes = null;
        $dia = null;

        if ($fecha) {
            $dt = new \DateTime($fecha);
            $anio = (int) $dt->format('Y');
            $mes = (int) $dt->format('m');
            $dia = (int) $dt->format('d');
        }

        return [
            'Nombre' => $this->texto($row[0] ?? null),
            'Genero' => $this->texto($row[1] ?? null),
            'Edad' => $this->entero($row[2] ?? null),
            'Ciudad' => $this->texto($row[3] ?? null),
            'Producto' => $this->texto($row[4] ?? null),
            'Categoria' => $this->texto($row[5] ?? null),
            'Precio' => $this->decimal($row[6] ?? null),
            'Fecha' => $fecha,
            'Anio' => $anio,
            'Mes' => $mes,
            'Dia' => $dia,
            'Region' => $this->texto($row[11] ?? null),
            'Pais' => $this->texto($row[12] ?? null),
            'Cantidad' => $this->entero($row[13] ?? null),
            'Total' => $this->decimal($row[14] ?? null),
            'Comentario' => $this->texto($row[15] ?? null),
        ];
    }

    private function texto($valor)
    {
        $valor = trim((string) $valor);
        return $valor !== '' ? $valor : null;
    }

    private function entero($valor)
    {
        $valor = trim((string) $valor);
        return is_numeric($valor) ? (int) $valor : null;
    }

    private function decimal($valor)
    {
        $valor = trim((string) $valor);
        $valor = str_replace(',', '.', $valor);
        return is_numeric($valor) ? (float) $valor : null;
    }

    private function fechaMysql($valor)
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        $formatos = ['d/m/Y', 'Y-m-d', 'd-m-Y'];

        foreach ($formatos as $formato) {
            $fecha = \DateTime::createFromFormat($formato, $valor);

            if ($fecha) {
                return $fecha->format('Y-m-d');
            }
        }

        return null;
    }

    private function crearIndicesStaging()
    {
        $indices = [
            'idx_staging_nombre_ciudad' => 'CREATE INDEX idx_staging_nombre_ciudad ON staging_importacion (Nombre, Ciudad)',
            'idx_staging_producto' => 'CREATE INDEX idx_staging_producto ON staging_importacion (Producto)',
            'idx_staging_fecha' => 'CREATE INDEX idx_staging_fecha ON staging_importacion (Fecha)',
            'idx_staging_region_pais' => 'CREATE INDEX idx_staging_region_pais ON staging_importacion (Region, Pais)',
        ];

        foreach ($indices as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                // índice ya existe
            }
        }
    }

    private function pasarStagingADimensionesYHechos()
    {
        DB::beginTransaction();

        try {
            DB::statement("
                INSERT INTO dim_cliente (Nombre, Genero, Edad, Ciudad)
                SELECT DISTINCT
                    IFNULL(Nombre, 'N/A'),
                    IFNULL(Genero, 'N/A'),
                    IFNULL(Edad, 0),
                    IFNULL(Ciudad, 'N/A')
                FROM staging_importacion s
                WHERE NOT EXISTS (
                    SELECT 1 FROM dim_cliente c
                    WHERE c.Nombre = IFNULL(s.Nombre, 'N/A')
                    AND c.Ciudad = IFNULL(s.Ciudad, 'N/A')
                )
            ");

            DB::statement("
                INSERT INTO dim_producto (Producto, Categoria, Precio)
                SELECT DISTINCT
                    IFNULL(Producto, 'N/A'),
                    IFNULL(Categoria, 'General'),
                    IFNULL(Precio, 0)
                FROM staging_importacion s
                WHERE NOT EXISTS (
                    SELECT 1 FROM dim_producto p
                    WHERE p.Producto = IFNULL(s.Producto, 'N/A')
                )
            ");

            DB::statement("
                INSERT INTO dim_tiempo (Fecha, Anio, Mes, Dia)
                SELECT DISTINCT
                    IFNULL(Fecha, CURDATE()),
                    IFNULL(Anio, YEAR(CURDATE())),
                    IFNULL(Mes, MONTH(CURDATE())),
                    IFNULL(Dia, DAY(CURDATE()))
                FROM staging_importacion s
                WHERE NOT EXISTS (
                    SELECT 1 FROM dim_tiempo t
                    WHERE t.Fecha = IFNULL(s.Fecha, CURDATE())
                )
            ");

            DB::statement("
                INSERT INTO dim_ubicacion (Region, Pais)
                SELECT DISTINCT
                    IFNULL(Region, 'N/A'),
                    IFNULL(Pais, 'N/A')
                FROM staging_importacion s
                WHERE NOT EXISTS (
                    SELECT 1 FROM dim_ubicacion u
                    WHERE u.Region = IFNULL(s.Region, 'N/A')
                    AND u.Pais = IFNULL(s.Pais, 'N/A')
                )
            ");

            DB::statement("
                INSERT INTO fact_ventas (ClienteID, ProductoID, FechaID, UbicacionID, Cantidad, Total)
                SELECT
                    c.ClienteID,
                    p.ProductoID,
                    t.FechaID,
                    u.UbicacionID,
                    IFNULL(s.Cantidad, 0),
                    IFNULL(s.Total, 0)
                FROM staging_importacion s
                INNER JOIN dim_cliente c
                    ON c.Nombre = IFNULL(s.Nombre, 'N/A')
                    AND c.Ciudad = IFNULL(s.Ciudad, 'N/A')
                INNER JOIN dim_producto p
                    ON p.Producto = IFNULL(s.Producto, 'N/A')
                INNER JOIN dim_tiempo t
                    ON t.Fecha = IFNULL(s.Fecha, CURDATE())
                INNER JOIN dim_ubicacion u
                    ON u.Region = IFNULL(s.Region, 'N/A')
                    AND u.Pais = IFNULL(s.Pais, 'N/A')
            ");

            DB::statement("
                INSERT INTO fact_opiniones (ClienteID, ProductoID, Comentario)
                SELECT
                    c.ClienteID,
                    p.ProductoID,
                    s.Comentario
                FROM staging_importacion s
                INNER JOIN dim_cliente c
                    ON c.Nombre = IFNULL(s.Nombre, 'N/A')
                    AND c.Ciudad = IFNULL(s.Ciudad, 'N/A')
                INNER JOIN dim_producto p
                    ON p.Producto = IFNULL(s.Producto, 'N/A')
                WHERE s.Comentario IS NOT NULL
                AND s.Comentario <> ''
            ");

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
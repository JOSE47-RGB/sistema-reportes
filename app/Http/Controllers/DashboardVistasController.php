<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardVistasController extends Controller
{
    public function index()
    {
        return view('dashboard-vistas', [
            'resumen' => DB::table('vw_resumen_dashboard')->first(),

            'ventasDetalle' => DB::table('vw_ventas_detalle')
                ->limit(20)
                ->get(),

            'ventasProducto' => DB::table('vw_ventas_por_producto')
                ->limit(10)
                ->get(),

            'ventasCliente' => DB::table('vw_ventas_por_cliente')
                ->limit(10)
                ->get(),

            'ventasUbicacion' => DB::table('vw_ventas_por_ubicacion')
                ->limit(10)
                ->get(),

            'opiniones' => DB::table('vw_opiniones_detalle')
                ->limit(10)
                ->get(),

            'topProductos' => DB::table('vw_top_productos_vendidos')
                ->limit(10)
                ->get(),

            'bitacora' => DB::table('vw_bitacora_usuarios')
                ->limit(10)
                ->get(),
        ]);
    }
}
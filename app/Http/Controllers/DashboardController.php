<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalClientes = DB::table('dim_cliente')->count();
        $totalProductos = DB::table('dim_producto')->count();
        $totalVentas = DB::table('fact_ventas')->count();
        $montoTotal = DB::table('fact_ventas')->sum('Total');

        $ventasPorProducto = DB::table('fact_ventas')
            ->join('dim_producto', 'fact_ventas.ProductoID', '=', 'dim_producto.ProductoID')
            ->select('dim_producto.Producto', DB::raw('SUM(fact_ventas.Total) as total'))
            ->groupBy('dim_producto.Producto')
            ->get();

        $ultimasVentas = DB::table('fact_ventas')
            ->join('dim_cliente', 'fact_ventas.ClienteID', '=', 'dim_cliente.ClienteID')
            ->join('dim_producto', 'fact_ventas.ProductoID', '=', 'dim_producto.ProductoID')
            ->select(
                'fact_ventas.VentaID',
                'dim_cliente.Nombre',
                'dim_producto.Producto',
                'fact_ventas.Cantidad',
                'fact_ventas.Total'
            )
            ->orderBy('fact_ventas.VentaID', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard-datos', compact(
            'totalClientes',
            'totalProductos',
            'totalVentas',
            'montoTotal',
            'ventasPorProducto',
            'ultimasVentas'
        ));
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Exports\ReportExport;
use App\Exports\ReportViewExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\Reporter;
use App\Models\Factura;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\PrecioGeneral;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use ApiResponser, Reporter;

    public function ventas() {
        $query = Venta::select(
            'ventas.codigo_secuencia',
            'ventas.total',
            'ventas.descuento',
            'ventas.fecha',
            'ventas.descripcion',
            'ventas.informacion_tarjeta',
            'ventas.estado',
            'metodos_pago.metodo AS metodo_pago',
            'sucursales.nombres AS sucursal',
            'clientes.razon_social AS cliente',
            'tipo_venta.tipo AS tipo_venta',
            'puntos_venta.nombre AS punto_venta',
        )
        ->join('metodos_pago', 'ventas.metodo_pago_id', 'metodos_pago.id')
        ->join('sucursales', 'ventas.sucursal_id', 'sucursales.id')
        ->join('clientes', 'ventas.cliente_id', 'clientes.id')
        ->join('tipo_venta', 'ventas.tipo_venta_id', 'tipo_venta.id_tipo_venta')
        ->join('puntos_venta', 'ventas.punto_venta_id', 'puntos_venta.id')
        ->when( request()->query('informe', 'resumen') == 'detalle', function ($query) {
            $query->addSelect('dv.cantidad', 'dv.precio', 'dv.descuento', 'dv.sub_total', 'p.producto')
                ->join('detalle_venta AS dv', 'ventas.id', 'dv.venta_id')
                ->join('inventario AS i', 'dv.inventario_id', 'i.id')
                ->join('productos AS p', 'i.producto_id', 'p.id');
        })
        ->withDate('ventas.fecha')
        ->filter()
        ->get();

        if( request()->query('informe', 'resumen') == 'detalle' ) {
            $columns = [
                ['field' => 'fecha', 'as' => 'Fecha'],
                ['field' => 'codigo_secuencia', 'as' => 'Nro venta'],
                ['field' => 'producto', 'as' => 'Producto'],
                ['field' => 'cantidad', 'as' => 'Cant.', 'type' => 'number'],
                ['field' => 'precio', 'as' => 'Precio', 'type' => 'money'],
                ['field' => 'descuento', 'as' => 'Descuento', 'type' => 'money'],
                ['field' => 'sub_total', 'as' => 'Subtotal', 'type' => 'money'],
            ];
        } else {
            $columns =  [
                ['field' => 'fecha', 'as' => 'Fecha'],
                ['field' => 'codigo_secuencia', 'as' => 'Nro venta'],
                ['field' => 'cliente', 'as' => 'Cliente',],
                ['field' => 'metodo_pago', 'as' => 'Metodo de pago'],
                ['field' => 'punto_venta', 'as' => 'Punto de venta'],
                ['field' => 'tipo_venta', 'as' => 'Tipo de venta'],
                ['field' => 'total', 'as' => 'Total', 'type' => 'money'],
            ];
        }

        return $this->export([
            'title' => 'Reporte de ventas',
            'data' => $query,
            'columns' => $columns
        ]);
    }

    public function compras() {
        $query = Compra::select(
            'compras.total',
            'compras.codigo_secuencia',
            'compras.descuento',
            'compras.pagos',
            'compras.escaneado',
            'compras.estado',
            'compras.created_at',
            'compras.updated_at',
            'sucursal_id',
            'presupuesto_id',
            'tipo_compra_id',
            'contacto_id',
            'transporte_id',
            'autorizacion_id',
            'sucursales.nombres AS sucursal',
            'tipos_compra.tipo As tipo_compra',
            DB::raw("CONCAT_WS(' ', contactos.nombres, contactos.apellidos) AS contacto"),
        )
        ->join('sucursales', 'compras.sucursal_id', 'sucursales.id')
        ->join('tipos_compra', 'compras.tipo_compra_id', 'tipos_compra.id_tipo_compra')
        ->join('contactos', 'compras.contacto_id', 'contactos.id_contacto')
        ->withDate('compras.created_at')
        ->filter()
        ->get();

        return $this->export([
            'title' => 'compras',
            'data' => $query,
            'columns' => [
                ['field' => 'codigo_secuencia', 'as' => 'Nro compra'],
                ['field' => 'total', 'as' => 'Total Bs'],
                ['field' => 'descuento', 'as' => 'Descuento Bs'],
                ['field' => 'estado', 'as' => 'Estado'],
                ['field' => 'sucursal', 'as' => 'Sucursal'],
                ['field' => 'tipo_compra', 'as' => 'Tipo de compra'],
                ['field' => 'contacto', 'as' => 'Contacto'],
            ]
        ]);
    }

    public function facturas() {
        $query = Factura::select(
            'facturas.codigo_documento_sector',
            'facturas.codigo_tipo_factura',
            'facturas.numero_documento_identidad AS nit_ci',
            'facturas.codigo_documento_identidad',
            'facturas.codigo_metodo_pago',
            'facturas.codigo_cliente',
            'facturas.razon_social',
            'facturas.leyenda',
            'facturas.usuario',
            'facturas.cuf',
            'facturas.cafc',
            'facturas.estado',
            'facturas.venta_id',
            'facturas.cufd_id',
            'v.fecha AS fecha',
            'v.total AS total',
            'ds.descripcion AS documento_sector',
            'tf.descripcion AS tipo_factura',
            'di.descripcion AS documento_identidad',
        )
        ->join('ventas AS v', 'facturas.venta_id', 'v.id')
        ->join('valores_catalogo AS ds', function ($join) {
            // Documento sector
            $join->on('facturas.codigo_documento_sector', 'ds.codigo_clasificador')
            ->where('ds.sincronizacion_catalogo_id', function($query) {
                $query->select('id')
                ->from('sincronizacion_catalogos AS sc')
                ->where('sc.catalogo_facturacion_id', 8)
                ->limit(1);
            });
        })
        ->join('valores_catalogo AS tf', function ($join) {
            // Tipo factura
            $join->on('facturas.codigo_tipo_factura', 'tf.codigo_clasificador')
            ->where('tf.sincronizacion_catalogo_id', function($query) {
                $query->select('id')
                ->from('sincronizacion_catalogos AS sc')
                ->where('sc.catalogo_facturacion_id', 9)
                ->limit(1);
            });
        })
        ->join('valores_catalogo AS di', function($join) {
            $join->on('facturas.codigo_documento_identidad', 'di.codigo_clasificador')
            ->where('di.sincronizacion_catalogo_id', function($query) {
                $query->select('id')
                ->from('sincronizacion_catalogos AS sc')
                ->where('sc.catalogo_facturacion_id', 6)
                ->limit(1);
            });
        })
        ->withDate('v.fecha')
        ->filter()
        ->get();

        return $this->export([
            'title' => 'Reporte de facturas',
            'data' => $query,
            'columns' => [
                ['field' => 'fecha', 'as' => 'Fecha'],
                ['field' => 'nit_ci', 'as' => 'NIT/CI'],
                ['field' => 'razon_social', 'as' => 'Razon social'],
                ['field' => 'documento_sector', 'as' => 'Documento sector'],
                ['field' => 'tipo_factura', 'as' => 'Tipo de factura'],
                ['field' => 'documento_identidad', 'as' => 'Documento de identidad'],
                ['field' => 'estado', 'as' => 'Estado'],
                ['field' => 'total', 'as' => 'Total Bs'],
            ],
        ]);
    }

    public function productos() {
        $query = Producto::select(
            'productos.codigo_alternativo',
            'productos.producto',
            // 'productos.descripcion',
            'productos.codigo_barra',
            'productos.codigo_qr',
            'productos.observaciones',
            'productos.estado',
            'productos.created_at AS fecha_creacion',
            'productos.updated_at AS fecha_actualizacion',
            'l.linea AS linea',
            'sl.sub_linea AS sublinea',
            'p.proveedor AS proveedor',
            'cp.clasificacion AS clasificacion',
            'g.grupo AS grupo',
            'tp.tipo AS tipo',
            'pg.precio_venta AS precio_venta_gral',
            'pg.descuento_venta AS descuento_venta_gral',
        )
        ->join('lineas AS l', 'productos.linea_id', 'l.id_linea')
        ->join('sub_lineas AS sl', 'productos.sub_linea_id', 'sl.id_sub_linea')
        ->join('proveedores AS p', 'productos.proveedor_id', 'p.id_proveedor')
        ->join('clasificaciones_producto AS cp', 'productos.clasificacion_producto_id', 'cp.id')
        ->join('grupos AS g', 'productos.grupo_id', 'g.id_grupo')
        ->join('tipos_producto AS tp', 'productos.tipo_producto_id', 'tp.id_tipo_producto')
        ->leftJoin('precios_general AS pg', function ($join) {
            $join->on('productos.id', 'pg.producto_id')
                ->whereNull('pg.deleted_at');
        })
        ->filter()
        ->get();

        return $this->export([
            'title' => 'Reporte de productos',
            'data' => $query,
            'columns' => [
                ['field' => 'codigo_alternativo', 'as' => 'Codigo alternativo'],
                ['field' => 'producto', 'as' => 'Producto'],
                ['field' => 'linea', 'as' => 'Linea'],
                ['field' => 'sublinea', 'as' => 'Sublinea'],
                ['field' => 'proveedor', 'as' => 'Proveedor'],
                ['field' => 'clasificacion', 'as' => 'Clasificacion'],
                ['field' => 'grupo', 'as' => 'Grupo'],
                ['field' => 'tipo', 'as' => 'Tipo'],
                ['field' => 'descripcion', 'as' => 'Descripcion'],
                ['field' => 'estado', 'as' => 'Estado'],
                ['field' => 'fecha_creacion', 'as' => 'Fecha de creacion'],
            ]
        ]);
    }

    public function inventarios() {
        $query = Inventario::select(
            'cantidad',
            'cantidad_maxima',
            'cantidad_minima',
            'producto_id',
            'sucursal_id',
            'p.producto AS producto',
            's.nombres AS sucursal',
        )
        ->join('productos AS p', 'inventario.producto_id', 'p.id')
        ->join('sucursales AS s', 'inventario.sucursal_id', 's.id')
        ->filter()
        ->get();

        return $this->export([
            'title' => 'Reporte de inventarios',
            'data' => $query,
            'columns' => [
                ['field' => 'sucursal', 'as' => 'Sucursal'],
                ['field' => 'producto', 'as' => 'Producto'],
                ['field' => 'cantidad', 'as' => 'Stock'],
                ['field' => 'cantidad_maxima', 'as' => 'Cant. maxima'],
                ['field' => 'cantidad_minima', 'as' => 'Cant. minima'],
            ]
        ]);
    }

    public function traspasos() {
        $query = Traspaso::select(
            'traspasos.codigo_secuencia',
            'traspasos.total',
            // 'traspasos.descuento',
            'traspasos.costo_transporte',
            'traspasos.estado',
            'traspasos.sucursal_origen',
            'traspasos.sucursal_destino',
            'traspasos.transporte_id',
            'traspasos.tipo_traspaso_id',
            'traspasos.autorizacion_id',
            'so.nombres AS sucursal_origen',
            'sd.nombres AS sucursal_destino',
            'tt.tipo AS tipo_traspaso',
            'a.fecha_solicitud',
            'a.observaciones_solicitud',
            'a.fecha_autorizacion',
            'a.observaciones_autorizacion',
            'a.fecha_recepcion',
            'a.observaciones_recepcion',
            'a.solicitado_por',
            'a.autorizado_por',
            'a.recepcionado_por',
        )
        ->join('sucursales AS so', 'traspasos.sucursal_origen', 'so.id')
        ->join('sucursales AS sd', 'traspasos.sucursal_destino', 'sd.id')
        ->join('tipo_traspaso AS tt', 'traspasos.tipo_traspaso_id', 'tt.id_tipo_traspaso')
        ->join('autorizaciones AS a', 'traspasos.autorizacion_id', 'a.id_autorizacion')
        ->filter()
        ->get();

        return $this->export([
            'title' => 'Reporte de traspasos',
            'data' => $query,
            'columns' => [
                ['field' => 'codigo_secuencia', 'as' => 'Nro. traspaso'],
                ['field' => 'total', 'as' => 'Total'],
                ['field' => 'costo_transporte', 'as' => 'Costo transporte'],
                ['field' => 'estado', 'as' => 'Estado'],
                ['field' => 'sucursal_origen', 'as' => 'Sucursal origen'],
                ['field' => 'sucursal_destino', 'as' => 'Sucursal destino'],
                ['field' => 'tipo_traspaso', 'as' => 'Tipo de traspaso'],
            ],
        ]);
    }

    public function sucursales() {
        $query = Sucursal::select(
            'sucursales.codigo_siat',
            'sucursales.nombres AS nombre',
            'sucursales.abreviatura',
            'sucursales.direccion',
            'sucursales.latitud',
            'sucursales.longitud',
            'sucursales.telefono',
            'sucursales.email',
            'sucursales.tipo',
            'sucursales.estado',
            'sucursales.estado_lote',
            'sucursales.localidad_id',
            'sucursales.tipo_sucursal_id',
            'l.localidad AS localidad',
            'ts.tipo AS tipo_sucursal'
        )
        ->join('localidades AS l', 'sucursales.localidad_id', 'l.id_localidad')
        ->join('tipo_sucursal AS ts', 'sucursales.tipo_sucursal_id', 'ts.id_tipo_sucursal')
        ->get();

        return $this->export([
            'title' => 'Reporte de sucursales',
            'data' => $query,
            'columns' => [
                ['field' => 'codigo_siat', 'as' => 'Codigo SIN'],
                ['field' => 'nombre', 'as' => 'Sucursal'],
                ['field' => 'abreviatura', 'as' => 'Abreviatura'],
                ['field' => 'direccion', 'as' => 'Direccion'],
                ['field' => 'telefono', 'as' => 'Telefono'],
                ['field' => 'email', 'as' => 'Email'],
                ['field' => 'tipo', 'as' => 'Tipo'],
                ['field' => 'estado', 'as' => 'Estado'],
                ['field' => 'localidad', 'as' => 'Localidad'],
                ['field' => 'tipo_sucursal', 'as' => 'Tipo de sucursal'],
            ],
        ]);
    }

    public function movimientos() {
        $query = InventarioMovimiento::select(
            'inventario_movimiento.inicial',
            'inventario_movimiento.ingresos',
            'inventario_movimiento.egresos',
            'inventario_movimiento.precio',
            'inventario_movimiento.identificador',
            'inventario_movimiento.origen',
            'inventario_movimiento.secuencial_origen',
            'inventario_movimiento.observaciones',
            'inventario_movimiento.fecha',
            'inventario_movimiento.movimiento_id',
            'inventario_movimiento.inventario_id',
            'm.movimiento AS movimiento',
            'p.producto AS producto',
            's.nombres AS sucursal',
        )
        ->join('movimientos AS m', 'inventario_movimiento.movimiento_id', 'm.id')
        ->join('inventario AS i', 'inventario_movimiento.inventario_id', 'i.id')
        ->join('productos AS p', 'i.producto_id', 'p.id')
        ->join('sucursales AS s', 'i.sucursal_id', 's.id')
        ->withDate('fecha')
        ->filter()
        ->get();

        return $this->export([
            'title' => 'Reporte de movimientos',
            'data' => $query,
            'columns' => [
                ['field' => 'fecha', 'as' => 'Fecha'],
                ['field' => 'sucursal', 'as' => 'Sucursal'],
                ['field' => 'producto', 'as' => 'Producto'],
                ['field' => 'movimiento', 'as' => 'Movimiento'],
                ['field' => 'origen', 'as' => 'Origen'],
                ['field' => 'ingresos', 'as' => 'Ingresos'],
                ['field' => 'egresos', 'as' => 'Egresos'],
                ['field' => 'precio', 'as' => 'Precio'],
                ['field' => 'observaciones', 'as' => 'Observaciones'],
            ]
        ]);
    }

    public function preciosGenerales() {
        $query = PrecioGeneral::select(
            'precios_general.precio_venta',
            'precios_general.descuento_venta',
            'precios_general.precio_compra',
            'precios_general.descuento_compra',
            'precios_general.estado',
            'precios_general.producto_id',
            'precios_general.carga_precio_id',
            'p.producto AS producto',
            'cp.lugar_carga',
            'cp.estado',
            'cp.subido_por',
            'cp.autorizado_por',
            'cp.created_at AS fecha_carga',
        )
        ->join('productos AS p', 'precios_general.producto_id', 'p.id')
        ->join('carga_precios AS cp', 'precios_general.carga_precio_id', 'cp.id')
        ->filter()
        ->get();

        return $this->export([
            'title' => 'Reporte de precios generales',
            'data' => $query,
            'columns' => [
                ['field' => 'fecha_carga', 'as' => 'Fecha de carga'],
                ['field' => 'producto', 'as' => 'Producto'],
                ['field' => 'precio_venta', 'as' => 'Precio de venta'],
                ['field' => 'descuento_venta', 'as' => 'Descuento de venta'],
                ['field' => 'precio_compra', 'as' => 'Precio de compra'],
                ['field' => 'descuento_compra', 'as' => 'Descuento de compra'],
                ['field' => 'estado', 'as' => 'Estado'],
            ],
        ]);
    }



    public function kardex(Request $request) {
        try {
            if( !$request->has('sucursal_id') || !$request->has('producto_id') ) {
                throw new \Exception("sucursal_id y producto_id son requeridos.");
            }

            $producto = Producto::find($request->producto_id)->value('producto');
            $sucursal = Sucursal::find($request->sucursal_id)->value('nombres');

            $query = InventarioMovimiento::select(
                'inventario_movimiento.inicial',
                'inventario_movimiento.ingresos',
                'inventario_movimiento.egresos',
                'inventario_movimiento.precio',
                'inventario_movimiento.origen',
                'inventario_movimiento.secuencial_origen',
                'inventario_movimiento.fecha',
                'm.movimiento AS movimiento',
            )
            ->join('movimientos AS m', 'inventario_movimiento.movimiento_id', 'm.id')
            ->withDate('fecha')
            ->filter()
            ->get();

            $format = $request->query('format', 'pdf');

            if($format == 'pdf') {
                $pdf = Pdf::loadView('reports.kardex', [
                    'producto' => $producto,
                    'sucursal' => $sucursal,
                    'data' => $query,
                    'title' => 'Kardex de inventario',
                    'title' => 'Kardex de inventario',
                    'columns' => ['codigo_alternativo', 'producto',  'clasificacion', 'descripcion', 'estado', 'fecha_creacion']
                ])
                ->setPaper('letter');
                return $pdf->stream('kardex.pdf');
            }

            return (new ReportViewExport($query, []))->download("kardex.xlsx");
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}

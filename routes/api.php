<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\PermisoController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\RolPermisoController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/', function () {
    $data = [
        'company' => 'BAYOEX S.R.L.',
        'app' => 'Facturacion - Version 1',
        'version api' => 'v1',
    ];
    return response()->json($data);
});
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
});
Route::middleware('auth:api')->group(function () {

    Route::post('/sendResetEmail', [ForgotPasswordController::class, 'sendResetEmail']);
    Route::post('/resetPassword', [ResetPasswordController::class, 'passwordReset']);
    Route::post('/updatePassword', [UsuarioController::class, 'passwordChange']);

    Route::controller(UsuarioController::class)->prefix('usuarios')->group(function () {
        Route::get('/', 'index')->name('usuarios.index');
        Route::post('/', 'store')->name('usuarios.store');
        Route::get('/{idUsuario}', 'show')->name('usuarios.show');
        Route::match(['put', 'patch'], '/{usuario}', 'update')->name('usuarios.update');
        Route::delete('/{usuario}', 'destroy')->name('usuarios.destroy');
        Route::post('/update-tipo-impresion', 'updateTipoImpresion')->name('usuarios.updateTipoImpresion');
    });

    Route::controller(RolController::class)->prefix('roles')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('/{rol}', 'update')->name('roles.update');
        Route::post('/changeRol/{idUsuario}', 'cambiarRol');
        Route::delete('/{rol}', 'destroy');
    });

    Route::controller(PermisoController::class)->prefix('permisos')->group(function () {
        Route::get('/', 'index')->name('permisos.index');
        Route::post('/', 'store')->name('permisos.store');
        Route::put('/{permiso}', 'update')->name('permisos.update');
        Route::delete('/{permiso}', 'destroy')->name('permisos.destroy');
    });

    Route::controller(RolPermisoController::class)->prefix('rol-permiso')->group(function () {
        Route::get('/', 'index')->name('rol-permiso.index');
        Route::get('/{rol}', 'show')->name('rol-permiso');
        Route::post('/', 'assignPermission')->name('rol-permiso.store');
//    Route::put('/{permiso}', 'update')->name('rol-permiso.update');
//    Route::delete('/{permiso}', 'destroy')->name('rol-permiso.destroy');
    });

//Route::get('/rol-permiso', [RolPermisoController::class, 'index']);
//Route::post('/rol-permiso', [RolPermisoController::class, 'assignPermission']);



    Route::controller(\App\Http\Controllers\Api\AtributoController::class)->prefix('atributos')->group(function () {
        Route::get('/', 'index')->name('atributos.index');
        Route::post('/', 'store')->name('atributos.store');
        Route::get('/{atributo}', 'show')->name('atributos.show');
        Route::match(['put', 'patch'], '/{atributo}', 'update')->name('atributos.update');
        Route::delete('/{atributo}', 'destroy')->name('atributos.delete');
    });

    Route::controller(\App\Http\Controllers\Api\ClasificacionProductoController::class)->prefix('clasificacion-productos')->group(function () {
        Route::get('/', 'index')->name('clasificacion_productos.index');
        Route::post('/', 'store')->name('clasificacion_productos.store');
        Route::get('/{clasificacionProducto}', 'show')->name('clasificacion_productos.show');
        Route::match(['put', 'patch'], '/{clasificacionProducto}', 'update')->name('clasificacion_productos.update');
        Route::delete('/{clasificacionProducto}', 'destroy')->name('clasificacion_productos.delete');
    });

    Route::controller(\App\Http\Controllers\Api\ProcedenciaController::class)->prefix('procedencias')->group(function () {
        Route::get('/', 'index')->name('procedencias.index');
        Route::post('/', 'store')->name('procedencias.store');
        Route::get('/{procedencia}', 'show')->name('procedencias.show');
        Route::match(['put', 'patch'], '/{procedencia}', 'update')->name('procedencias.update');
        Route::delete('/{procedencia}', 'destroy')->name('procedencias.delete');
    });
    Route::controller(\App\Http\Controllers\Api\UnidadMedidaController::class)->prefix('unidad-medidas')->group(function () {
        Route::get('/', 'index')->name('unidad-medidas.index');
        Route::post('/', 'store')->name('unidad-medidas.store');
        Route::get('/{unidadMedida}', 'show')->name('unidad-medidas.show');
        Route::match(['put', 'patch'], '/{unidadMedida}', 'update')->name('unidad-medidas.update');
        Route::delete('/{unidadMedida}', 'destroy')->name('unidad-medidas.delete');
    });

    Route::controller(\App\Http\Controllers\Api\ProductoController::class)->prefix('productos')->group(function () {
        Route::get('/', 'index')->name('productos.index');
        Route::post('/', 'store')->name('productos.store');
        Route::get('/{producto}', 'show')->name('productos.show');
        Route::match(['put', 'patch'], '/{producto}', 'update')->name('productos.update');
        Route::delete('/{producto}', 'destroy')->name('productos.delete');
        Route::get('/exportaciones/plantilla', 'exportTemplate')->name('productos.export_template');
        Route::post('/importacion', 'import')->name('productos.import');
    });

    Route::controller(\App\Http\Controllers\Api\DepartamentoController::class)->prefix('departamentos')->group(function () {
        Route::get('/', 'index')->name('departamentos.index');
        Route::post('/', 'store')->name('departamentos.store');
        Route::get('/{departamento}', 'show')->name('departamentos.show');
        Route::match(['put', 'patch'], '/{departamento}', 'update')->name('departamentos.update');
        Route::delete('/{departamento}', 'destroy')->name('departamentos.delete');
    });


    Route::controller(\App\Http\Controllers\Api\AlmacenController::class)->prefix('almacenes')->group(function () {
        Route::get('/', 'index')->name('almacenes.index');
        Route::post('/', 'store')->name('almacenes.store');
        Route::get('/{almacen}', 'show')->name('almacenes.show');
        Route::match(['put', 'patch'], '/{almacen}', 'update')->name('almacenes.update');
        Route::delete('/{almacen}', 'destroy')->name('almacenes.delete');
    });

    Route::controller(\App\Http\Controllers\Api\SucursalController::class)->prefix('sucursales')->group(function () {
        Route::get('/', 'index')->name('sucursales.index');
        Route::post('/', 'store')->name('sucursales.store');
        Route::get('/{sucursal}', 'show')->name('sucursales.show');
        Route::match(['put', 'patch'], '/{sucursal}', 'update')->name('sucursales.update');
        Route::delete('/{sucursal}', 'destroy')->name('sucursales.delete');
        Route::get('/departamento/{departamento}', 'showSucursalesDepartamentoTipoCompra')->name('sucursales.showSucursalesDepartamentoTipoCompra');
    });

    Route::controller(\App\Http\Controllers\Api\MovimientoController::class)->prefix('movimientos')->group(function () {
        Route::get('/', 'index')->name('movimientos.index');
        Route::post('/', 'store')->name('movimientos.store');
        Route::get('/{movimiento}', 'show')->name('movimientos.show');
        Route::match(['put', 'patch'], '/{movimientoEdit}', 'update')->name('movimientos.update');
        Route::delete('/{movimiento}', 'destroy')->name('movimientos.delete');
    });

    Route::controller(\App\Http\Controllers\Api\CargaPrecioController::class)->prefix('carga-precios')->group(function () {
        Route::post('/importacion', 'import')->name('carga_precios.import');
        Route::post('/lote', 'storeMany')->name('carga_precios.store_many');
        Route::get('/exportaciones/plantilla', 'exportTemplate')->name('carga_precios.export_template');
    });

    Route::controller(\App\Http\Controllers\Api\PrecioGeneralController::class)->prefix('precios-generales')->group(function () {
        Route::get('/', 'index')->name('precios_generales.index');
        Route::post('/', 'store')->name('precios_generales.store');
        Route::get('/{precioGeneral}', 'show')->name('precios_generales.show');
        Route::match(['put', 'patch'], '/{precioGeneral}', 'update')->name('precios_generales.update');
        Route::delete('/{precioGeneral}', 'destroy')->name('precios_generales.delete');
    });

    Route::controller(\App\Http\Controllers\Api\InventarioController::class)->prefix('inventarios')->group(function () {
        Route::get('/', 'index')->name('inventarios.index');
        Route::post('/', 'store')->name('inventarios.store');
        Route::get('/{inventario}', 'show')->name('inventarios.show');
        Route::match(['put', 'patch'], '/{inventario}', 'update')->name('inventarios.update');
        Route::delete('/{inventario}', 'destroy')->name('inventarios.delete');
        //buscar producto para compras y traspasos
        Route::get('/buscarProductos/{sucursal}', 'buscarProductos')->name('inventarios.buscarProductos');
    });

    Route::controller(\App\Http\Controllers\Api\InventarioMovimientoController::class)->prefix('inventario-movimientos')->group(function () {
        Route::get('/', 'index')->name('inventario_movimientos.index');
        Route::post('/', 'store')->name('inventario_movimientos.store');
        Route::get('/{inventarioMovimiento}', 'show')->name('inventario_movimientos.show');
        Route::match(['put', 'patch'], '/{inventarioMovimiento}', 'update')->name('inventario_movimientos.update');
        Route::delete('/{inventarioMovimiento}', 'destroy')->name('inventario_movimientos.delete');
    });


// Registro de factura
    Route::controller(\App\Http\Controllers\Api\VentaController::class)->prefix('ventas')->group(function () {
        Route::get('/', 'index')->name('ventas.index');
        Route::get('/facturasEmitidas/sucursal/{sucursal}/puntoVenta/{puntoVenta}', 'facturasEmitidas')->name('ventas.facturasEmitidas');
        Route::post('/verificacionEstadoFactura', 'verificacionEstadoFactura')->name('ventas.verificacionEstadoFactura');
        Route::get('/verificacionComunicacionSiat', 'verificacionComunicacionSiat')->name('ventas.verificacionComunicacionSiat');
        Route::post('/', 'store')->name('ventas.store');
        Route::get('/{venta}', 'show')->name('ventas.show')
            ->where(['venta' => '[0-9]+']);

        Route::get('/tipo-venta/{tipoVenta}/cliente/{cliente}', 'showTipoVentaCliente')->name('ventas.showTipoVentaCliente');
        // Route::match(['put', 'patch'], '/{cuis}', 'update')->name('cuis.update');
        // Route::delete('/{cuis}', 'destroy')->name('cuis.delete');
        Route::get('/{cuf}/pdf/', 'pdfFactura')->name('ventas.pdfFactura')->withoutMiddleware('auth:api');

        Route::post('/reenviar-factura-email', 'reenviarFacturaEmail')->name('ventas.reenviarFacturaEmail');
        Route::get('/export', 'export')->name('ventas.export');

//    Route::get('qr-code-g', function () {\QrCode::size(500)->format('png')->generate('www.google.com', public_path('images/qrcode.png'));
//        return view('qrCode');
//    });

    });
//proformas
    Route::controller(\App\Http\Controllers\Api\ProformaController::class)->prefix('proformas')->group(function () {
        Route::get('/', 'index')->name('proformas.index');
        Route::post('/', 'store')->name('proformas.store');
        Route::get('/{proforma}', 'show')->name('proformas.show')
            ->where(['proforma' => '[0-9]+']);
        Route::match(['put', 'patch'], '/{proforma}', 'update')->name('proformas.update');
        Route::delete('/{proforma}', 'destroy')->name('proformas.delete');
        Route::get('/buscarProductos/', 'buscarProductos')->name('proformas.buscarProductos');
        Route::post('/buscar-producto-inventario/', 'buscarProductoInventario')->name('proformas.buscarProductoInventario');
        Route::get('/reporte', 'reporte')->name('proformas.reporte');
    });

    Route::controller(\App\Http\Controllers\Api\ReportController::class)->prefix('reportes')->group(function () {
        Route::get('/ventas', 'ventas')->name('reportes.ventas');
        Route::get('/compras', 'compras')->name('reportes.compras');
        Route::get('/facturas', 'facturas')->name('reportes.facturas');
        Route::get('/productos', 'productos')->name('reportes.productos');
        Route::get('/inventarios', 'inventarios')->name('reportes.inventarios');
        Route::get('/sucursales', 'sucursales')->name('reportes.sucursales');
        Route::get('/movimientos', 'movimientos')->name('reportes.movimientos');
        Route::get('/precios-generales', 'preciosGenerales')->name('reportes.precios_generales');
        Route::get('/proveedores', 'proveedores')->name('reportes.proveedores');
        Route::get('/kardex', 'kardex')->name('reportes.kardex');
    });
    //cafc
    Route::controller(\App\Http\Controllers\Api\CafcController::class)->prefix('cafc')->group(function () {
        Route::get('/', 'index')->name('cafc.index');
        Route::post('/', 'store')->name('cafc.store');
        Route::get('/{cafc}', 'show')->name('cafc.show')
            ->where(['proforma' => '[0-9]+']);
        Route::match(['put', 'patch'], '/{cafc}', 'update')->name('cafc.update');
        Route::delete('/{cafc}', 'destroy')->name('cafc.delete');
    });
// SIAT
//Autorizacion de sistemas
    Route::controller(\App\Http\Controllers\Api\AutorizacionSistemaController::class)->prefix('autorizacion-sistemas')->group(function () {
        Route::get('/', 'index')->name('autorizacion_sistemas.index');
        Route::post('/', 'store')->name('autorizacion_sistemas.store');
        Route::get('/{autorizacionSistema}', 'show')->name('autorizacion_sistemas.show');
        Route::match(['put', 'patch'], '/{autorizacionSistema}', 'update')->name('autorizacion_sistemas.update');
        Route::delete('/{autorizacionSistema}', 'destroy')->name('autorizacion_sistemas.delete');
    });
// Firma digital
    Route::controller(\App\Http\Controllers\Api\FirmaController::class)->prefix('firmas-digitales')->group(function () {
        Route::get('/', 'index')->name('firmas_digitales.index');
        Route::post('/', 'store')->name('firmas_digitales.store');
        Route::get('/{firma}', 'show')->name('firmas_digitales.show')->where(['firma' => '[0-9]+']);;
        // Route::match(['put', 'patch'], '/{firma}', 'update')->name('firmas_digitales.update');
        Route::delete('/{firma}', 'destroy')->name('firmas_digitales.delete');
        Route::get('/mostrar-firma-activo', 'mostrarFirmaActivo')->name('firmas-digitales.mostrarFirmaActivo');
    });
// Tokens delegados
    Route::controller(\App\Http\Controllers\Api\TokenDelegadoController::class)->prefix('token-delegados')->group(function () {
        Route::get('/', 'index')->name('token-delegados.index');
        Route::post('/', 'store')->name('token-delegados.store');
        Route::get('/{tokenDelegado}', 'show')->name('token-delegados.show')->where(['tokenDelegado' => '[0-9]+']);
        Route::match(['put', 'patch'], '/{tokenDelegado}', 'update')->name('token-delegados.update');
        Route::delete('/{tokenDelegado}', 'destroy')->name('token_delegados.delete');
        Route::get('/mostrar-token-activo', 'mostrarTokenActivo')->name('token-delegados.mostrarTokenActivo');
    });
// Punto de venta
    Route::controller(\App\Http\Controllers\Api\PuntoVentaController::class)->prefix('pos')->group(function () {
        Route::get('/', 'index')->name('pos.index');
//    Route::get('/sucursal/{sucursal}', 'index')->name('pos.index');
        Route::post('/', 'store')->name('pos.store');
        Route::get('/{puntoVenta}', 'show')->name('pos.show')
            ->where(['puntoVenta' => '[0-9]+']);
        Route::match(['put', 'patch'], '/{puntoVenta}', 'update')->name('pos.update');
        Route::delete('/{puntoVenta}', 'destroy')->name('pos.delete');
        Route::get('/registrados/{sucursal}', 'registered')->name('pos.registered');
        Route::post('/restauracion/{sucursal}', 'restore')->name('pos.restore');
        Route::get('/sucursal/{sucursal}/buscarProductosVenta/', 'buscarProductosVenta')->name('pos.buscarProductosVenta');
    });
    Route::controller(\App\Http\Controllers\Api\ClienteController::class)->prefix('clientes')->group(function () {
        Route::get('/', 'index')->name('clientes.index');
        Route::get('/buscarClienteVenta', 'buscarClienteVenta')->name('clientes.buscarClienteVenta');
        Route::post('/', 'store')->name('clientes.store');
        Route::get('/{cliente}', 'show')->name('clientes.show');
        Route::put('/{cliente}', 'update')->name('clientes.update')
            ->where(['cliente' => '[0-9]+']);
        Route::delete('/{cliente}', 'destroy')->name('clientes.destroy');
    });
// catalogos para facturacion, readOnly
    Route::controller(\App\Http\Controllers\Api\CatalogoFacturacionController::class)->prefix('catalogos')->group(function () {
        Route::get('/', 'index')->name('catalogos.index');
        Route::post('/', 'store')->name('catalogos.store');
        Route::get('/{catalogoFacturacion}', 'show')->name('catalogos.show');
        Route::match(['put', 'patch'], '/{catalogoFacturacion}', 'update')->name('catalogos.update');
        Route::delete('/{catalogoFacturacion}', 'destroy')->name('catalogos.delete');
    });

// CUIS
    Route::controller(\App\Http\Controllers\Api\CuisController::class)->prefix('cuis')->group(function () {
        Route::get('/', 'index')->name('cuis.index');
        Route::post('/', 'store')->name('cuis.store');
        Route::get('/{cuis}', 'show')->name('cuis.show');
        // Route::match(['put', 'patch'], '/{cuis}', 'update')->name('cuis.update');
        // Route::delete('/{cuis}', 'destroy')->name('cuis.delete');
    });
// CUFD
    Route::controller(\App\Http\Controllers\Api\CufdController::class)->prefix('cufd')->group(function () {
        Route::get('/', 'index')->name('cufd.index');
        Route::get('/estado', 'cufdStatus')->name('cufd.cufdStatus');
        Route::get('/fecha', 'cufdFecha')->name('cufd.cufdFecha');
        Route::get('/{cufd}', 'show')->name('cufd.show');

        Route::post('/', 'store')->name('cufd.store');
    });

// CUFD
    Route::controller(\App\Http\Controllers\Api\CufdController::class)->prefix('cufd')->group(function () {
        Route::get('/', 'index')->name('cufd.index');
        Route::post('/', 'store')->name('cufd.store');
        Route::get('/{cufd}', 'show')->name('cufd.show');
        // Route::match(['put', 'patch'], '/{cuis}', 'update')->name('cuis.update');
        // Route::delete('/{cuis}', 'destroy')->name('cuis.delete');
    });

// CUFD
    Route::controller(\App\Http\Controllers\Api\SincronizacionCatalogoController::class)->prefix('sincronizacion-catalogos')->group(function () {
        Route::get('/', 'index')->name('sincronizacion_catalogos.index');
        Route::post('/', 'store')->name('sincronizacion_catalogos.store');
        Route::get('/{sincronizacionCatalogo}', 'show')->name('sincronizacion_catalogos.show')
            ->where(['sincronizacionCatalogo' => '[0-9]+']);
        Route::post('/sync-all', 'syncAll')->name('sincronizacion_catalogos.sync_all');
        Route::post('/datetime', 'syncDateTime')->name('sincronizacion_catalogos.sync_datetime');

        Route::get('/catalogo-facturacion', 'catalogoFacturacion')->name('sincronizacion-catalogos.catalogoFacturacion');

    });

    Route::controller(\App\Http\Controllers\Api\ValorCatalogoController::class)->prefix('valores-catalogos')->group(function () {
        Route::get('/', 'index')->name('valores_catalogos.index');
    });

// Registro de factura
//Route::controller(\App\Http\Controllers\Api\VentaBackController::class)->prefix('ventas')->group(function() {
//    Route::get('/', 'index')->name('ventas.index');
//    Route::post('/', 'store')->name('ventas.store');
//    Route::get('/{venta}', 'show')->name('ventas.show');
//    // Route::match(['put', 'patch'], '/{cuis}', 'update')->name('cuis.update');
//    // Route::delete('/{cuis}', 'destroy')->name('cuis.delete');
//});

// Facturas
    Route::controller(\App\Http\Controllers\Api\FacturaController::class)->prefix('facturas')->group(function () {
        Route::get('/', 'index')->name('facturas.index');
        Route::get('/{factura}', 'show')->name('facturas.show');
    });

// Anulacion de factura
    Route::controller(\App\Http\Controllers\Api\AnulacionFacturaController::class)->prefix('anulacion-facturas')->group(function () {
        Route::get('/', 'index')->name('anulacion_facturas.index');
        Route::post('/', 'store')->name('anulacion_facturas.store');
        Route::get('/{anulacionFactura}', 'show')->name('anulacion_facturas.show');
    });

// Registro eventos significativos
    Route::controller(\App\Http\Controllers\Api\EventoSignificativoController::class)->prefix('eventos-significativos')->group(function () {
        Route::get('/', 'index')->name('eventos_significativos.index');
        Route::post('/', 'store')->name('eventos_significativos.store');
        Route::get('/{eventoSignificativo}', 'show')->name('eventos_significativos.show');
        Route::match(['put', 'patch'], '/{eventoSignificativo}', 'update')->name('eventos_significativos.update');
        Route::delete('/{eventoSignificativo}', 'destroy')->name('eventos_significativos.delete');
        // Transcripcion de facturas emitidas en contingencia
        Route::post('/{eventoSignificativo}/transcripcion-facturas', 'transcribe')->name('eventos_significativos.transcribe');
        //Registro en SIAT
        Route::post('/{eventoSignificativo}/registro', 'register')->name('eventos_significativos.register');
        //Vaidacion de paquetes
        Route::post('/{eventoSignificativo}/validacion', 'validateReception')->name('eventos_significativos.validate_reception');

        Route::get('/{eventoSignificativo}/consulta-evento-siat', 'getSignificatEvent')->name('eventos_significativos.getSignificatEvent');
        Route::post('/{eventoSignificativo}/registro-evento-siat', 'registerEventSignificant')->name('eventos_significativos.registerEventSignificant');

    });

//registro de facturacion electronica en linea
    Route::controller(\App\Http\Controllers\Api\EmisionPaqueteFacturaController::class)->prefix('emision-paquetes')->group(function () {
        Route::post('/recepcion-paquetes/{eventoSignificativo}', 'receptionElectronicInvoicePackage')->name('emision-paquetes.receptionElectronicInvoicePackage');
        Route::post('/validacion-paquetes/{eventoSignificativo}', 'validateReceptionInvoicePackage')->name('emision-paquetes.validateReceptionInvoicePackage');
    });

    Route::controller(\App\Http\Controllers\Api\EmisionMasivaFacturaController::class)->prefix('emisiones-masivas')->group(function () {
        Route::get('/', 'index')->name('emisiones-masivas.index');
        Route::post('/', 'store')->name('emisiones-masivas.store');
        Route::get('/{emisionMasiva}', 'show')->name('emisiones-masivas.show');
        Route::match(['put', 'patch'], '/{emisionMasiva}', 'update')->name('emisiones-masivas.update');
        // Transcripcion de facturas emitidas en masivo
        Route::post('/{emisionMasiva}/transcripcion-facturas', 'transcribe')->name('emisiones-masivas.transcribe');
        Route::post('/recepcion-masiva/{emisionMasiva}', 'receptionInvoiceMassive')->name('emisiones-masivas.receptionInvoiceMassive');
        Route::post('/validacion-masiva/{emisionMasiva}', 'validateReceptionInvoiceMassive')->name('emisiones-masivas.validateReceptionInvoiceMassive');
    });
// Homologacion de productos
    Route::controller(\App\Http\Controllers\Api\HomologacionProductoController::class)->prefix('homologacion-productos')->group(function () {
        Route::get('/', 'index')->name('homologacion_productos.index');
        Route::post('/', 'store')->name('homologacion_productos.store');
        Route::get('/{homologacionProducto}', 'show')->name('homologacion_productos.show');
        Route::match(['put', 'patch'], '/{homologacionProducto}', 'update')->name('homologacion_productos.update');
        Route::get('/exportaciones/plantilla', 'exportTemplate')->name('homologacion_productos.export_template');
        Route::post('/importacion', 'import')->name('homologacion_productos.import');
    });
    /**
     * SERVICIOS SOAP CODIGOS
     */
    Route::controller(\App\Http\Controllers\Api\VerificacionNitController::class)->prefix('verificacion-nit')->group(function () {
        Route::get('/', 'index')->name('verificacion-nit.index');
        Route::post('/', 'store')->name('verificacion-nit.store');
    });

    Route::controller(\App\Http\Controllers\Api\ConfiguracionSistemaController::class)->prefix('configuracion-sistema')->group(function () {
        Route::get('/mostrar-tipo-impresion', 'mostrarTipoImpresion')->name('configuracion-sistema.mostrarTipoImpresion');
        Route::match(['put', 'patch'], '/actualizar-tipo-impresion', 'actualizarTipoImpresion')->name('configuracion-sistema.actualizarTipoImpresion');
    });

    Route::controller(\App\Http\Controllers\Api\AlertasController::class)->prefix('alertas')->group(function () {
        Route::get('/alertas-siat', 'alertasSiat')->name('alertas.alertasSiat');
    });
});


/**
 * FIN SERVICIOS SOAP CODIGOS
 */

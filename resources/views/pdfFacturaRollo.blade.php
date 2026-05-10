<!DOCTYPE html>
<html>
<head>
    <title>Factura {{$cuf}}</title>
    <style>
        @page { margin-top: 5px; margin-bottom: 0px; margin_left: 4px; margin_right: 4px; }

        table {
            width: 100%;
            border: 0px solid #000;
            border-collapse: collapse;
            font-size: 10px;
            font-family: "Times New Roman", Times, serif;
            font-style: normal;

        }
        td {
            vertical-align: middle;
            /*text-align: left;*/
            border: 0px solid #808080;
            padding:2px 1px;
        }
        tr {
            vertical-align: middle;
            /*text-align: left;*/
            border: 0px solid #808080;
            padding:2px 1px;
        }
        th{
            width: 0%;
            border: 0px solid #808080;
            padding:2px 1px;
        }
        tdead{
            /*text-align: center;*/
            color: #000000;
            /*background: #a81a20;*/
        }
        .textCenter{
            text-align: center;
        }
        p {
            font-size: 12px;
            font-family: "Times New Roman", Times, serif;
        }
        hr {
            border-top: 1px dashed black;
        }

        .row{
            height: auto;
            width: 100%;
            font-size: 100%;
            top: -50px;
            left: -35px;
            right: -35px;
            /*width: 150px;*/
            /*margin-top: -20px;*/
            font-style: normal;
        }
        body { font-size: 100%;}
        .marca-agua {
            position: fixed; /* para que el div se fije en la posición especificada */
            top: 45%; /* ajusta el valor para posicionar la marca de agua verticalmente */
            transform: rotate(-45deg); /* para girar el texto de la marca de agua */
            opacity: 0.5; /* ajusta la opacidad para que la marca de agua sea semi-transparente */
            font-size: 4em; /* ajusta el tamaño del texto de la marca de agua */
            color: red; /* ajusta el color del texto de la marca de agua */
            z-index: -1000; /* asegura que el div de marca de agua se superponga sobre el contenido del informe */
        }
    </style>


</head>
<body>
<div id="pdf" class="row">
    @if($dataVenta->estado === 'ANULADO')
        <div class="marca-agua">ANULADO</div>
    @endif

    <p class="textCenter">FACTURA <br>CON  DERECHO A CREDITO FISCAL <br>
        {{$dataSistema->razon_social}}
    </p>
    <p class="textCenter">
        {{$dataFacturaVentaSucursalPos->venta->sucursal->nombres}} <br>
        No Punto de Venta  {{$dataFacturaVentaSucursalPos->venta->pos->codigo_siat}} <br>
        {{$dataFacturaVentaSucursalPos->venta->sucursal->direccion}} <br>
        Telf. {{$dataFacturaVentaSucursalPos->venta->sucursal->telefono}}<br>
        La Paz
    </p>
    <hr>
    <p class="textCenter">
        NIT <br>
        {{$dataSistema->nit}} <br>
        FACTURA Nº <br>
        {{$dataVenta->codigo_secuencia}}<br>
        CÓD AUTORIZACIÓN <br>

        @php
            $array = str_split($dataFacturaVentaSucursalPos->cuf,40);
            echo $array[0].'<br>'.$array[1];
//            dd($array);
//            echo str_split($array,29)
        @endphp
    </p>
    <hr>
    <table>
        <tr>
            <td style="width: 48%">NOMBRE/RAZON SOCIAL</td>
            <td style="width: 4%">:</td>
            <td style="width: 48%; text-align: left">{{$dataFacturaVentaSucursalPos->razon_social}} </td>
        </tr>
        <tr>
            <td style="width: 48%">NIT/CI/CEX </td>
            <td style="width: 4%">:</td>
            <td style="width: 48%; text-align: left">{{$dataCliente->cliente->cedula_nit}} {{$dataCliente->cliente->complemento}} </td>
        </tr>
        <tr>
            <td style="width: 48%">COD. CLIENTE</td>
            <td style="width: 4%">:</td>
            <td style="width: 48%; text-align: left">{{$dataCliente->cliente->id}}  </td>
        </tr>
        <tr>
            <td style="width: 48%">    FECHA de EMISION</td>
            <td style="width: 4%">:</td>
            <td style="width: 48%; text-align: left">{{\Carbon\Carbon::parse($dataCliente->fecha)->format('d/m/Y - H:i:s')}} </td>
        </tr>
    </table>
    <hr>

    <table>
        <tr class="textCenter">
            <td colspan="2">DETALLE</td>
        </tr>

        @foreach($dataVenta->inventarios as $inventario)
            <tr>
                <td colspan="2" >
                    {{$inventario->producto->id}} - {{$inventario->producto->descripcion}}  <br>
                </td>
            </tr>
            <tr>
                <td style="width: 80%;"> {{number_format($inventario->pivot->cantidad, 2, '.', ',')}} X {{number_format($inventario->pivot->precio, 2, '.', ',')}}  - {{number_format( ($inventario->pivot->cantidad*$inventario->pivot->precio)*($inventario->pivot->descuento/100), 2, '.', ',')}}</td>
                <td style="width: 20%;text-align:right">{{number_format($inventario->pivot->sub_total, 2, '.', ',')}}</td>
            </tr>
        @endforeach

        <!--Totales -->
        <tr><th colspan="2">  <hr></th></tr>
        <tr>
            <td  style="width: 80%;text-align:right">SUBTOTAL Bs</td>
            <td style="width: 20%;text-align:right">{{number_format($dataVenta->total + $dataVenta->descuento, 2, '.', ',')}}</td>
        </tr>
        <tr>
            <td  style="width: 80%;text-align:right">DESCUENTO Bs</td>
            <td style="width: 20%;text-align:right">{{number_format($dataVenta->descuento, 2, '.', ',')}}</td>
        </tr>
        <tr>
            <td  style="width: 80%;text-align:right">TOTAL Bs</td>
            <td style="width: 20%;text-align:right">{{ number_format($dataVenta->total, 2, '.', ',')}}</td>
        </tr>
        <tr>
            <td  style="width: 80%;text-align:right">MONTO GIFT CARD Bs</td>
            <td style="width: 20%;text-align:right">0.00</td>
        </tr>
        <tr>
            <th style="width: 80%;text-align:right">MONTO A PAGAR Bs</th>
            <th style="width: 20%;text-align:right">{{ number_format($dataVenta->total, 2, '.', ',')}}</th>
        </tr>
        <tr>
            <th  style="width: 80%;text-align:right">IMPORTE BASE CRÉDITO FISCAL Bs</th>
            <th style="width: 20%;text-align:right">{{ number_format($dataVenta->total, 2, '.', ',')}}</th>
        </tr>
        <tr>
            <td colspan="2" style="text-align:left; font-size: 14px; padding-top: 4px">
              Son:  {{$numeroLiteral}}
            </td>
        </tr>

    </table>
    <hr>
    <p class="textCenter">
        ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY.
    </p>
    <p class="textCenter">{{$dataFacturaVentaSucursalPos->leyenda}}</p>
{{--    <p class="textCenter">“Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea"</p>--}}
    <p class="textCenter">{{'"'.$leyendaRepresentacionGrafica.'"'}}</p>
    <div style="text-align: center;" >
        <img src="{{ public_path("qrcode/".$cuf.".svg") }}" />
    </div>
</div>

</body>

</html>

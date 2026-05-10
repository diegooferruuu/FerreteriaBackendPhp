

<!DOCTYPE html>
<html>
<head>
    <title>Factura {{$cuf}}</title>
    <style>
        /*@page {*/
        /*    margin: 1cm;*/
        /*}*/
        @page { margin-top: 0.7cm; }
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
        .row{

        }
        .textCenter{
            text-align: center;
        }

        p {
            font-size: 12px;
            font-family: "Times New Roman", Times, serif;
        }
        span {
            font-size: 12px;
            font-family: "Times New Roman", Times, serif;
        }
        hr {
            border-top: 1px dashed black;
        }

        body {
            margin: 0 0 0 0;
        }


        .tableProductos {
            width: 100%;
            border: 0px solid #000;
            border-collapse: collapse;
            font-size: 10px;
            font-family: "Times New Roman", Times, serif;
            font-style: normal;
        }
        .tableProductos td {
            vertical-align: middle;

            /*text-align: left;*/
            border: 1px solid #000;
            padding:2px 1px;
        }
        .tableProductos tr {
            vertical-align: middle;
            /*text-align: left;*/
            border: 0px solid #000;
            padding:2px 1px;
        }
        .tableProductos th{
            width: 0%;
            border: 1px solid #000;
            padding:2px 1px;
        }
        .contenido {
            margin-top: 0px;
            /*padding-top: -20px;*/
        }
        .marca-agua {
            position: fixed; /* para que el div se fije en la posición especificada */
            top: 45%; /* ajusta el valor para posicionar la marca de agua verticalmente */
            left: 20%; /* ajusta el valor para posicionar la marca de agua horizontalmente */
            transform: rotate(-45deg); /* para girar el texto de la marca de agua */
            opacity: 0.5; /* ajusta la opacidad para que la marca de agua sea semi-transparente */
            font-size: 6em; /* ajusta el tamaño del texto de la marca de agua */
            color: red; /* ajusta el color del texto de la marca de agua */
            z-index: -1000; /* asegura que el div de marca de agua se superponga sobre el contenido del informe */
        }
    </style>

</head>
<body>
@if($dataVenta->estado === 'ANULADO')
    <div class="marca-agua">ANULADO</div>
@endif
<div class="row">


    <table>
            <tr>
                <td style=" text-align: left; width: 33%;">
                    <div style="text-align: center; width: 85%;">
                        @if(!is_null($dataSistema->logo))
                            <img src="{{'storage/'.$dataSistema->logo}}" alt="" class="img-fluid" width="200" height="65">
                        @endif
                        <span>
                       <span style="font-size: 10px"> {{$dataSistema->razon_social}}</span><br>
                        {{$dataFacturaVentaSucursalPos->venta->sucursal->nombres}}<br>
{{--                        No Punto de Venta  {{$dataFacturaVentaSucursalPos->venta->pos->codigo_siat}}<br>--}}
                        {{$dataFacturaVentaSucursalPos->venta->sucursal->direccion}} <br>
                        Telf. {{$dataFacturaVentaSucursalPos->venta->sucursal->telefono}}<br>
                        {{$dataFacturaVentaSucursalPos->venta->sucursal->departamento->departamento}}<br>
                        </span>
                    </div>

                </td>
                <td style="padding-right: 20px">
                    <div style="text-align: center; padding-bottom: 8px">
                        <span><strong style="font-size: 20px"> FACTURA</strong> <br> (Con Derecho a Crédito Fiscal)<br></span>
                    </div>
                </td>
                <td style="width: 35%;">

                    <table style="font-size: 12px">
                        <tr>
                            <td style="width: 50%;"><b> NIT:</b></td>
                            <td>{{$dataSistema->nit}}</td>
                        </tr>
                        <tr>
                            <td><b>FACTURA Nª:</b></td>
                            <td>{{$dataVenta->codigo_secuencia}}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: text-top"><b>CÓDIGO AUTORIZACIÓN:</b></td>
                            <td>
                                @php
                                    $cufArray = str_split($dataFacturaVentaSucursalPos->cuf,20);
                                    foreach ($cufArray as $dataCuf) {
                                            echo $dataCuf.'<br>';
                                    }
                                @endphp
                            </td>
                        </tr>
                    </table>
                    <br><br>

                </td>

            </tr>
        </table>

    <div class="contenido">

        <table style="font-size: 12px">
            <tr>
                <td style="width: 75%">   <b> Fecha:</b> {{\Carbon\Carbon::parse($dataCliente->fecha)->format('d/m/Y - H:i:s')}}</td>
                <td style="width: 25%; text-align: left"><b> NIT/CI/CEX:</b> {{$dataCliente->cliente->cedula_nit}} {{$dataCliente->cliente->complemento}}  </td>
            </tr>
            <tr>
                <td style="width: 75%"><b>Nombre/Razón Social:</b> {{$dataFacturaVentaSucursalPos->razon_social}}  </td>
                <td style="width: 25%; text-align: left"> <b>Cod- cliente:</b> {{$dataCliente->cliente->id}}  </td>
            </tr>

        </table>

        <table class="tableProductos" >
            <tr>
                <th>CÓDIGO <br> PRODUCTO /<br>SERVICIO</th>
                <th>CANTIDAD</th>
                <th>UNIDAD DE <br> MEDIDA</th>
                <th>DESCRIPCIÓN</th>
                <th>PRECIO <br> UNITARIO</th>
                <th>DESCUENTO</th>
                <th>SUBTOTAL</th>
            </tr>
            @php

            @endphp
            @foreach($dataVenta->inventarios as $inventario)
                <tr>
                        <td style="text-align: center">{{$inventario->producto->id}} </td>
                        <td style="text-align: right">{{number_format($inventario->pivot->cantidad, 2, '.', ',')}}</td>
                        <td style="text-align: center">{{$inventario->producto->unidadMedida->valorCatalogo->descripcion }} </td>
                        <td> {{$inventario->producto->descripcion}} </td>
                        <td style="text-align: right">{{number_format($inventario->pivot->precio, 2, '.', ',')}}</td>
                        <td style="text-align: right">{{number_format($inventario->pivot->descuento, 2, '.', ',')}}</td>
                        <td style="text-align: right">{{number_format($inventario->pivot->sub_total, 2, '.', ',')}}</td>
                </tr>

            @endforeach
            <tr style=" border: 0px solid #808080;" >
                <td style="border: 0px !important;" colspan="4" rowspan="6"> <b>  Son: {{$numeroLiteral}}</b></td>
                <td colspan="2" style="text-align: right">SUBTOTAL Bs</td>
                <td style="text-align: right" >{{number_format(($dataVenta->total + $dataVenta->descuento), 2, '.', ',')}}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right">DESCUENTO Bs</td>
                <td style="text-align: right">{{number_format($dataVenta->descuento, 2, '.', ',')}}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right">TOTAL Bs</td>
                <td style="text-align: right">{{ number_format($dataVenta->total, 2, '.', ',')}}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right">MONTO GIFT CARD Bs</td>
                <td style="text-align: right">0.00</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right"><b>MONTO A PAGAR Bs </b></td>
                <td style="text-align: right">{{ number_format($dataVenta->total, 2, '.', ',')}}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right"><b> IMPORTE BASE CRÉDITO FISCAL Bs</b></td>
                <td style="text-align: right">{{ number_format($dataVenta->total, 2, '.', ',')}}</td>
            </tr>


        </table>

    </div>

    <table>
        <tr>
            <td>
                <p class="textCenter">
                    ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY.
                </p>
                <p class="textCenter">{{$dataFacturaVentaSucursalPos->leyenda}}</p>
                <p class="textCenter">{{'"'.$leyendaRepresentacionGrafica.'"'}}</p>
            </td>
            <td>
                <div style="text-align: center">
                    <img src="{{ public_path("qrcode/".$cuf.".svg") }}" />
                </div>

            </td>
        </tr>
    </table>


</div>
<script type="text/php">

    if (isset($pdf)) {
        $x = 580;
        $y = 760;
        $text = "{PAGE_NUM}/{PAGE_COUNT}";
        $font = null;
        $size = 10;
        $color = array(0,0,0);
        $word_space = 0.0;  //  default
        $char_space = 0.0;  //  default
        $angle = 0.0;   //  default
        $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
    }

</script>
</body>

</html>


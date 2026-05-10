<!DOCTYPE html>
<html>
<head>
    <title>Proforma {{$datosProforma->codigo_secuencia}}</title>
    <style>
        @page { margin-top: 0.5cm; }
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

            top: -50px;
            left: -35px;
            right: -35px;
            height: auto;
            font-style: normal;
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

        body { height: auto;}
        span {
            font-size: 12px;
            font-family: "Times New Roman", Times, serif;
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
@if($datosProforma->estado === 'ANULADO')
    <div class="marca-agua">ANULADO</div>
@endif
<div class="row">


    <table style="padding-top: 5px">
        <tr>
            <td style=" text-align: left; width: 35%">
                <div style="text-align: center; width: 85%;">

                    @if(!is_null($logo))
{{--                        <img src="{{'storage/'.$dataSistema->logo}}" alt="" class="img-fluid" width="180px" height="65">--}}
                        <img src="{{$logo}}" alt="" class="img-fluid" width="200px" height="65">
                    @endif
                        <br>
                        <span>
                        {{$datosProforma->sucursal->direccion}} <br>
                        Telf. {{$datosProforma->sucursal->telefono}}<br>
                        {{$datosProforma->sucursal->departamento->departamento}} - Bolivia<br>
                        </span>
                </div>
            </td>
            <td style="padding-right: 20px">
                <div style="text-align: center; padding-bottom: 8px">
                    <span><b style="font-size: 25px"> PRO-FORMA</b></span>
                </div>
            </td>
            <td style="width: 35%;" >
                <br>
                <p style="margin-left: 30%">
                    <b> Fecha: </b> {{\Carbon\Carbon::parse($datosProforma->fecha)->format('d/m/Y - H:i')}} <br>
                    <b>Nro Proforma: </b> {{$datosProforma->codigo_secuencia}}<br>
                    <b>VALIDEZ: </b> {{\Carbon\Carbon::parse($datosProforma->vigencia)->format('d-m-Y')}}
                </p>
            </td>

        </tr>
    </table>

    <div class="contenido">
        <table style="font-size: 12px; padding-top: 5px">
            <tr>
                <td style="width: 75%"><b>Señor(es):</b> {{$datosProforma->cliente->razon_social}}  </td>
                <td style="width: 25%; text-align: left"><b> NIT/CI/CEX:</b> {{$datosProforma->cliente->cedula_nit}} </td>
            </tr>
        </table>

        <table class="tableProductos" >
            <tr>
                <th>CÓDIGO <br> PRODUCTO /<br>SERVICIO</th>
                <th>CANTIDAD</th>
   <th>UNIDAD DE <br> MEDIDA</th>
                <th>DESCRIPCIÓN</th>
                <th>PRECIO</th>
                <th>DESCUENTO</th>
                <th>SUBTOTAL</th>
            </tr>
            @php

                @endphp
            @foreach($datosProforma->productos as $productos)
                <tr>
                    <td style="text-align: center">{{$productos->id}} </td>
                    <td style="text-align: right">{{number_format($productos->pivot->cantidad, 2, '.', ',')}}</td>
                    <td style="text-align: center">{{$productos->unidadMedida->valorCatalogo->descripcion }} </td>
                    <td> {{$productos->descripcion}} </td>
                    <td style="text-align: right">{{number_format($productos->pivot->precio, 2, '.', ',')}}</td>
                    <td style="text-align: right">{{number_format($productos->pivot->descuentoMonto, 2, '.', ',')}}</td>
                    <td style="text-align: right">{{number_format($productos->pivot->sub_total, 2, '.', ',')}}</td>
                </tr>

            @endforeach
            <tr style=" border: 0px solid #808080;" >
                <td style="border: 0px !important;" colspan="4" rowspan="6"> <b> Son: {{$numeroLiteral}}</b></td>
                <td colspan="2" style="text-align: right">SUBTOTAL Bs</td>
                <td style="text-align: right" >{{number_format(($datosProforma->total + $datosProforma->descuentoMonto), 2, '.', ',')}}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right">DESCUENTO Bs</td>
                <td style="text-align: right">{{number_format($datosProforma->descuentoMonto, 2, '.', ',')}}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right">TOTAL Bs</td>
                <td style="text-align: right">{{ number_format($datosProforma->total, 2, '.', ',')}}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right"><b>MONTO A PAGAR Bs </b></td>
                <td style="text-align: right">{{ number_format($datosProforma->total, 2, '.', ',')}}</td>
            </tr>

        </table>

    </div>

    <table>
        <tr>
            <td>
                <p style="text-align: left"> <b>Descripción:</b> {{$datosProforma->descripcion }}</p>
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

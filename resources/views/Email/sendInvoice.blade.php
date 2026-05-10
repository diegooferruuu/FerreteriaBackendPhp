<!DOCTYPE html>

<html>
<head>
    <title>Mensaje enviado</title>
</head>
<body>
<h3>FACTURACIÓN - FAC v1</h3>
<p> <b>Estimado/a: </b> {{$factura['razon_social']}} <br>

<p>La Empresa <b> Ferretería America</b> ha generado su factura Nro. {{$venta['codigo_secuencia']}} con fecha de emisión {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i')}}.  </p>
<p>Al presente correo, se adjunta la Representación Gráfica y el archivo XML. </p>
<p>No responder a este correo.</p>

</body>
</html>

<!DOCTYPE html>

<html>
<head>
    <title>Mensaje enviado</title>
</head>
<body>
<p> <b>Estimado/a: </b> {{$dataFactura->razon_social}} <br>

<p>La Empresa <b> Ferretería America</b> te notifica la anulacion de la factura Nro. {{$dataFactura->venta->codigo_secuencia}}, con codigo de autorizacion {{$dataFactura->cuf}} correspondiente a fecha de emisión {{ \Carbon\Carbon::parse($dataFactura->venta->fecha)->format('d/m/Y H:i')}}.  </p>
<p>Si tienes alguna consulta respecto a tus facturas, por favor envia un correo a <a href="mailto:correo@ferreteriaamerica.com" target="_blank">correo@ferreteriaamerica.com</a>  </p>

</body>
</html>

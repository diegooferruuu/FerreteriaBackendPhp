<table class="table table-bordered">
    <thead>
        <tr>
            <th rowspan="2">FECHA</th>
            <th rowspan="2">DETALLE</th>
            <th rowspan="2">PRECIO UNITARIO</th>
            <th colspan="2">ENTRADAS</th>
            <th colspan="2">SALIDAS</th>
            <th colspan="1">SALDOS</th>
        </tr>
        <tr>
            <th>CANT.</th>
            <th>TOTAL</th>
            <th>CANT.</th>
            <th>TOTAL</th>
            <th>CANT.</th>
            {{--  <th>TOTAL</th>  --}}
        </tr>
    </thead>
    <tbody>
        @php
            $saldo = 0;
        @endphp
        @foreach ($data as $item)
            @php
                $saldo = $saldo + $item->ingresos - $item->egresos;
                $subtotalIngreso = $item->precio * $item->ingresos;
                $subtotalEgreso = $item->precio * $item->egresos;
            @endphp   
            <tr>
                <td>{{ $item->fecha }}</td>
                <td>{{ $item->movimiento }}</td>
                <td class="text-right">{{ $item->precio }}</td>
                <td class="text-right">{{ $item->ingresos }}</td>
                <td class="text-right">{{ $subtotalIngreso == 0 ? '' : $subtotalIngreso }}</td>
                <td class="text-right">{{ $item->egresos }}</td>
                <td class="text-right">{{ ( $subtotalEgreso == 0 ? '' : $subtotalEgreso ) }}</td>
                <td class="text-right">{{ $saldo }}</td>
                {{--  <td>{{ $item->precio * $saldo }}</td>  --}}
            </tr>
        @endforeach
    </tbody>
</table>
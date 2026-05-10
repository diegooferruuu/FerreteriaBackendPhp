<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>{{$title}}</title>
        <style>
            @page {
                margin: 0cm 0cm;
                font-family: Arial;
            }

            body {
                margin: 2cm;
            }

            .text-uppercase {
                text-transform: uppercase !important;
            }

            .text-right {
                text-align: right !important;
            }

            .text-center {
                text-align: center !important;
            }

            .table {
                width: 100%;
                margin-bottom: 1rem;
                color: #212529;
                border-collapse: collapse;
            }
              
            .table th,
            .table td {
                padding: 0.3rem;
                vertical-align: top;
                border-top: 1px solid #dee2e6;
                white-space: nowrap;
                font-size: 11px;
                color: #363636;
            }
              
            .table thead th {
                vertical-align: bottom;
                border-bottom: 2px solid #dee2e6;
            }
              
            .table tbody + tbody {
                border-top: 2px solid #dee2e6;
            }
              
            .table-bordered {
                border: 1px solid #dee2e6;
            }
              
            .table-bordered th,
            .table-bordered td {
                border: 1px solid #dee2e6;
            }
              
            .table-bordered thead th,
            .table-bordered thead td {
                border-bottom-width: 2px;
            }

            .head{
                width:100% !important;
                height: 4rem;
            }
            .head > div{
                float: left !important;
                width:33.3333% !important;
            }

            .filters {
                width: 100% !important;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <div>
            <div class="head">
                <div>
                    <img src="{{ public_path('logo_emapa.png') }}" alt="Logo" height="60">
                </div>
                <div>
                    <h4 class="text-uppercase text-center">{{ $title }}</h4>
                </div>
                <div class="text-right">
                    <small><b>Usuario: </b>User</small></br>
                    <small><b>Fecha actual: </b>{{ now()->format('d-m-Y H:i') }}</small>
                </div>
            </div>
            <hr>
            {{--  <div class="filters">
                <small><b>filter: </b>value</small>
            </div>  --}}
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-uppercase">Nro.</th>
                        @foreach ($columns as $column)
                            <th class="text-uppercase">{{ $column['as'] ?? Str::of($column['field'])->headline() }}</th>
                        @endforeach     
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->chunk(200) as $group)
                        @foreach ($group as $key => $item)  
                            <tr>
                                <td>{{ ($key + 1) }}</td>
                                @foreach ($columns as $column)
                                    @php
                                        $value = null;
                                        $class = isset($column['class']) ? $column['class'] : '';
                                        $style = isset($column['style']) ? $column['style'] : '';
                                        if( isset($column['type']) ) {
                                            switch( $column['type'] ) {
                                                case 'number':
                                                    $value = number_format($item->{$column['field']}, 0, '.', ',');
                                                    $class .= Str::contains($class, 'text-right') ? '' : ' text-right';
                                                    break;
                                                case 'money':
                                                    $value = number_format($item->{$column['field']}, 2, '.', ',');
                                                    $class .=  Str::contains($class, 'text-right') ? '' : ' text-right';
                                                    break;
                                                default:
                                                    $value = $item->{$column['field']};
                                            }
                                        } else {
                                            $value = $item->{$column['field']};
                                        }
                                    @endphp
                                    <td 
                                        class="{{ $class }}"
                                        style="{{ $style }}"
                                    >
                                        {{ $value }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <script type="text/php">
            if ( isset($pdf) ) {
                $pdf->page_script('
                    $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                    $pdf->text(270, 730, "Pagina $PAGE_NUM de $PAGE_COUNT", $font, 10);
                ');
            }
        </script>
    </body>
</html>
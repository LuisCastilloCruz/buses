<style>

    .bordered {
        border: 1px solid black;
        border-collapse: collapse;
    }

    .bordered td{
        border: 1px solid black;
    }

    .table-border{
        border: 1px solid black;
        border-collapse: collapse;
    }

    .aqpfact-color:{
        background-color: #003c71 !important;
    }

</style>
<br><br>
<table style="width: 100%;font-size:11px">
    <tr>
        <td style="width: 33%;text-align: center">
            <strong>TRANSPORTES Y TURISMO ANGELES
                TOUR JUL PERU S.A.C. </strong> <br> 
            <span style="font-size: 6px">
                AV. LIMA NRO. 749 (EN ANTENA TELEFONICA FACTORIA SAN LUIS)
                AREQUIPA - CAMANA
            </span>   
        </td>
        <td style="width: 10% !important;text-align: center">
            <table class="bordered" style="width: 30%;font-size:8px;">
                <tr style="background-color: #0088cc;">
                    <td style="text-align: center;color:white">DIA</td>
                    <td style="text-align: center;color:white">MES</td>
                    <td style="text-align: center;color:white">AÑO</td>
                </tr>
                <tr>
                    <?php  $fecha = \Carbon\Carbon::parse($manifiesto->fecha);  ?>
                    <td style="text-align: center">{{ $fecha->day }}</td>
                    <td style="text-align: center">{{ $fecha->month < 10 ? '0'.$fecha->month : $fecha->month  }}</td>
                    <td style="text-align: center">{{ $fecha->year }}</td>
                </tr>
            </table>
            <table class="bordered" style="width: 30%;font-size:8px;margin-top:3px">
                <tr style="background-color: #0088cc;">
                    <td colspan="3" style="text-align: center;color:white">HORA</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center;">{{ $manifiesto->hora }}</td>
                </tr>
            </table>
        </td>

        <td style="width: 40%">
            <table class="bordered" style="width: 100%">
                <tr>
                    <td style="width: 100%;text-align:center">
                        <h1>R.U.C.20600665431</h3>
                    </td>
                </tr>
                <tr style="background-color: #0088cc;">
                    <td style="width: 100%;text-align:center;color:white">
                        <h2>MANIFIESTO DE PASAJEROS</h2>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align:center">
                        <h4>004-02090</h4>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table style="width: 100%;font-size:11px;margin-top:20px">
    <tr>
        <td style="width: 33%">
            <table style="width: 100%">
                <tr>
                    <td style="width: 10px">
                        <strong>Origen:</strong>
                    </td>
                    <td style="text-align: left;border-bottom:1px solid black">
                        {{ $programacion->origen->nombre }}
                    </td>
                    <td style="width: 10px"> <strong>Destino: </strong></td>
                    <td style="text-align: left;border-bottom:1px solid black">
                        {{ $programacion->destino->nombre }}
                    </td>
                </tr>
            
            </table>
            <table style="width: 100%;margin-top:20px">
                <tr>
                    <td style="width: 110px"><strong>Marca de vehículo: </strong></td>
                    <td style="text-align: left;border-bottom:1px solid black">
                       {{ $vehiculo->nombre }}
                    </td>
                    <td style="width: 10px"><strong>Placa:</strong></td>
                    <td style="text-align: left;border-bottom:1px solid black">
                        {{ $vehiculo->placa }}
                    </td>
                </tr>
            
            </table>
        
        </td>
        <td style="width: 33%"></td>
        
    </tr>
</table>

<?php $chofer = $manifiesto->chofer; ?>
<?php $copiloto = $manifiesto->copiloto; ?>
<table style="width: 100%;font-size:11px;margin-top:20px">

    <tr>

        <td>

            <table class="table-border" style="width: 100%">
                <tr>
                    <td> <strong>Conductor:</strong></td>
                    <td style="text-align: right"><strong>Nro de Licencia:</strong></td>
                </tr>
                <tr>
                    
                    <td>1.- {{ $chofer->nombre }}</td>
                    <td style="text-align: right">{{ $chofer->licencia }}</td>
                </tr>
            </table>

        </td>
        <td>

            <table class="table-border" style="width: 100%">
                <tr>
                    <td> <strong>Conductor:</strong></td>
                    <td style="text-align: right"><strong>Nro de Licencia:</strong></td>
                </tr>
                <tr>
                    <td>2.- {{ $copiloto->nombre }}</td>
                    <td style="text-align: right">{{ $copiloto->licencia }}</td>
                </tr>
            </table>

        </td>
    </tr>

</table>

<table style="width: 100%;font-size:9px;margin-top:20px">
    <tr>
        <td style="width: 115px">Cantidad Max de Asientos: </td>
        <td style="border: 1px solid black;width:30px;text-align:center"> 
            {{ $vehiculo->asientos }}
        </td>
        <td style="width: 112px">Ocupados por Tripulación</td>
        <td style="border: 1px solid black;width:30px;text-align:center">
            {{ count($pasajes) }}
        </td>
        <td style="width: 155px">Pasajero embarcados en el terminal:</td>
        <td style="border: 1px solid black;width:30px;text-align:center">
            {{ $pasajesEnTerminal }}
        </td>
        <td style="width: 155px">Cantidad de pasajeros recogidos en ruta: </td>
        <td style="border: 1px solid black;width:30px;text-align:center">
            {{ $pasajesRecogidosRuta }}
        </td>
    </tr>
</table> 

<table class="bordered" style="width: 100%;font-size:9px;margin-top:20px;text-align:center">
    <tr>
        <td><strong>Item</strong></td>
        <td> <strong>NOMBRES Y APELLIDOS</strong> </td>
        <td> <strong>DESTINO</strong> </td>
        <td> <strong>TIPO DOC.</strong> </td>
        <td> <strong>Nº DE DOC</strong> </td>
        <td> <strong>Nº ASIENTO</strong> </td>
        <td> <strong> SERIE Nº BOLETO</strong></td>
        <td> <strong>IMPORTE S/</strong> </td>
    </tr>

    @foreach ($pasajes as $pasaje)
        <?php $pasajero = $pasaje->pasajero; ?>
        <?php $document = $pasaje->document; ?>
        <tr>
            <td>{{ $pasaje->id }}</td>
            <td>{{ $pasajero->name }}</td>
            <td>{{ $pasaje->programacion->destino->nombre }}</td>
            <td>{{ $pasajero->identity_document_type->description }}</td>
            <td>{{ $document->number  }}</td>
            <td>{{ $pasaje->asiento->numero_asiento }}</td>
            <td>{{ $document->series }} - {{ $document->number }}</td>
            <td>{{  number_format($pasaje->precio,2,'.','')   }}</td>
        </tr>
        
    @endforeach
</table>


<table style="width: 100%;font-size:10px;margin-top:20px">

    <tr>
        <td style="width: 100px">OBSERVACIONES: </td>
        <td style="border-bottom:1px solid black">
            {{ $manifiesto->observaciones }}
        </td>
    </tr>

</table>


<table style="width: 100%;font-size:10px;margin-top:60px">

    <tr>
        <td style="width: 50%;text-align:center">
            <table style="width: 250px">
                <tr>
                    <td style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td>CHOFER</td>
                </tr>
            </table>
            
        </td>
        <td style="width: 50%;text-align:center">
            <table style="width: 250px">
                <tr>
                    <td style="border-bottom:1px solid black"></td>
                </tr>
                <tr>
                    <td>COPILOTO</td>
                </tr>
            </table>
        </td>
    </tr>

</table>
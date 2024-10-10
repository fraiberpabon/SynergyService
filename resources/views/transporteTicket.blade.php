<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Synergy transport voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="container-fluid bg-white">
    @if (isset($transport))
        <div style="padding-top: 16px bg-white">
            @foreach ($transport as $key)
                <div class="card bg-white p-2 m-2">
                    @if (!empty($key['voucher']))
                        <h4>{{ $key['voucher'] }}</h4>
                    @endif

                    <div class="row bg-white">
                        <div class="col-12 col-md-6 bg-white">
                            <h6>Datos principales</h6>
                            @if (!empty($key['tipo']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Tipo:</strong>
                                    </div>
                                    <div class="col">
                                        @if ($key['tipo'] == '1')
                                            <p>Llegada</p>
                                        @else
                                            <p>Salida</p>
                                        @endif

                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['solicitud']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Solicitud:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['solicitud'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['fechaRegistro']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Fecha:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['fechaRegistro'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['tipo']) && $key['tipo'] == '2')
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Origen:</strong>
                                    </div>
                                    <div class="col">
                                        @if (!empty($key['plantaOrigen']))
                                            <p>{{ $key['plantaOrigen'] }}</p>
                                        @else
                                            <p>
                                                Frente: {{ $key['tramoOrigen'] }} Zona: {{ $key['hitoOrigen'] }}
                                                @if (!empty($key['abscisaOrigen']))
                                                    Abscisa: {{ $key['abscisaOrigen'] }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['tipo']) && $key['tipo'] == '1')
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Destino:</strong>
                                    </div>
                                    <div class="col">
                                        @if (!empty($key['plantaDestino']))
                                            <p>{{ $key['plantaDestino'] }}</p>
                                        @else
                                            <p>
                                                Frente: {{ $key['tramoDestino'] }} Zona:
                                                {{ $key['hitoDestino'] }}
                                                @if (!empty($key['abscisaDestino']))
                                                    Abscisa: {{ $key['abscisaDestino'] }}
                                                @endif
                                            </p>
                                        @endif

                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['costCenter']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>WBE destino:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['costCenter'] }}</p>
                                    </div>
                                </div>
                            @endif


                        </div>

                        <div class="col-12 col-md-6 bg-white">
                            @if (!empty($key['material']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Material:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['material'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['formula']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Formula:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['formula'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['cantidad']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Cantidad:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['cantidad'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['creadoPor']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Creado por:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['creadoPor'] }}</p>
                                    </div>
                                </div>
                            @endif


                            <h6>Informacion del vehiculo</h6>

                            @if (!empty($key['equipo']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Equipo:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['equipo'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['placa']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Placa:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['placa'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['cubicaje']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Cubicaje (m³):</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['cubicaje'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['contratista']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Contratista:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['contratista'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['chofer']))
                                <div class="row bg-white">
                                    <div class="col">
                                        <strong>Chofer:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['chofer'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>

                    @if (!empty($key['observacion']))
                        <div class="row bg-white">
                            <div class="col-12">
                                <strong>Observaciones:</strong>
                            </div>
                            <div class="col-12">
                                <p>{{ $key['observacion'] }}</p>
                            </div>
                        </div>
                    @endif


                </div>
            @endforeach




        </div>
    @else
        <h1>Registro no ha sido sincronizado</h1>
    @endif

</body>

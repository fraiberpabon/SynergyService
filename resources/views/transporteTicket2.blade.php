<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Synergy transport voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        /* Estilo base para el timeline */
        .timeline {
            position: relative;
            margin: 0 auto;
            padding: 20px 0;
        }

        /* Línea central del timeline */
        .timeline::before {
            content: '';
            position: absolute;
            width: 4px;
            background-color: #f8f9fa !important;
            top: 0;
            bottom: 0;
            z-index: 1;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        /* Punto del timeline */
        .timeline-item::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: #0d6efd !important;
            border-radius: 50%;
            left: 49%;
            z-index: 2;
            /* Para que el punto esté sobre la línea */
        }

        /* Línea que conecta los puntos */
        .timeline-item::after {
            content: '';
            position: absolute;
            width: 4px;
            background-color: #0d6efd;
            top: 38px;
            /* Colocar después del punto azul */
            bottom: 0px;
            /* Estirar hacia abajo */
            left: 49.4%;
            z-index: 1;
        }

        /* Evitar la línea en el último punto */
        .timeline-item:last-child::after {
            display: none;
        }

        /* Contenido del timeline */
        .timeline-item-content {
            background-color: #f8f9fa !important;
            border-radius: 8px;
            position: relative;
            width: 45%;
            z-index: 3;
            /* Para que el contenido esté sobre el punto */
        }

        .timeline-item.left .timeline-item-content {
            left: 0;
        }

        .timeline-item.right .timeline-item-content {
            left: 55%;
        }

        /* Flecha de los cuadros de texto */
        .timeline-item-content::before {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-style: solid;
        }

        .timeline-item.left .timeline-item-content::before {
            /*border-color: transparent #f8f8fa transparent transparent;*/
            border-color: transparent transparent transparent transparent;
        }

        .timeline-item.right .timeline-item-content::before {
            /*border-color: transparent transparent transparent #f8f9fa;*/
            border-color: transparent transparent transparent transparent;
        }

        /* Estilos responsive para pantallas móviles */
        @media (max-width: 768px) {
            .timeline::before {
                left: auto;
                right: 2px;
                /* Mueve la línea del timeline a la derecha */
            }

            .timeline-item::before {
                left: auto;
                right: 2px;
                /* Mueve el punto azul a la derecha */
            }

            .timeline-item::after {
                left: auto;
                right: 8px;
                /* Mueve el punto azul a la derecha */
            }

            .timeline-item-content {
                width: calc(100% - 30px);
                left: auto !important;
                /* Ajusta el contenido hacia la derecha */
            }

            .timeline-item-content::before {
                border-color: transparent transparent transparent transparent !important;
            }
        }
    </style>
</head>

<body class="container-fluid mt-2">
    @if (isset($transport))
        <div class="">
            <div class="card bg-white p-2 mb-3">
                @if (sizeof($transport) > 1)
                    <div class="row p-1">
                        <div class="col-12 col-lg-6">
                            @if (!empty($transport[1]['voucher']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Voucher:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['voucher'] }}</p>
                                    </div>
                                </div>
                            @endif


                            @if (!empty($transport[1]['material']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Material:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['material'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[1]['formula']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Formula:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['formula'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[1]['solicitud']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Solicitud:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['solicitud'] }}</p>
                                    </div>
                                </div>
                            @endif

                        </div>
                        <div class="col-12 col-lg-6">

                            @if (!empty($transport[1]['equipo']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Equipo:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['equipo'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[1]['placa']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Placa:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['placa'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[1]['cubicaje']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Cubicaje (m³):</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['cubicaje'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[1]['contratista']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Contratista:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['contratista'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[1]['chofer']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Chofer:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[1]['chofer'] }}</p>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                @else
                    <div class="row p-1">
                        <div class="col-12 col-lg-6">
                            @if (!empty($transport[0]['voucher']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Voucher:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['voucher'] }}</p>
                                    </div>
                                </div>
                            @endif


                            @if (!empty($transport[0]['material']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Material:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['material'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[0]['formula']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Formula:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['formula'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[0]['solicitud']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Solicitud:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['solicitud'] }}</p>
                                    </div>
                                </div>
                            @endif

                        </div>
                        <div class="col-12 col-lg-6">

                            @if (!empty($transport[0]['equipo']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Equipo:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['equipo'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[0]['placa']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Placa:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['placa'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[0]['cubicaje']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Cubicaje (m³):</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['cubicaje'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[0]['contratista']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Contratista:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['contratista'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($transport[0]['chofer']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Chofer:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $transport[0]['chofer'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
            @foreach ($transport as $key)
                <div class="timeline-item {{ $loop->index % 2 == 0 ? 'left' : 'right' }}">
                    <div class="card timeline-item-content p-1">
                        <div class="p-2 bg-white">

                            @if (!empty($key['fechaProgramacion']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Fecha Programacion:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['fechaProgramacion'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['solicitante']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Solicitante:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['solicitante'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['tipo']))
                                <div class="row">
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



                            @if (!empty($key['cantidad']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Cantidad:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['cantidad'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['fechaRegistro']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Fecha:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['fechaRegistro'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['tipo']) && $key['tipo'] == '2')
                                <div class="row">
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
                                <div class="row">
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
                                <div class="row">
                                    <div class="col">
                                        <strong>WBE destino:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['costCenter'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($key['creadoPor']))
                                <div class="row">
                                    <div class="col">
                                        <strong>Creado por:</strong>
                                    </div>
                                    <div class="col">
                                        <p>{{ $key['creadoPor'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if (!empty($key['observacion']))
                            <div class="row">
                                <div class="col-12">
                                    <strong>Observaciones:</strong>
                                </div>
                                <div class="col-12">
                                    <p>{{ $key['observacion'] }}</p>
                                </div>
                            </div>
                        @endif
                    </div>


                </div>
            @endforeach

        </div>
    @else
        <h1>Registro no ha sido sincronizado</h1>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

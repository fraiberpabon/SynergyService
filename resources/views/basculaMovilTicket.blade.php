<!DOCTYPE html>

@php
    $lang = request()->getPreferredLanguage(['es', 'en', 'it']);
    app()->setLocale($lang);
@endphp

<html lang="{{ str_replace('_', '-', $lang) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>{{ __('messages.title_ticket_bascula') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <style>
        .timeline-content {
            text-align: left !important;
            max-width: 700px;
        }

        .description_machine {
            padding-left: 3rem;
        }
    </style>
</head>
<div class="row">
    <div class="col">

        <body class="container mt-2" style="max-width: 1140px;">

            @if (isset($card))
                <div class="bg-white mb-3 description_machine">
                    <x-bladewind::card>
                        <div class="row p-2">
                            <div class="col-12 col-md-11">

                                @if (!empty($card['equipo']))
                                    <div class="row">
                                        <div class="col-12 col-sm-6">
                                            <strong>{{ __('messages.equipo_card') }}</strong>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <p>{{ $card['equipo'] }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($card['placa']))
                                    <div class="row">
                                        <div class="col-12 col-sm-6">
                                            <strong>{{ __('messages.placa_card') }}</strong>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <p>{{ $card['placa'] }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($card['contratista']))
                                    <div class="row">
                                        <div class="col-12 col-sm-6">
                                            <strong>{{ __('messages.contratista_card') }}</strong>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <p>{{ $card['contratista'] }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($card['conductor']))
                                    <div class="row">
                                        <div class="col-12 col-sm-6">
                                            <strong>{{ __('messages.chofer_card') }}</strong>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <p>{!! nl2br($card['conductor']) !!}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-bladewind::card>
                </div>
            @endif



            @if (isset($scale))
                {{-- @php
                    $posicion = sizeof($scale) > 1 ? 2 : 0;
                @endphp --}}
                <div class="justify-content-center">
                    <x-bladewind::timeline-group anchor="big" completed="true" color="blue" position="left"
                        stacked="true">
                        @php
                            $mostrarElemento = true;

                        @endphp

                        @foreach ($scale as $key)
                            @php
                                $alignLeft = 'false'; //$loop->index % 2 == 0 ? 'true' : 'false';
                                $icon = !empty($key['tipo'])
                                    ? ($key['tipo'] == '1'
                                        ? 'inbox-arrow-down'
                                        : 'truck')
                                    : '';
                                $last = $loop->index + 1 == sizeof($scale) ? 'true' : false;
                                // $last = false;
                            @endphp

                            @if (!empty($key['tipo']))
                                <x-bladewind::timeline align_left="{{ $alignLeft }}" icon="{{ $icon }}"
                                    last="{{ $last }}">
                                    <x-slot:content>
                                        <div class="timeline-content">
                                            <x-bladewind::card style="margin-bottom: 20px;">
                                                @if (!empty($key['voucher']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.numero_boucher_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <p>{{ $key['voucher'] }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (!empty($key['tipo']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.tipo_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            @if ($key['tipo'] == '1')
                                                                <p>{{ __('messages.llegada') }}</p>
                                                            @else
                                                                <p>{{ __('messages.salida') }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!empty($key['plantaOrigen']) || !empty($key['otroOrigen']) || !empty($key['tramoOrigen']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.origen_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            @if (!empty($key['plantaOrigen']))
                                                                <p>{{ $key['plantaOrigen'] }}</p>
                                                            @elseif (!empty($key['otroOrigen']))
                                                                <p>{{ $key['otroOrigen'] }}</p>
                                                            @elseif (!empty($key['tramoOrigen']))
                                                                <p>
                                                                    {{ __('messages.frente_card') }}
                                                                    {{ $key['tramoOrigen'] }}
                                                                    {{ __('messages.zona_card') }}
                                                                    {{ $key['hitoOrigen'] }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif


                                                @if (!empty($key['costCenterOrigen']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.wbe_origen_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <p>{{ $key['costCenterOrigen'] }}</p>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!empty($key['plantaDestino']) || !empty($key['otroDestino']) || !empty($key['tramoDestino']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.destino_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            @if (!empty($key['plantaDestino']))
                                                                <p>{{ $key['plantaDestino'] }}</p>
                                                            @elseif (!empty($key['otroDestino']))
                                                                <p>{{ $key['otroDestino'] }}</p>
                                                            @elseif (!empty($key['tramoDestino']))
                                                                <p>
                                                                    {{ __('messages.frente_card') }}
                                                                    {{ $key['tramoDestino'] }}
                                                                    {{ __('messages.zona_card') }}
                                                                    {{ $key['hitoDestino'] }}
                                                                </p>
                                                            @endif

                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!empty($key['costCenterDestino']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.wbe_destino_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <p>{{ $key['costCenterDestino'] }}</p>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!empty($card['material']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.material_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <p>{{ $card['material'] }}</p>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!empty($card['formulaTipo']))
                                                    @if ($card['formulaTipo'] == 'M')
                                                        @if (!empty($card['formula']))
                                                            <div class="row">
                                                                <div class="col-12 col-sm-6">
                                                                    <strong>{{ __('messages.formula_card') }}</strong>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <p>{{ $card['formula'] }}</p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @else
                                                        @if (!empty($card['formulaAsf']))
                                                            <div class="row">
                                                                <div class="col-12 col-sm-6">
                                                                    <strong>{{ __('messages.formula_card') }}</strong>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <p>{{ $card['formulaAsf'] }}</p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @else
                                                    @if (!empty($card['formula']))
                                                        <div class="row">
                                                            <div class="col-12 col-sm-6">
                                                                <strong>{{ __('messages.formula_card') }}</strong>
                                                            </div>
                                                            <div class="col-12 col-sm-6">
                                                                <p>{{ $card['formula'] }}</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif

                                                @if (!empty($key['pesoNeto']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.pesoNeto_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <p>{{ $key['pesoNeto'] }} {{ __('messages.kg') }}</p>
                                                        </div>
                                                    </div>
                                                @endif



                                                @if (!empty($key['observacion']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.observacion_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <p>{{ $key['observacion'] }}</p>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!empty($key['creadoPor']))
                                                    <div class="row">
                                                        <div class="col-12 col-sm-6">
                                                            <strong>{{ __('messages.creado_por_card') }}</strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <p>{{ $key['creadoPor'] }}</p>
                                                        </div>
                                                    </div>
                                                @endif

                                            </x-bladewind::card>

                                            @if (!empty($key['pesoNeto']))
                                                @php
                                                    $peso1Mayor = $key['peso1'] > $key['peso2'];
                                                @endphp
                                                <div>
                                                    <div class="row">
                                                        <x-bladewind::card class="col m-2 p-2" style="min-width: 240px">
                                                            <div class="row p-0">
                                                                @if ($peso1Mayor)
                                                                    <svg viewBox="0 -0.5 17 17" version="1.1"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        stroke-width="1.5" stroke="currentColor"
                                                                        class="col size-6">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <path
                                                                                d="M12.867,3.039 L11.944,3.039 C11.978,2.887 12,2.719 12,2.531 C12,1.135 10.877,0 9.495,0 C8.113,0 6.99,1.135 6.99,2.531 C6.99,2.719 7.016,2.886 7.058,3.039 L6.136,3.039 C5.011,3.039 4.099,3.922 4.099,5.01 L2.083,13.985 C2.083,15.075 2.873,15.957 4,15.957 L15,15.957 C16.126,15.957 16.917,15.075 16.917,13.985 L14.905,5.01 C14.905,3.922 13.993,3.039 12.867,3.039 Z M7.824,2.531 C7.824,1.582 8.573,0.808 9.495,0.808 C10.416,0.808 11.165,1.581 11.165,2.531 C11.165,2.709 11.131,2.877 11.082,3.039 L7.906,3.039 C7.857,2.877 7.824,2.709 7.824,2.531 L7.824,2.531 Z M10.054,10.08 L10.054,13.039 L8.946,13.039 L8.946,10.101 L6.813,10.08 L9.543,7.02 L12.107,10.08 L10.054,10.08 L10.054,10.08 Z"
                                                                                fill="#434343" class="si-glyph-fill">

                                                                            </path>
                                                                        </g>
                                                                    </svg>
                                                                @else
                                                                    <svg viewBox="0 0 16 16" version="1.1"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        class="col size-6">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <path
                                                                                d="M12.905,5.01 C12.905,3.922 11.993,3.039 10.867,3.039 L9.944,3.039 C9.978,2.887 10,2.719 10,2.531 C10,1.135 8.877,-4.54747351e-13 7.495,-4.54747351e-13 C6.113,-4.54747351e-13 4.99,1.135 4.99,2.531 C4.99,2.719 5.016,2.886 5.058,3.039 L4.136,3.039 C3.011,3.039 2.099,3.922 2.099,5.01 L0.083,13.985 C0.083,15.075 0.995,15.957 2.122,15.957 L12.88,15.957 C14.006,15.957 14.917,15.075 14.917,13.985 L12.905,5.01 L12.905,5.01 Z M5.824,2.531 C5.824,1.582 6.573,0.808 7.495,0.808 C8.416,0.808 9.165,1.581 9.165,2.531 C9.165,2.709 9.131,2.877 9.082,3.039 L5.906,3.039 C5.857,2.877 5.824,2.709 5.824,2.531 L5.824,2.531 Z M6.963,9.947 L6.963,6.958 L8.037,6.958 L8.037,9.926 L10.107,9.947 L7.459,13.039 L4.969,9.947 L6.963,9.947 L6.963,9.947 Z"
                                                                                fill="#434343" class="si-glyph-fill">
                                                                            </path>
                                                                        </g>
                                                                    </svg>
                                                                @endif

                                                                <div class="col-9 p-0">
                                                                    @if (!empty($key['peso1']))
                                                                        <div class="row">
                                                                            <div class="col-12 col-sm-6">
                                                                                <strong>{{ __('messages.peso1_card') }}</strong>
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <p>{{ $key['peso1'] }}
                                                                                    {{ __('messages.kg') }}</p>
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                    @if (!empty($key['fechaRegistro']))
                                                                        <div class="row">
                                                                            {{-- <div class="col-12">
                                                                                <strong>{{ __('messages.fecha_card') }}</strong>
                                                                            </div> --}}
                                                                            <div class="col-12">
                                                                                <p>{{ $key['fechaRegistro'] }}</p>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </x-bladewind::card>

                                                        <x-bladewind::card class="col m-2 p-2"
                                                            style="min-width: 240px">
                                                            <div class="row p-0">
                                                                @if ($peso1Mayor)
                                                                    <svg viewBox="0 0 16 16" version="1.1"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        class="col size-6">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <path
                                                                                d="M12.905,5.01 C12.905,3.922 11.993,3.039 10.867,3.039 L9.944,3.039 C9.978,2.887 10,2.719 10,2.531 C10,1.135 8.877,-4.54747351e-13 7.495,-4.54747351e-13 C6.113,-4.54747351e-13 4.99,1.135 4.99,2.531 C4.99,2.719 5.016,2.886 5.058,3.039 L4.136,3.039 C3.011,3.039 2.099,3.922 2.099,5.01 L0.083,13.985 C0.083,15.075 0.995,15.957 2.122,15.957 L12.88,15.957 C14.006,15.957 14.917,15.075 14.917,13.985 L12.905,5.01 L12.905,5.01 Z M5.824,2.531 C5.824,1.582 6.573,0.808 7.495,0.808 C8.416,0.808 9.165,1.581 9.165,2.531 C9.165,2.709 9.131,2.877 9.082,3.039 L5.906,3.039 C5.857,2.877 5.824,2.709 5.824,2.531 L5.824,2.531 Z M6.963,9.947 L6.963,6.958 L8.037,6.958 L8.037,9.926 L10.107,9.947 L7.459,13.039 L4.969,9.947 L6.963,9.947 L6.963,9.947 Z"
                                                                                fill="#434343" class="si-glyph-fill">

                                                                            </path>
                                                                        </g>
                                                                    </svg>
                                                                @else
                                                                    <svg viewBox="0 -0.5 17 17" version="1.1"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        stroke-width="1.5" stroke="currentColor"
                                                                        class="col size-6">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <path
                                                                                d="M12.867,3.039 L11.944,3.039 C11.978,2.887 12,2.719 12,2.531 C12,1.135 10.877,0 9.495,0 C8.113,0 6.99,1.135 6.99,2.531 C6.99,2.719 7.016,2.886 7.058,3.039 L6.136,3.039 C5.011,3.039 4.099,3.922 4.099,5.01 L2.083,13.985 C2.083,15.075 2.873,15.957 4,15.957 L15,15.957 C16.126,15.957 16.917,15.075 16.917,13.985 L14.905,5.01 C14.905,3.922 13.993,3.039 12.867,3.039 Z M7.824,2.531 C7.824,1.582 8.573,0.808 9.495,0.808 C10.416,0.808 11.165,1.581 11.165,2.531 C11.165,2.709 11.131,2.877 11.082,3.039 L7.906,3.039 C7.857,2.877 7.824,2.709 7.824,2.531 L7.824,2.531 Z M10.054,10.08 L10.054,13.039 L8.946,13.039 L8.946,10.101 L6.813,10.08 L9.543,7.02 L12.107,10.08 L10.054,10.08 L10.054,10.08 Z"
                                                                                fill="#434343" class="si-glyph-fill">

                                                                            </path>
                                                                        </g>
                                                                    </svg>
                                                                @endif

                                                                <div class="col-9 p-0">
                                                                    @if (!empty($key['peso2']))
                                                                        <div class="row">
                                                                            <div class="col-12 col-sm-6">
                                                                                <strong>{{ __('messages.peso2_card') }}</strong>
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <p>{{ $key['peso2'] }}
                                                                                    {{ __('messages.kg') }}</p>
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                    @if (!empty($key['fechaRegistroPeso2']))
                                                                        <div class="row">
                                                                            {{-- <div class="col-12">
                                                                                <strong>{{ __('messages.fecha_card') }}</strong>
                                                                            </div> --}}
                                                                            <div class="col-12">
                                                                                <p>{{ $key['fechaRegistroPeso2'] }}</p>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </x-bladewind::card>
                                                    </div>
                                                </div>

                                                {{-- <x-bladewind::timeline-group anchor="small" completed="true"
                                                    orientation="horizontal" color="blue" position="left"
                                                    stacked="true">

                                                    <x-bladewind::timeline align_left="false" icon="false">
                                                        <x-slot:content>
                                                            <x-bladewind::card>
                                                                <div class="timeline-content">
                                                                    @if (!empty($key['peso1']))
                                                                        <div class="row">
                                                                            <div class="col-12 col-sm-6">
                                                                                <strong>{{ __('messages.peso1_card') }}</strong>
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <p>{{ $key['peso1'] }}
                                                                                    {{ __('messages.kg') }}</p>
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                    @if (!empty($key['fechaRegistro']))
                                                                        <div class="row">
                                                                            <div class="col-12 col-sm-6">
                                                                                <strong>{{ __('messages.fecha_card') }}</strong>
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <p>{{ $key['fechaRegistro'] }}</p>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </x-bladewind::card>

                                                        </x-slot:content>
                                                    </x-bladewind::timeline>

                                                    <x-bladewind::timeline align_left="false" icon="false"
                                                        last="true">
                                                        <x-slot:content>
                                                            <x-bladewind::card>
                                                                <div class="timeline-content">
                                                                    @if (!empty($key['peso2']))
                                                                        <div class="row">
                                                                            <div class="col-12 col-sm-6">
                                                                                <strong>{{ __('messages.peso2_card') }}</strong>
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <p>{{ $key['peso2'] }}
                                                                                    {{ __('messages.kg') }}</p>
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                    @if (!empty($key['fechaRegistroPeso2']))
                                                                        <div class="row">
                                                                            <div class="col-12 col-sm-6">
                                                                                <strong>{{ __('messages.fecha_card') }}</strong>
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <p>{{ $key['fechaRegistroPeso2'] }}</p>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </x-bladewind::card>

                                                        </x-slot:content>
                                                    </x-bladewind::timeline>
                                                </x-bladewind::timeline-group> --}}
                                            @endif
                                        </div>
                                    </x-slot:content>
                                </x-bladewind::timeline>
                            @endif
                        @endforeach
                </div>
                </x-bladewind::timeline-group>
            @else
                <div class="container mt-3" style="max-width: 800px;">
                    <x-bladewind::empty-state show_image="true"
                        heading="{{ __('messages.ups_registro_no_encontrado') }}"
                        message="{{ __('messages.msg_no_ha_sincronizado_transporte') }}" class="shadow-sm p-3"
                        image="{{ asset('imagenes/ITsolucionesLogo.svg') }}">
                    </x-bladewind::empty-state>
                </div>
            @endif
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
            <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
            <script src="//unpkg.com/alpinejs" defer></script>
        </body>
    </div>
    <div class="col">
        @if (isset($card))
            <x-bladewind::card title="{{ __('messages.recorrido_transporte') }}" class="p-2 p-sm-4">
                <div id="map" style="height: 400px; width: 100%; border-radius: 14px;" ></div>
                <script>
                    const map = L.map('map').setView([51.505, -0.09], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                    }).addTo(map);

                    const waypoints = [];
                    const tooltipInfo = [];
                    @foreach ($scale as $key)
                        @php
                            if (isset($key['tipo']) && $key['tipo'] == '2' && isset($key['equipo'])) {
                                $salida = $key['plantaOrigen'] ? "<p>{$key['plantaOrigen']}</p>" : '<p>' . __('messages.frente_card') . (!empty($key['tramoOrigen']) ? $key['tramoOrigen'] : __('messages.default_tramo')) . '<br>' . __('messages.zona_card') . (!empty($key['hitoOrigen']) ? $key['hitoOrigen'] : __('messages.default_hito')) . '</p>';
                                $entrada = explode(';', $ubicacion_salida);
                                if (count($entrada) === 2) {
                                    echo "waypoints.push(L.latLng({$entrada[0]}, {$entrada[1]}));";
                                    echo "tooltipInfo.push('" . __('messages.salida') . ": {$salida}');";
                                }
                            }

                            if (isset($key['tipo']) && $key['tipo'] == '1' && isset($key['equipo'])) {
                                $salidas_info = $key['plantaDestino'] ? "<p>{$key['plantaDestino']}</p>" : '<p>' . __('messages.frente_card') . (!empty($key['tramoDestino']) ? $key['tramoDestino'] : __('messages.default_tramo')) . '<br>' . __('messages.zona_card') . (!empty($key['hitoDestino']) ? $key['hitoDestino'] : __('messages.default_hito')) . '</p>';
                                $salidas = explode(';', $ubicacion_entrada);
                                if (count($salidas) === 2) {
                                    echo "waypoints.push(L.latLng({$salidas[0]}, {$salidas[1]}));";
                                    echo "tooltipInfo.push('" . __('messages.llegada') . ": {$salidas_info}');";
                                    echo "tooltipInfo.push('0');";
                                }
                            }
                        @endphp
                    @endforeach
                    const redIcon = L.icon({
                        iconUrl: 'https://mapmarker.io/api/v3/font-awesome/v6/pin?text=L&size=85&color=FFF&background=ed6464&hoffset=0&voffset=0',
                        iconSize: [40, 41],
                        iconAnchor: [18, 41],
                        popupAnchor: [1, -34],
                    });

                    const greenIcon = L.icon({
                        iconUrl: 'https://mapmarker.io/api/v3/font-awesome/v6/pin?text=S&size=85&color=FFF&background=5aba45&hoffset=0&voffset=0',
                        iconSize: [40, 41],
                        iconAnchor: [18, 41],
                        popupAnchor: [1, -34],
                    });
                    if (waypoints.length === 2) {
                        L.Routing.control({
                            waypoints: waypoints,
                            routeWhileDragging: true,
                            language: '{{ app()->getLocale() }}',
                        }).addTo(map);

                        map.fitBounds(L.latLngBounds(waypoints));

                        waypoints.forEach((point, index) => {
                            const icon = index === 0 ? greenIcon : redIcon;
                            const marker = L.marker(point, {
                                icon
                            }).addTo(map);
                            marker.bindTooltip(tooltipInfo[index]);
                        });
                    } else if (waypoints.length === 1) {
                        map.setView(waypoints[0], 13);
                        const icon = tooltipInfo.includes('0') ? redIcon : greenIcon;
                        L.marker(waypoints[0], {
                            icon
                        }).addTo(map).bindTooltip(tooltipInfo[0]);
                    } else {
                        alert("{{ __('messages.no_puntos_validos') }}");
                    }
                </script>

            </x-bladewind::card>
        @endif
    </div>
</div>

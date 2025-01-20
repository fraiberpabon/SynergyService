<!DOCTYPE html>

@php
    $lang = request()->getPreferredLanguage(['es', 'en', 'it']);
    app()->setLocale($lang);
@endphp

<html lang="{{ str_replace('_', '-', $lang) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>{{ __('messages.title_ticket_transport') }}</title>
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

        .description_machine{
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
                                   <div class="col">
                                       <strong>{{ __('messages.equipo_card') }}</strong>
                                   </div>
                                   <div class="col">
                                       <p>{{ $card['equipo'] }}</p>
                                   </div>
                               </div>
                           @endif
   
                           @if (!empty($card['placa']))
                               <div class="row">
                                   <div class="col">
                                       <strong>{{ __('messages.placa_card') }}</strong>
                                   </div>
                                   <div class="col">
                                       <p>{{ $card['placa'] }}</p>
                                   </div>
                               </div>
                           @endif
   
                           @if (!empty($card['cubicaje']))
                               <div class="row">
                                   <div class="col">
                                       <strong>{{ __('messages.cubicaje_m3_card') }}</strong>
                                   </div>
                                   <div class="col">
                                       <p>{{ $card['cubicaje'] }}</p>
                                   </div>
                               </div>
                           @endif
   
                           @if (!empty($card['contratista']))
                               <div class="row">
                                   <div class="col">
                                       <strong>{{ __('messages.contratista_card') }}</strong>
                                   </div>
                                   <div class="col">
                                       <p>{{ $card['contratista'] }}</p>
                                   </div>
                               </div>
                           @endif
   
                           @if (!empty($card['chofer']))
                               <div class="row">
                                   <div class="col">
                                       <strong>{{ __('messages.chofer_card') }}</strong>
                                   </div>
                                   <div class="col">
                                       <p>{{ $card['chofer'] }}</p>
                                   </div>
                               </div>
                           @endif
   
                       </div>
                   </div>
               </x-bladewind::card>
           </div>
           @endif
      
           @if (isset($transport))
           @php
           $posicion = sizeof($transport) > 1 ? 1 : 0;
       @endphp
           <div class="justify-content-center">
               <x-bladewind::timeline-group stacked="false" anchor="big" completed="true" color="blue" position="left"
                   stacked="true">
                   @php
                        $mostrarElemento =true;
                   @endphp
                   @foreach ($transport as $key)
                       @php
                           $alignLeft = 'false'; //$loop->index % 2 == 0 ? 'true' : 'false';
                           $icon = !empty($key['tipo']) ? ($key['tipo'] == '1' ? 'inbox-arrow-down' : 'truck') : '';
                           $last = $loop->index + 1 == sizeof($transport) ? 'true' : false;
                          // $last = false;
                       @endphp
   
                       @if (!empty($key['tipo']))
                           <x-bladewind::timeline align_left="{{ $alignLeft }}" icon="{{ $icon }}"
                               last="{{ $last }}">
                               <x-slot:content>
                                   <div class="timeline-content">
                                       <x-bladewind::card>
                                           @if (!empty($key['voucher']))
                                               <div class="row">
                                                   <div class="col">
                                                       <strong>{{ __('messages.numero_boucher_card') }}</strong>
                                                   </div>
                                                   <div class="col">
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
   
   
                                           @if (!empty($key['cantidad']))
                                               <div class="row">
                                                   <div class="col-12 col-sm-6">
                                                       <strong>{{ __('messages.cantidad_card') }}</strong>
                                                   </div>
                                                   <div class="col-12 col-sm-6">
                                                       <p>{{ $key['cantidad'] }}</p>
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
   
                                           @if (!empty($key['tipo']) && $key['tipo'] == '2' && $conteoTipos['tipo2'] != 0)
                                               <div class="row">
                                                   <div class="col-12 col-sm-6">
                                                       <strong>{{ __('messages.origen_card') }}</strong>
                                                   </div>
                                                   <div class="col-12 col-sm-6">
                                                       @if (!empty($key['plantaOrigen']))
                                                           <p>{{ $key['plantaOrigen'] }}</p>
                                                       @else
                                                           <p>
                                                               {{ __('messages.frente_card') }} {{ $key['tramoOrigen'] }}
                                                               {{ __('messages.zona_card') }}
                                                               {{ $key['hitoOrigen']}}
                                                               @if (!empty($key['abscisaOrigen']))
                                                                   {{ __('messages.abscisa_card') }}
                                                                   {{ $key['abscisaOrigen'] }}
                                                               @endif
                                                           </p>
                                                       @endif
                                                   </div>
                                               </div>
                                           @endif
   
                                           @if (!empty($key['tipo']) && $key['tipo'] == '1' && $conteoTipos['tipo1'] != 0 )
                                               <div class="row">
                                                   <div class="col-12 col-sm-6">
                                                       <strong>{{ __('messages.destino_card') }}</strong>
                                                   </div>
                                                   <div class="col-12 col-sm-6">
                                                       @if (!empty($key['plantaDestino']))
                                                           <p>{{ $transport[2]['plantaDestino']}}</p>
                                                       @else
                                                           <p>
                                                               {{ __('messages.frente_card') }}
                                                               {{ $key['tramoDestino']}}
                                                               {{ __('messages.zona_card') }}
                                                               {{ $key['hitoDestino']}}
                                                               @if (!empty($key['abscisaDestino']))
                                                                   {{ __('messages.abscisa_card') }}
                                                                   {{ $key['abscisaDestino'] }}
                                                               @endif
                                                           </p>
                                                       @endif
   
                                                   </div>
                                               </div>
                                           @endif
   
                                           @if (!empty($key['costCenter']) && !empty($key['tipo']))
                                               <div class="row">
                                                   @if ($key['tipo'] == '1')
                                                       <div class="col-12 col-sm-6">
                                                           <strong>{{ __('messages.wbe_destino_card') }}</strong>
                                                       </div>
                                                       <div class="col-12 col-sm-6">
                                                           <p>{{ $key['costCenter'] }}</p>
                                                       </div>
                                                   @else
                                                       <div class="col-12 col-sm-6">
                                                           <strong>{{ __('messages.wbe_origen_card') }}</strong>
                                                       </div>
                                                       <div class="col-12 col-sm-6">
                                                           <p>{{ $key['costCenter'] }}</p>
                                                       </div>
                                                   @endif
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
                                       </x-bladewind::card>
                                   </div>
                               </x-slot:content>
                           </x-bladewind::timeline>
                           @else
                           <x-bladewind::timeline align_left="{{ $alignLeft }}" icon="clipboard"
                               last="{{ $last }}">
                               <x-slot:content>
                                   <div class="timeline-content">
                                       <x-bladewind::card>                 
                                       @if (!empty($transport[$posicion]['solicitud']))
                                       <div class="row">
                                           <div class="col">
                                               <strong>{{ __('messages.solicitud_card') }}</strong>
                                           </div>
                                           <div class="col">
                                               <p>{{ $transport[$posicion]['solicitud']  }}</p>
                                           </div>
                                          
                                       </div>    
                                           @else
                                           <div class="row">
                                               <div class="col">
                                                   <strong>{{ __('messages.solicitud_card') }}</strong>
                                               </div>
                                               <div class="col">
                                                   <p>{{$solicitudes = collect($transport)->pluck('solicitud')->get(0);}}</p>   
                                               </div>
                                           </div>    
                                           @endif
   
                                           @if (!empty($key['fechaProgramacion']))
                                               <div class="row">
                                                   <div class="col-12 col-sm-6">
                                                       <strong>{{ __('messages.fecha_programacion_card') }}</strong>
                                                   </div>
                                                   <div class="col-12 col-sm-6">
                                                       <p>{{ $key['fechaProgramacion'] }}</p>
                                                   </div>
                                               </div>
                                           @endif
                                           @if (!empty($transport[$posicion]['material']) &&!empty($transport[$posicion]['solicitud']))
                                               <div class="row">
                                                   <div class="col">
                                                       <strong>{{ __('messages.material_card') }}</strong>
                                                   </div>
                                                   <div class="col">
                                                       <p>{{ $transport[$posicion]['material'] }}</p>
                                                   </div>
                                               </div>
                                           @endif
   
                                           @if (!empty($transport[$posicion]['formula']))
                                               <div class="row">
                                                   <div class="col">
                                                       <strong>{{ __('messages.formula_card') }}</strong>
                                                   </div>
                                                   <div class="col">
                                                       <p>{{ $transport[$posicion]['formula'] }}</p>
                                                   </div>
                                               </div>
                                           @endif
                                           @if (!empty($key['cantidad']))
                                           <div class="row">
                                               <div class="col-12 col-sm-6">
                                                   <strong>{{ __('messages.cantidad_card') }}</strong>
                                               </div>
                                               <div class="col-12 col-sm-6">
                                                   <p>{{ $key['cantidad'] }}</p>
                                               </div>
                                           </div>
                                       @endif
                                           @if (!empty($key['solicitante']))
                                               <div class="row">
                                                   <div class="col-12 col-sm-6">
                                                       <strong>{{ __('messages.solicitante_card') }}</strong>
                                                   </div>
                                                   <div class="col-12 col-sm-6">
                                                       <p>{{ $key['solicitante'] }}</p>
                                                   </div>
                                               </div>
                                           @endif
                                           @if (!empty($key['nota_usuario']))
                                           <div class="row">
                                               <div class="col-12 col-sm-6">
                                                   <strong>{{ __('messages.observacion_card') }}</strong>
                                               </div>
                                               <div class="col-12 col-sm-6">
                                                   <p>{{ $key['nota_usuario'] }}</p>
                                               </div>
                                           </div>
                                       @endif
                                       @if (!empty($key['nota_su']))
                                           <div class="row">
                                               <div class="col-12 col-sm-6">
                                                   <strong>{{ __('messages.observacion_card_aprobador') }}</strong>
                                               </div>
                                               <div class="col-12 col-sm-6">
                                                   <p>{{ $key['nota_su'] }}</p>
                                               </div>
                                           </div>
                                       @endif
                                       @if (!empty($key['super_aprobador']))
                                       <div class="row">
                                           <div class="col-12 col-sm-6">
                                               <strong>{{ __('messages.usuario_aprobador') }}</strong>
                                           </div>
                                           <div class="col-12 col-sm-6">
                                               <p>{{ $key['super_aprobador'] }}</p>
                                           </div>
                                       </div>
                                   @endif
                                       </x-bladewind::card>
                                   </div>
                               </x-slot:content>
                           </x-bladewind::timeline>
                       @endif
                       @endforeach
           </div>
       </x-bladewind::timeline-group>
   
       @else
           <div class="container mt-3" style="max-width: 800px;">
               <x-bladewind::empty-state show_image="true" heading="{{ __('messages.ups_registro_no_encontrado') }}"
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
            <x-bladewind::card title="Recorrido del transporte">
                <div id="map" style="height: 400px; width: 100%;"></div>
                <script>
                    const map = L.map('map').setView([51.505, -0.09], 13); 
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                    }).addTo(map);
                
                    const waypoints = [];
                    const tooltipInfo = [];
                
                    @php
                        if (!empty($ubicacion_salida) && str_contains($ubicacion_salida, ';')) {
                            $salida = isset($key['plantaOrigen']) ? "<p>{$key['plantaOrigen']}</p>" : "<p>"
                                    . __('messages.frente_card') 
                                    . (!empty($key['tramoOrigen']) ? $key['tramoOrigen'] : __('messages.default_tramo')) .
                                   "<br>" . __('messages.zona_card') 
                                    . (!empty($key['hitoOrigen']) ? $key['hitoOrigen'] : __('messages.default_hito'))
                                    . (!empty($key['abscisaOrigen']) 
                                        ? "<br>" . __('messages.abscisa_card') . " " . $key['abscisaOrigen'] 
                                        : "")
                                . "</p>";
                
                            $entrada = explode(';', $ubicacion_salida);
                            if (count($entrada) === 2) {
                                echo "waypoints.push(L.latLng({$entrada[0]}, {$entrada[1]}));";
                                echo "tooltipInfo.push('Salida: {$salida}');";
                            }
                        }
                        if (!empty($ubicacion_entrada) && str_contains($ubicacion_entrada, ';')) {
                            $llegada = !empty($key['plantaDestino']) ? "<p>{$key['plantaDestino']}</p>" : "<p>"
                            . __('messages.frente_card') ." ". $key['tramoDestino2'] ."<br>"
                            . __('messages.zona_card') ." ". $key['hitoDestino2'] ."<br>"
                            . (!empty($key['abscisaDestino']) 
                                ?   __('messages.abscisa_card') . " " . $key['abscisaDestino'] 
                                : "")
                            . "</p>";
                
                            $salida = explode(';', $ubicacion_entrada);
                            if (count($salida) === 2) {
                                echo "waypoints.push(L.latLng({$salida[0]}, {$salida[1]}));";
                                echo "tooltipInfo.push('Llegada: {$llegada}');";
                            }
                        }
                    @endphp
                
                    // Crear iconos personalizados para marcadores
                    const redIcon = L.icon({
                        iconUrl: 'https://mapmarker.io/api/v3/font-awesome/v6/pin?text=L&size=85&color=FFF&background=ed6464&hoffset=0&voffset=0', // Cambiar a la URL de tu icono rojo
                        iconSize: [40, 41], // Tamaño del icono
                        iconAnchor: [18, 41], // Punto de anclaje del icono
                        popupAnchor: [1, -34], // Punto de anclaje para el popup
                    });
                
                    const greenIcon = L.icon({
                        iconUrl: 'https://mapmarker.io/api/v3/font-awesome/v6/pin?text=S&size=85&color=FFF&background=5aba45&hoffset=0&voffset=0', // Cambiar a la URL de tu icono verde
                        iconSize: [40, 41], // Tamaño del icono
                        iconAnchor: [18, 41], // Punto de anclaje del icono
                        popupAnchor: [1, -34], // Punto de anclaje para el popup
                    });
                
                    // Si ambos puntos están presentes, mostrar la ruta
                    if (waypoints.length === 2) {
                        L.Routing.control({
                            waypoints: waypoints,
                            routeWhileDragging: true,
                        }).addTo(map);
                
                        // Ajustar el mapa a los dos puntos
                        map.fitBounds(L.latLngBounds(waypoints));
                
                        // Añadir marcadores con tooltips para cada punto
                        waypoints.forEach((point, index) => {
                            const icon = index === 0 ?   greenIcon : redIcon; // Salida es roja, llegada es verde
                            const marker = L.marker(point, { icon }).addTo(map);
                            marker.bindTooltip(tooltipInfo[index]); // Asociar el tooltip correspondiente
                        });
                    }
                    // Si solo hay un punto (entrada o salida), centrar el mapa en ese punto
                    else if (waypoints.length === 1) {
                        map.setView(waypoints[0], 13); // Centrar el mapa en el único punto
                        const icon = tooltipInfo[0].includes('Llegada') ? greenIcon : redIcon; // Definir icono según el tipo
                        L.marker(waypoints[0], { icon }).addTo(map).bindTooltip(tooltipInfo[0]);
                    } else {
                        alert("No se han proporcionado puntos válidos para mostrar en el mapa.");
                    }
                </script>
                
                
            </x-bladewind::card>
        @endif
    </div>
</div>



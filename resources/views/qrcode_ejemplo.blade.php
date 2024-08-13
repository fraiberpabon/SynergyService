<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Webu QR code</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/salto_de_pagina.css') }}">
</head>
<body class="d-flex flex-column">

            <div class="d-flex flex-row contenedor"  >
                <div class="d-flex flex-column tarjeta" style=" width:{{{ $ancho }}}cm; height: {{{ $alto}}}cm;">

                        @include('tarjetas',['medidas'=>['ancho'=>$ancho,'alto'=>$alto],'datos'=>['codigo'=>'1234']])

                </div>
                <div class="d-flex flex-column tarjeta" style=" width:{{{ $ancho }}}cm; height: {{{ $alto}}}cm;">

                        @include('tarjetas',['medidas'=>['ancho'=>$ancho,'alto'=>$alto],'datos'=>['codigo'=>'1234']])

                </div>
 
            </div>
            <div class="d-flex flex-row contenedor"  >
                <div class="d-flex flex-column tarjeta" style=" width:{{{ $ancho }}}cm; height: {{{ $alto}}}cm;">

                        @include('tarjetas',['medidas'=>['ancho'=>$ancho,'alto'=>$alto],'datos'=>['codigo'=>'1234']])

                </div>
                <div class="d-flex flex-column tarjeta" style=" width:{{{ $ancho }}}cm; height: {{{ $alto}}}cm;">

                        @include('tarjetas',['medidas'=>['ancho'=>$ancho,'alto'=>$alto],'datos'=>['codigo'=>'1234']])

                </div>
 
            </div>

            <div class="d-flex flex-row contenedor"  >
                <div class="d-flex flex-column tarjeta" style=" width:{{{ $ancho }}}cm; height: {{{ $alto}}}cm;">

                        @include('tarjetas',['medidas'=>['ancho'=>$ancho,'alto'=>$alto],'datos'=>['codigo'=>'1234']])

                </div>
                <div class="d-flex flex-column tarjeta" style=" width:{{{ $ancho }}}cm; height: {{{ $alto}}}cm;">

                        @include('tarjetas',['medidas'=>['ancho'=>$ancho,'alto'=>$alto],'datos'=>['codigo'=>'1234']])

                </div>
 
            </div>
            <div class="d-flex flex-row contenedor"  >
                <div class="d-flex flex-column tarjeta" style=" width:{{{ $ancho }}}cm; height: {{{ $alto}}}cm;">

                        @include('tarjetas',['medidas'=>['ancho'=>$ancho,'alto'=>$alto],'datos'=>['codigo'=>'1234']])

                </div>
                <div class="d-flex flex-column tarjeta" style=" width:{{{ $ancho }}}cm; height: {{{ $alto}}}cm;">

                        @include('tarjetas',['medidas'=>['ancho'=>$ancho,'alto'=>$alto],'datos'=>['codigo'=>'1234']])

                </div>
 
            </div>
   
</body>
</html>

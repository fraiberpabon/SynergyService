<table>
    <thead>
        <tr>
            <th style ="font-size:22px;text-align:center;"colspan="14">REPORTE INFORME HALLAZGOS</th>
        </tr>
        <tr>
            <th style="font-weight: bold; font-size:14px">Id</th>
            <th style="font-weight: bold; font-size:14px">Fecha registro</th>
            <th style="font-weight: bold; font-size:14px">Calzada</th>
            <th style="font-weight: bold; font-size:14px">Ruta nacional</th>
            <th style="font-weight: bold; font-size:14px">Observación</th>
            <th style="font-weight: bold; font-size:14px">Usuario</th>
            <th style="font-weight: bold; font-size:14px">Ubicación</th>
            <th style="font-weight: bold; font-size:14px">Hallazgo</th>
            <th style="font-weight: bold; font-size:14px">Foto 1</th>
            <th style="font-weight: bold; font-size:14px">Foto 2</th>
            <th style="font-weight: bold; font-size:14px">Foto 3</th>
            <th style="font-weight: bold; font-size:14px">Foto 4</th>
            <th style="font-weight: bold; font-size:14px">Foto 5</th>
            <th style="font-weight: bold; font-size:14px">Foto 6</th>
            <th style="font-weight: bold; font-size:14px">Estado</th>
            <th style="font-weight: bold; font-size:14px">Fecha de cierre Hallazgo</th>
            <th style="font-weight: bold; font-size:14px">Foto cierre 1</th>
            <th style="font-weight: bold; font-size:14px">Foto cierre 2</th>
            <th style="font-weight: bold; font-size:14px">Observaciones cierre</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($wbInformeCampo as $wbInformeCampos)
            <tr>
                <td style="text-align:center">{{ $wbInformeCampos->id_proyecto }}</td>
                <td style="text-align:center">{{ \Carbon\Carbon::parse($wbInformeCampos->fecha_registro)->format('d/m/Y') }}</td>
                <td style="text-align:center">
                    @if ($wbInformeCampos->tipoCalzada)
                        {{ $wbInformeCampos->tipoCalzada->Descripcion }}
                    @else
                        N/A
                    @endif
                </td>
                <td style="text-align:center">
                    @if ($wbInformeCampos->tipoRuta)
                        {{ $wbInformeCampos->tipoRuta->nombre }}
                    @else
                        N/A
                    @endif
                </td >

                <td style="text-align:center">{{ $wbInformeCampos->observacion }}</td>
                <td style="text-align:center">
                    @if ($wbInformeCampos->tipoUsuario)
                        {{ $wbInformeCampos->tipoUsuario->Nombre }} {{ $wbInformeCampos->tipoUsuario->Apellido }}
                    @else
                        N/A
                    @endif
                </td>
                <td style="text-align:center">
                    @php
                        $ubicacion = $wbInformeCampos->ubicacion_hallazgo;

                        if (strlen($ubicacion) == 5) {
                            $ubicacion_formatted = 'PR ' . substr($ubicacion, 0, 2) . '+' . substr($ubicacion, 2, 3);
                        } else {
                            $ubicacion_formatted = 'PR ' . substr($ubicacion, 0, 1) . '+' . substr($ubicacion, 1, 3);
                        }
                    @endphp
                    {{ $ubicacion_formatted }}
                </td>

                <td style="text-align:center">
                    {{ $wbInformeCampos->tipoHallazgo->implode('nombre', ',') }}
                </td>


                <td style="text-align:center">
                </td>
                <td style="text-align:center">
                </td>
                <td style="text-align:center">
                </td>
                <td style="text-align:center">
                </td>
                <td style="text-align:center">
                </td>
                <td style="text-align:center">
                </td>
                <td style="text-align:center">
                    @if ($wbInformeCampos->tipoEstado)
                        {{ $wbInformeCampos->tipoEstado->descripcion_estado }}
                    @else
                        N/A
                    @endif
                </td>
                <td style="text-align:center">
                    @if ($wbInformeCampos->fecha_cierre)
                        {{ $wbInformeCampos->fecha_cierre }}
                    @else
                    @endif
                </td >
                <td style="text-align:center">
                </td>
                <td style="text-align:center">
                </td>
                <td style="text-align:center">
                    @if ($wbInformeCampos->observaciones_cierre)
                        {{ $wbInformeCampos->observaciones_cierre }}
                    @else
                    @endif
                </td >
        @endforeach
    </tbody>
</table>

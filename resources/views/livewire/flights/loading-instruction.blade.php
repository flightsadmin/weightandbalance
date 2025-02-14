<div class="container-fluid">
    <pre class="small m-0" style="font-family: monospace; line-height: 1.2;">
LOADING INSTRUCTION/REPORT             PREPARED           PRINTED
ALL WEIGHTS IN KILOS                   N/A                {{ now()->format('Hi') }}                                    
FLIGHT     DATE      FROM-TO       ETD        A-C/REG       VERSION      GATE
{{ str_pad($flight->flight_number, 8) }} {{ strtoupper($flight->scheduled_departure_time->format('dMy')) }} {{ str_pad($flight->departure_airport . '-' . $flight->arrival_airport, 10) }} {{ $flight->scheduled_departure_time->format('Hi') }} {{ str_pad($flight->aircraft->registration_number, 8) }} {{ str_pad($loadplan->version, 8) }}

PLANNED LOAD
AMS    OF    OC    OY /     OC    OM    OB
LOADING SPECS: NIL
TRANSIT SPECS: NIL
RELOADS:

LOADING INSTRUCTION                                    ACTUAL
-----------------------------------------------------------------------------------------
CPT  {{ $flight->aircraft->type->holds->where('code', 'FH')->first()->code }}    FWD MAX {{ $flight->aircraft->type->holds->where('code', 'FH')->first()->max_weight }}        * CPT 1  TOTAL:
-----------------------------------------------------------------------------------------
    </pre>

    <!-- Table for Forward Hold -->
    <table class="table table-borderless"
        style="font-family: monospace; font-size: small; border-collapse: separate; border-spacing: 0;">
        <tbody>
            @foreach ($loadingInstructions->where('hold', 'Forward Hold')->sortBy('position') as $index => $container)
                @if ($index % 2 == 0)
                    <tr style="border-bottom: 1px dotted black;">
                        <td>
                            :{{ str_pad($container['position'], 8) }}
                            {{ str_pad($container['content_type'] === 'baggage' ? 'PAG' : 'CGO', 8) }}<br>
                            :ONLOAD {{ str_pad($container['container_number'], 8) }}<br>
                            :REPORT
                        </td>
                    @else
                        <td>
                            :{{ str_pad($container['position'], 8) }}
                            {{ str_pad($container['content_type'] === 'baggage' ? 'PAG' : 'CGO', 8) }}<br>
                            :ONLOAD {{ str_pad($container['container_number'], 8) }}<br>
                            :REPORT
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <pre class="small m-0" style="font-family: monospace; line-height: 1.2;">
-----------------------------------------------------------------------------------------
CPT  {{ $flight->aircraft->type->holds->where('code', 'AH')->first()->code }}    AFT MAX {{ $flight->aircraft->type->holds->where('code', 'AH')->first()->max_weight }}        * CPT 2  TOTAL:
-----------------------------------------------------------------------------------------
    </pre>

    <!-- Table for Aft Hold -->
    <table class="table table-borderless" style="font-family: monospace; font-size: small; border-collapse: separate; border-spacing: 0;">
        <tbody>
            @foreach ($loadingInstructions->where('hold', 'Aft Hold')->sortBy('position') as $index => $container)
                @if ($index % 2 == 0)
                    <tr style="border-bottom: 1px dotted black;">
                        <td>
                            :{{ str_pad($container['position'], 8) }}
                            {{ str_pad($container['content_type'] === 'baggage' ? 'PAG' : 'CGO', 8) }}<br>
                            :ONLOAD {{ str_pad($container['container_number'], 8) }}<br>
                            :REPORT
                        </td>
                    @else
                        <td>
                            :{{ str_pad($container['position'], 8) }}
                            {{ str_pad($container['content_type'] === 'baggage' ? 'PAG' : 'CGO', 8) }}<br>
                            :ONLOAD {{ str_pad($container['container_number'], 8) }}<br>
                            :REPORT
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <pre class="small m-0" style="font-family: monospace; line-height: 1.2;">
-----------------------------------------------------------------------------------------
CPT  {{ $flight->aircraft->type->holds->where('code', 'BH')->first()->code }}    BULK MAX {{ $flight->aircraft->type->holds->where('code', 'BH')->first()->max_weight }}       * CPT 3  TOTAL:
-----------------------------------------------------------------------------------------
    </pre>

    <!-- Table for Bulk Hold -->
    <table class="table table-borderless" style="font-family: monospace; font-size: small; border-collapse: separate; border-spacing: 0;">
        <tbody>
            @foreach ($loadingInstructions->where('hold', 'Bulk Hold')->sortBy('position') as $index => $container)
                @if ($index % 2 == 0)
                    <tr style="border-bottom: 1px dotted black;">
                        <td>
                            :{{ str_pad($container['position'], 8) }}
                            {{ str_pad($container['content_type'] === 'baggage' ? 'PAG' : 'CGO', 8) }}<br>
                            :ONLOAD {{ str_pad($container['container_number'], 8) }}<br>
                            :REPORT
                        </td>
                    @else
                        <td>
                            :{{ str_pad($container['position'], 8) }}
                            {{ str_pad($container['content_type'] === 'baggage' ? 'PAG' : 'CGO', 8) }}<br>
                            :ONLOAD {{ str_pad($container['container_number'], 8) }}<br>
                            :REPORT
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <pre class="small m-0" style="font-family: monospace; line-height: 1.2;">
-----------------------------------------------------------------------------------------
    </pre>
</div>

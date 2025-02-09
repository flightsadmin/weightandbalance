<div class="mb-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title m-0">Load Sheet</h3>
                    <div class="d-flex gap-2 m-0">
                        @if ($loadsheet && !$loadsheet->final)
                            <button class="btn btn-success btn-sm m-0" wire:click="finalizeLoadsheet">
                                <i class="bi bi-check2-circle"></i> Finalize Loadsheet</button>
                        @endif
                        <button class="btn btn-primary btn-sm m-0" wire:click="generateLoadsheet"
                            {{ !$flight->fuel || !$loadplan || $loadplan->status !== 'released' ? 'disabled' : '' }}>
                            <i class="bi bi-plus-circle"></i> Generate New Loadsheet</button>
                    </div>
                </div>
                @if ($loadsheet)
                    <div class="card-body p-2">
                        @php
                            $distribution = $loadsheet->payload_distribution;
                            $pax = $loadsheet->payload_distribution['load_data'];
                            $totalPax = array_sum(
                                array_column(
                                    array_filter($pax['pax_by_type'], fn($data, $type) => $type !== 'infant', ARRAY_FILTER_USE_BOTH),
                                    'count',
                                ),
                            );

                            $totalDeadload = array_sum(array_column($pax['hold_breakdown'], 'weight'));
                            $zfw = $flight->aircraft->type->max_zero_fuel_weight - $distribution['weights']['zero_fuel'];
                            $tow = $flight->aircraft->type->max_takeoff_weight - $distribution['weights']['takeoff'];
                            $ldw = $flight->aircraft->type->max_landing_weight - $distribution['weights']['landing'];
                            $underload = min($zfw, $tow, $ldw);
                        @endphp
                        <div style="font-family: monospace;">
                            <table class="table table-sm table-borderless m-0">
                                <tbody>
                                    <tr>
                                        <td>LOADSHEET</td>
                                        <td>CHECKED</td>
                                        <td>APPROVED</td>
                                        <td>EDNO</td>
                                    </tr>
                                    <tr>
                                        <td>ALL WEIGHTS IN KILOS</td>
                                        <td class="text-uppercase">{{ $loadsheet->creator->name ?? 'N/A' }}</td>
                                        <td></td>
                                        <td>{{ $loadsheet->edition }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td>FROM/TO</td>
                                    <td>FLIGHT</td>
                                    <td>A/C REG</td>
                                    <td>VERSION</td>
                                    <td>CREW</td>
                                    <td>DATE</td>
                                    <td>TIME</td>
                                </tr>
                                <tr>
                                    <td>{{ $flight->departure_airport }}/{{ $flight->arrival_airport }}</td>
                                    <td>{{ $flight->flight_number }}</td>
                                    <td>{{ $flight->aircraft->registration_number }}</td>
                                    <td>{{ $flight->aircraft->type->code }}</td>
                                    <td>{{ $flight->fuel->crew ?? 'N/A' }}</td>
                                    <td>{{ strtoupper($flight->scheduled_departure_time->format('dMY')) }}</td>
                                    <td>{{ now()->format('Hi') }}</td>
                                </tr>
                            </table>
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td style="width: 50%;">WEIGHT</td>
                                    <td style="width: 50%;">DISTRIBUTION</td>
                                </tr>
                                <tr>
                                    <td>LOAD IN COMPARTMENTS</td>
                                    <td>
                                        @forelse ($pax['hold_breakdown'] as $hold)
                                            {{ $hold['hold_no'] }}/{{ $hold['weight'] }}
                                        @empty
                                            NIL
                                        @endforelse
                                    </td>
                                </tr>
                                <tr>
                                    <td>PASSENGER/CABIN BAG</td>
                                    <td>
                                        @forelse ($pax['pax_by_type'] as $type => $count)
                                            {{ $count['count'] . '/' }}
                                        @empty
                                            NIL
                                        @endforelse
                                        <span class="ms-3">TTL {{ $totalPax }} CAB 0</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>Y {{ $totalPax }} SOC 0/0</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>BLKD 0</td>
                                </tr>
                            </table>
                            <hr class="my-0">
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td>TOTAL TRAFFIC LOAD</td>
                                    <td>{{ $distribution['weights']['zero_fuel'] - $flight->aircraft->basic_weight }}</td>
                                </tr>
                                <tr>
                                    <td>DRY OPERATING WEIGHT</td>
                                    <td>{{ $flight->aircraft->basic_weight }}</td>
                                </tr>
                                <tr>
                                    <td>ZERO FUEL WEIGHT ACTUAL</td>
                                    <td>{{ $distribution['weights']['zero_fuel'] }} MAX
                                        {{ $flight->aircraft->type->max_zero_fuel_weight }} ADJ
                                    </td>
                                </tr>
                                <tr>
                                    <td>TAKE OFF FUEL</td>
                                    <td>{{ $distribution['fuel']['takeoff'] }}</td>
                                </tr>
                                <tr>
                                    <td>TAKE OFF WEIGHT ACTUAL</td>
                                    <td>{{ $distribution['weights']['takeoff'] }} MAX {{ $flight->aircraft->type->max_takeoff_weight }}
                                        ADJ</td>
                                </tr>
                                <tr>
                                    <td>TRIP FUEL</td>
                                    <td>{{ $distribution['fuel']['trip'] }}</td>
                                </tr>
                                <tr>
                                    <td>LANDING WEIGHT ACTUAL</td>
                                    <td>{{ $distribution['weights']['landing'] }} MAX {{ $flight->aircraft->type->max_landing_weight }}
                                        ADJ</td>
                                </tr>
                            </table>
                            <hr class="my-0">
                            <div>BALANCE / SEATING CONDITIONS</div>
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td>DOI: {{ number_format($distribution['indices']['doi'], 2) ?? 'N/A' }}</td>
                                    <td>DLI: {{ number_format($distribution['indices']['dli'], 2) ?? 'N/A' }}</td>
                                    <td>LAST MINUTE CHANGES</td>
                                </tr>
                                <tr>
                                    <td>LIZFW: {{ number_format($distribution['indices']['lizfw'], 2) ?? 'N/A' }}</td>
                                    <td>LITOW: {{ number_format($distribution['indices']['litow'], 2) ?? 'N/A' }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>MACZFW: {{ number_format($distribution['indices']['maczfw'], 2) ?? 'N/A' }}</td>
                                    <td>MACTOW: {{ number_format($distribution['indices']['mactow'], 2) ?? 'N/A' }}</td>
                                    <td></td>
                                </tr>
                            </table>
                            <div>STAB TRIM SETTING</div>
                            <div>STAB TO 1.9 NOSE UP</div>
                            <div>TRIM BY SEAT ROW</div>
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td style="width: 35%">UNDERLOAD BEFORE LMC</td>
                                    <td>{{ $underload }}</td>
                                    <td>LMC TOTAL</td>
                                </tr>
                            </table>
                            <hr class="my-0">
                            <div>LOADMESSAGE AND CAPTAIN'S INFORMATION BEFORE LMC</div>
                            <div>TAXI FUEL: {{ $distribution['fuel']['taxi'] }}</div>
                            {{-- LDM --}}
                            <div style="font-family: monospace;">
                                <div class="mt-3">LDM</div>
                                <div>
                                    {{ $flight->flight_number }}/{{ $flight->scheduled_departure_time->format('d') }}.
                                    {{ $flight->aircraft->registration_number }}.
                                    {{ $flight->aircraft->type->code }}.
                                    {{ $distribution['crew']['flight_deck'] + $distribution['crew']['cabin'] }}
                                </div>
                                <div>
                                    -{{ $flight->arrival_airport }}.
                                    @forelse ($pax['pax_by_type'] as $type => $count)
                                        {{ $count['count'] . '/' }}
                                    @empty
                                        NIL
                                    @endforelse
                                    T{{ $totalDeadload }}.PAX/{{ $totalPax }}.PAD/0
                                </div>
                                <div>
                                    SI PAX WEIGHTS USED
                                    @foreach ($pax['passenger_weights_used'] as $type => $weight)
                                        {{ strtoupper($type[0]) }}{{ $weight }}
                                    @endforeach
                                    &nbsp; BAG WGT: ACTUAL
                                </div>
                                <div>
                                    {{ $flight->arrival_airport }}
                                    @forelse ($pax['deadload_by_type'] as $type => $weight)
                                        {{ $type }} {{ $weight['weight'] }}
                                    @empty
                                        C 0 M 0 B 0/0
                                    @endforelse
                                    O 0 &nbsp; T {{ $totalDeadload }}
                                </div>
                                <div>PANTRY CODE {{ $distribution['indices']['pantry']['code'] }}</div>
                                <div>ACTUAL LOADING OF AIRCRAFT</div>
                                <div>
                                    @forelse ($pax['hold_breakdown'] as $hold)
                                        <div>CPT{{ $hold['hold_no'] }}/{{ $hold['weight'] }}</div>
                                    @empty
                                        NIL
                                    @endforelse
                                </div>
                                <br>
                                <div>AIRCRAFT TYPE: {{ $flight->aircraft->type->name }}</div>
                                <div>NOTOC: NO</div>
                                <br>
                                <div>
                                    {{ $flight->arrival_airport }} &nbsp;&nbsp;
                                    @forelse ($pax['deadload_by_type'] as $type => $weight)
                                        {{ $type }} {{ $weight['weight'] }} &nbsp;&nbsp;
                                    @empty
                                        C 0 M 0 B 0/0
                                    @endforelse
                                    TRA 0
                                </div>
                            </div>
                            <div>END LOADSHEET EDNO {{ $loadsheet->edition }} -
                                {{ $flight->flight_number }}/{{ $flight->scheduled_departure_time->format('d') }}
                                {{ $flight->scheduled_departure_time->format('Hi') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="text-center py-4">
                            <p class="text-muted">No loadsheet generated yet.</p>
                            @if (!$flight->fuel)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Fuel data must be added before generating loadsheet.
                                </div>
                            @endif
                            @if (!$loadplan || $loadplan->status !== 'released')
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Load plan must be released before generating loadsheet.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title m-0">Trim Sheet</h3>
                </div>
                <div class="card-body">
                    <div class="py-4">
                        @if (!$loadsheet)
                            <p class="text-muted text-center">No loadsheet generated yet.</p>
                        @else
                            <p>Loadsheet generated successfully.</p>
                            {{-- <pre>{{ json_encode($loadsheet->payload_distribution, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE) }}</pre> --}}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <button class="btn btn-primary btn-sm" wire:click="generateSummary">
        <i class="bi bi-list-check"></i> Summarize
    </button>

    <!-- Summary Modal -->
    <div class="modal fade" id="summaryModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Flight Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($showModal)
                        <!-- Passengers -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0">Passengers</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h6>By Gender</h6>
                                                <ul class="list-unstyled">
                                                    @foreach ($summary['passengers']['by_gender'] as $gender => $count)
                                                        <li>{{ ucfirst($gender) }}: {{ $count }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>By Zone</h6>
                                                <ul class="list-unstyled">
                                                    @foreach ($summary['passengers']['by_zone'] as $zone => $count)
                                                        <li>{{ $zone }}: {{ $count }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="col-md-4">
                                                <h6>Total</h6>
                                                <ul class="list-unstyled">
                                                    <li>Count: {{ $summary['passengers']['count'] }}</li>
                                                    <li>Weight: {{ number_format($summary['passengers']['total_weight']) }} kg</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Crew -->
                                <div class="card mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0">Crew</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <ul class="list-unstyled mb-0">
                                                    <li>Count: {{ $summary['crew']['count'] }}</li>
                                                    <li>Weight: {{ number_format($summary['crew']['weight']) }} kg</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Fuel -->
                                <div class="card mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0">Fuel</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <ul class="list-unstyled mb-0">
                                                    <li>Quantity: {{ number_format($summary['fuel']['quantity']) }} L</li>
                                                    <li>Density: {{ number_format($summary['fuel']['density'], 3) }} kg/L</li>
                                                    <li>Weight: {{ number_format($summary['fuel']['weight']) }} kg</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Baggage -->
                                <div class="card mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0">Baggage by Hold</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Hold</th>
                                                        <th>Count</th>
                                                        <th>Weight</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($summary['baggage'] as $hold => $data)
                                                        <tr>
                                                            <td>{{ $hold }}</td>
                                                            <td>{{ $data['count'] }}</td>
                                                            <td>{{ number_format($data['weight']) }} kg</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Cargo -->
                                <div class="card mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0">Cargo by Hold</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Hold</th>
                                                        <th>Count</th>
                                                        <th>Weight</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($summary['cargo'] as $hold => $data)
                                                        <tr>
                                                            <td>{{ $hold }}</td>
                                                            <td>{{ $data['count'] }}</td>
                                                            <td>{{ number_format($data['weight']) }} kg</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Total Weight -->
                            <div class="alert alert-info">
                                <strong>Total Weight:</strong> {{ number_format($summary['total_weight']) }} kg
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer d-flex justify-content-between py-1">
                    <button type="button" class="btn btn-sm btn-secondary bi bi-x-lg" data-bs-dismiss="modal"> Back</button>
                    <button type="button" class="btn btn-sm btn-primary bi bi-printer" onclick="window.print()"> Print</button>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('show-summary', () => {
                const modal = new bootstrap.Modal(document.getElementById('summaryModal'));
                modal.show();
            });
        </script>
    @endscript
</div>

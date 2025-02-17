<div class="modal fade" id="seatModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <form wire:submit="assignSeat">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Seat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="seat-container">
                        @dump($editingPassenger)
                        <div class="seat-grid">
                            @php
                                $allSeats = $seats
                                    ->pluck('seats')
                                    ->flatten()
                                    ->sortBy(['row', 'column']);
                            @endphp
                            @foreach ($allSeats->groupBy('row') as $row => $rowSeats)
                                <div class="seat-row">
                                    @foreach ($rowSeats->sortBy('column') as $seat)
                                        <div class="seat-cell 
                                            {{ $seat->is_occupied ? 'occupied' : '' }}
                                            {{ $seat->is_blocked ? 'blocked' : '' }}
                                            {{ $selectedSeat == $seat->id ? 'selected' : '' }}"
                                            wire:click="{{ !$seat->is_occupied && !$seat->is_blocked ? 'selectSeat(' . $seat->id . ')' : '' }}">
                                            {{ $seat->designation }}
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <div class="seat-legend">
                            <div class="legend-item">
                                <div class="seat-cell"></div>
                                <span>Available</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat-cell occupied"></div>
                                <span>Occupied</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat-cell blocked"></div>
                                <span>Blocked</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat-cell selected"></div>
                                <span>Selected</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" @if (!$selectedSeat) disabled @endif>
                        Assign Seat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .seat-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .seat-grid {
        display: table;
        border-spacing: 2px;
        border-collapse: separate;
        margin: 0 auto;
    }

    .seat-row {
        display: table-row;
    }

    .row-number {
        display: table-cell;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        padding-right: 4px;
        font-size: 0.80rem;
    }

    .seat-cell {
        display: table-cell;
        cursor: pointer;
        padding: 4px;
        border-radius: 3px;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
        text-align: center;
        min-width: 35px;
        height: 35px;
        vertical-align: middle;
        font-size: 0.80rem;
    }

    .seat-cell:hover:not(.occupied):not(.blocked) {
        background-color: #e9ecef;
    }

    .seat-cell.occupied {
        background-color: #6c757d;
        color: white;
        cursor: not-allowed;
    }

    .seat-cell.blocked {
        background-color: #dc3545;
        color: white;
        cursor: not-allowed;
    }

    .seat-cell.selected {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }

    .seat-legend {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #dee2e6;
        font-size: 0.75rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .legend-item .seat-cell {
        width: 24px;
        height: 24px;
        min-width: 24px;
        cursor: default;
    }
</style>

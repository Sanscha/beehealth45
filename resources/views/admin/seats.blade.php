@extends('layouts.admin')

@section('title', 'Seats & Zones')

@section('topbar_actions')
    <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('addZoneModal')">+ Add Zone</button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('addSeatModal')">+ Single Seat</button>
    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('batchSeatModal')">Batch Generate</button>
@endsection

@section('content')

    <!-- Zone Cards Header -->
    <div class="grid grid-cols-3" style="margin-bottom: 24px;">
        @foreach($zones as $z)
            <div class="card" style="padding: 16px 20px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <div style="font-size:14px; font-weight:600; color:var(--text-primary);">{{ $z->name }}</div>
                        <span class="badge badge-member" style="margin-top:4px;">{{ $z->code }}</span>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:22px; font-weight:600; color:#15803d;">
                            {{ $z->seats->where('status', 'available')->count() }}
                        </div>
                        <div style="font-size:11.5px; color:var(--text-muted);">available / {{ $z->seats->count() }} total</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Seat Directory -->
    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
            <div class="card-title">Seat Inventory</div>
            <form method="GET" action="{{ route('admin.seats') }}" style="display:flex; gap:10px;">
                <select name="zone_id" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Zones &amp; Floors</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ $selectedZoneId == $z->id ? 'selected' : '' }}>{{ $z->name }} ({{ $z->code }})</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px;">
                @forelse($seats as $seat)
                    <div class="card" style="padding: 12px 14px; background: #fafafa; border-color: var(--border-color);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                            <span style="font-size:15px; font-weight:600; color:var(--text-primary);">{{ $seat->seat_number }}</span>
                            <span class="badge badge-{{ $seat->status }}">{{ $seat->status }}</span>
                        </div>
                        <div style="font-size:11.5px; color:var(--text-muted); margin-bottom:10px;">
                            {{ $seat->zone->name ?? 'Unassigned' }}
                        </div>

                        @if($seat->activeBooking && $seat->activeBooking->user)
                            <div style="font-size:11px; color:#4338ca; margin-bottom:10px; background:#eef2ff; border:1px solid #e0e7ff; padding:3px 6px; border-radius:4px;">
                                Booked: <strong>{{ $seat->activeBooking->user->name }}</strong>
                            </div>
                        @endif

                        <form action="{{ route('admin.seats.update-status', $seat->id) }}" method="POST">
                            @csrf
                            <select name="status" class="form-control" style="font-size:11.5px; padding:5px 8px; margin-bottom:4px;" onchange="this.form.submit()">
                                <option value="available" {{ $seat->status == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="occupied" {{ $seat->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                <option value="reserved" {{ $seat->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                <option value="maintenance" {{ $seat->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </form>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px;">No seats found. Click "Batch Generate" above to populate.</div>
                @endforelse
            </div>
        </div>

        @if($seats->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-color);">
                {{ $seats->links() }}
            </div>
        @endif
    </div>

    <!-- Add Zone Modal -->
    <div id="addZoneModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Zone / Floor</h3>
                <button type="button" class="modal-close" onclick="closeModal('addZoneModal')">&times;</button>
            </div>
            <form action="{{ route('admin.zones.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="zoneName">Zone / Floor Name</label>
                        <input type="text" name="name" id="zoneName" class="form-control" placeholder="e.g. Third Floor - Reading Room" required>
                    </div>
                    <div class="form-group">
                        <label for="zoneCode">Zone Code</label>
                        <input type="text" name="code" id="zoneCode" class="form-control" placeholder="e.g. TF-RR" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addZoneModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Zone</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Single Seat Modal -->
    <div id="addSeatModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Single Seat</h3>
                <button type="button" class="modal-close" onclick="closeModal('addSeatModal')">&times;</button>
            </div>
            <form action="{{ route('admin.seats.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="seatZone">Select Zone</label>
                        <select name="zone_id" id="seatZone" class="form-control" required>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->name }} ({{ $z->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="seatNumber">Seat Number</label>
                        <input type="text" name="seat_number" id="seatNumber" class="form-control" placeholder="e.g. G-25" required>
                    </div>
                    <div class="form-group">
                        <label for="seatInitStatus">Status</label>
                        <select name="status" id="seatInitStatus" class="form-control" required>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="reserved">Reserved</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addSeatModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Seat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Generate Seats Modal -->
    <div id="batchSeatModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Batch Generate Seats</h3>
                <button type="button" class="modal-close" onclick="closeModal('batchSeatModal')">&times;</button>
            </div>
            <form action="{{ route('admin.seats.batch') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="batchZone">Target Zone</label>
                        <select name="zone_id" id="batchZone" class="form-control" required>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->name }} ({{ $z->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="prefix">Seat Prefix</label>
                        <input type="text" name="prefix" id="prefix" class="form-control" placeholder="e.g. G-" required value="G-">
                    </div>
                    <div class="grid grid-cols-2">
                        <div class="form-group">
                            <label for="startNum">From</label>
                            <input type="number" name="start" id="startNum" class="form-control" value="11" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="endNum">To</label>
                            <input type="number" name="end" id="endNum" class="form-control" value="30" min="1" required>
                        </div>
                    </div>
                    <p style="font-size:12px; color:var(--text-muted);">
                        Prefix 'G-' from 11 to 30 will generate G-11, G-12 ... G-30. Existing seats will be skipped automatically.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('batchSeatModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Seats</button>
                </div>
            </form>
        </div>
    </div>

@endsection

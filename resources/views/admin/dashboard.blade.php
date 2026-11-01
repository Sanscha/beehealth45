@extends('layouts.admin')

@section('title', 'Overview')

@section('topbar_actions')
    <a href="{{ route('admin.libraries') }}" class="btn btn-secondary btn-sm">Libraries ({{ $totalLibraries }})</a>
    <a href="{{ route('admin.bookings') }}" class="btn btn-secondary btn-sm">Scan Pass</a>
    <a href="{{ route('admin.payments') }}" class="btn btn-primary btn-sm">+ Record Payment</a>
@endsection

@section('content')

    <!-- Platform Wide Multi-Library Network Stats -->
    <div style="margin-bottom: 12px; display:flex; justify-content:space-between; align-items:center;">
        <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">
            Network Overview
        </h3>
        <a href="{{ route('admin.libraries') }}" style="font-size:12.5px; color:var(--primary); text-decoration:none; font-weight:500;">
            View all libraries &rarr;
        </a>
    </div>

    <div class="grid grid-cols-4" style="margin-bottom: 24px;">
        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Registered Libraries</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">{{ $totalLibraries }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15zM8 7h8M8 11h6"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; display:flex; gap:8px; font-size:12px; color:var(--text-secondary);">
                <span><strong style="color:var(--success);">{{ $activeLibraries }}</strong> active</span>
                <span>•</span>
                <span><strong style="color:var(--warning);">{{ $pendingLibraries }}</strong> pending</span>
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Onboarding Revenue</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">₹{{ number_format($libraryRegRevenue, 2) }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:#f0fdf4; color:#15803d; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Registration fees collected
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Library Managers</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">{{ $totalManagers }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:var(--info-light); color:var(--info); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Owners on platform
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Total Members</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">{{ $totalMembers }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:#f5f3ff; color:#6d28d9; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Registered students
            </div>
        </div>
    </div>

    <!-- Live Occupancy & Today's Revenue Metrics -->
    <div style="margin-bottom: 12px;">
        <h3 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">
            Branch Operations
        </h3>
    </div>

    <div class="grid grid-cols-4" style="margin-bottom: 24px;">
        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Live Occupancy</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">{{ $occupancyRate }}%</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 18v3h3v-3h10v3h3v-3h1a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-1V3H4v3H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h1z"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; display:flex; gap:8px; font-size:12px; color:var(--text-secondary);">
                <span><strong style="color:var(--success);">{{ $availableSeats }}</strong> free</span>
                <span>•</span>
                <span><strong style="color:var(--danger);">{{ $occupiedSeats }}</strong> taken</span>
                <span>•</span>
                <span><strong style="color:var(--warning);">{{ $reservedSeats }}</strong> res</span>
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Today's Revenue</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:#15803d;">₹{{ number_format($totalRevenueToday, 2) }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:#f0fdf4; color:#15803d; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Month total: <strong>₹{{ number_format($totalRevenueMonth, 2) }}</strong>
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Today's Passes</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">{{ $todayBookingsCount }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:var(--info-light); color:var(--info); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Active entries today
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Total Seats</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">{{ $totalSeats }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:var(--bg-elevated); color:var(--text-secondary); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                {{ $maintenanceSeats }} under maintenance
            </div>
        </div>
    </div>

    <!-- Live Interactive Floor Map -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div>
                <div class="card-title">Live Floor Seat Grid Map</div>
                <div style="font-size:12.5px; color:var(--text-muted); margin-top:1px;">Click any seat to update status or assign member.</div>
            </div>
            <div style="display:flex; gap:16px; font-size:12px; align-items:center;">
                <span style="display:flex; align-items:center; gap:6px;"><span style="width:8px; height:8px; border-radius:2px; background:var(--success);"></span> Available</span>
                <span style="display:flex; align-items:center; gap:6px;"><span style="width:8px; height:8px; border-radius:2px; background:var(--danger);"></span> Occupied</span>
                <span style="display:flex; align-items:center; gap:6px;"><span style="width:8px; height:8px; border-radius:2px; background:var(--warning);"></span> Reserved</span>
                <span style="display:flex; align-items:center; gap:6px;"><span style="width:8px; height:8px; border-radius:2px; background:#94a3b8;"></span> Maintenance</span>
            </div>
        </div>
        <div class="card-body">
            @foreach($zones as $zone)
                <div style="margin-bottom: 20px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                        <h4 style="font-size:14px; font-weight:600; color:var(--text-primary);">
                            {{ $zone->name }} <span style="font-size:12px; font-weight:400; color:var(--text-muted);">({{ $zone->code }})</span>
                        </h4>
                        <span style="font-size:12px; color:var(--text-muted);">
                            {{ $zone->seats->where('status', 'available')->count() }} free / {{ $zone->seats->count() }} total
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 8px;">
                        @forelse($zone->seats as $seat)
                            @php
                                $statusStyles = [
                                    'available' => 'background:#f0fdf4; border-color:#bbf7d0; color:#15803d;',
                                    'occupied' => 'background:#fef2f2; border-color:#fecaca; color:#b91c1c;',
                                    'reserved' => 'background:#fffbeb; border-color:#fde68a; color:#b45309;',
                                    'maintenance' => 'background:#f1f5f9; border-color:#e2e8f0; color:#64748b;',
                                ];
                            @endphp
                            <button type="button" 
                                onclick="openSeatModal('{{ $seat->id }}', '{{ $seat->seat_number }}', '{{ $zone->name }}', '{{ $seat->status }}')"
                                style="border: 1px solid; {{ $statusStyles[$seat->status] ?? '' }} border-radius: 6px; padding: 10px 4px; text-align: center; cursor: pointer; font-family: inherit;">
                                <div style="font-weight: 600; font-size: 13px;">{{ $seat->seat_number }}</div>
                                <div style="font-size: 9.5px; text-transform: uppercase; margin-top: 2px; font-weight: 500;">{{ $seat->status }}</div>
                            </button>
                        @empty
                            <div style="font-size: 13px; color: var(--text-muted);">No seats added in this zone yet.</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3-Column Activity Grid -->
    <div class="grid grid-cols-3">
        <!-- Recent Libraries -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Libraries</div>
                <a href="{{ route('admin.libraries') }}" class="btn btn-secondary btn-sm">All</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Library</th>
                            <th>Owner</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLibraries as $rl)
                            <tr>
                                <td>
                                    <div style="font-weight: 500; color:var(--text-primary); font-size:13px;">{{ $rl->name }}</div>
                                    <div style="font-size:11px; color:var(--text-muted);">{{ $rl->city ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div style="font-size:12.5px; color:var(--text-secondary);">{{ $rl->owner->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $rl->payment_status }}">
                                        {{ $rl->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:20px;">No libraries registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Passes -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Passes</div>
                <a href="{{ route('admin.bookings') }}" class="btn btn-secondary btn-sm">All</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Pass / Member</th>
                            <th>Seat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $b)
                            <tr>
                                <td>
                                    <div style="font-weight: 500; color: var(--text-primary);">{{ $b->pass_code }}</div>
                                    <div style="font-size: 11.5px; color: var(--text-muted);">{{ $b->user->name ?? 'Guest' }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-member">{{ $b->seat->seat_number ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $b->status }}">{{ $b->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:20px;">No bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Payments</div>
                <a href="{{ route('admin.payments') }}" class="btn btn-secondary btn-sm">All</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User / Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $p)
                            <tr>
                                <td>
                                    <div style="font-weight: 500; color: var(--text-primary); font-size:12.5px;">{{ $p->user->name ?? 'User' }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">
                                        @if($p->type === 'library_registration')
                                            <span style="color:#0369a1;">Reg: {{ $p->library->name ?? 'Library' }}</span>
                                        @else
                                            <span>{{ $p->plan->name ?? ($p->notes ?: 'Seat Pass') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: #15803d;">₹{{ number_format($p->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $p->status }}">{{ $p->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:20px;">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Seat Quick Update Modal -->
    <div id="seatStatusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Seat <span id="modalSeatNumber" style="color:var(--primary);"></span></h3>
                <button type="button" class="modal-close" onclick="closeModal('seatStatusModal')">&times;</button>
            </div>
            <form id="seatStatusForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p style="font-size:13px; color:var(--text-secondary); margin-bottom:14px;">
                        Zone: <strong id="modalZoneName" style="color:var(--text-primary);"></strong>
                    </p>

                    <div class="form-group">
                        <label for="seatStatusSelect">Change Status</label>
                        <select name="status" id="seatStatusSelect" class="form-control">
                            <option value="available">Available (Vacant & Ready)</option>
                            <option value="occupied">Occupied (Student seated)</option>
                            <option value="reserved">Reserved (Pass allocated)</option>
                            <option value="maintenance">Maintenance (Closed)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('seatStatusModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function openSeatModal(id, seatNumber, zoneName, currentStatus) {
        document.getElementById('modalSeatNumber').innerText = seatNumber;
        document.getElementById('modalZoneName').innerText = zoneName;
        document.getElementById('seatStatusSelect').value = currentStatus;
        document.getElementById('seatStatusForm').action = "/admin/seats/" + id + "/status";
        openModal('seatStatusModal');
    }
</script>
@endsection

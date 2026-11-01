@extends('layouts.admin')

@section('title', 'Passes & Bookings')

@section('content')

    <!-- Pass Lookup Bar -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; padding:16px 20px;">
            <div>
                <h3 style="font-size:14.5px; font-weight:600; color:var(--text-primary);">Fast Pass Lookup &amp; Check-In</h3>
                <p style="font-size:12px; color:var(--text-muted); margin-top:2px;">Scan or type member pass code (e.g. <code>LIB-PASS-001</code>) to check-in student.</p>
            </div>

            <form method="GET" action="{{ route('admin.bookings') }}" style="display:flex; gap:8px; width:100%; max-width:360px;">
                <input type="text" name="search" class="form-control" placeholder="Pass Code (e.g. LIB-PASS-001)..." value="{{ request('search') }}" style="font-family:monospace; text-transform:uppercase;">
                <button type="submit" class="btn btn-primary btn-sm">Lookup</button>
            </form>
        </div>
    </div>

    <!-- Bookings Table & Filters -->
    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
            <div class="card-title">All Passes ({{ $bookings->total() }})</div>
            <form method="GET" action="{{ route('admin.bookings') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="search" class="form-control" placeholder="Search by pass, student, or seat..." value="{{ request('search') }}" style="max-width: 220px;">
                <select name="status" class="form-control" style="max-width: 130px;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <select name="shift" class="form-control" style="max-width: 130px;">
                    <option value="">All Shifts</option>
                    <option value="morning" {{ request('shift') == 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="evening" {{ request('shift') == 'evening' ? 'selected' : '' }}>Evening</option>
                    <option value="full_day" {{ request('shift') == 'full_day' ? 'selected' : '' }}>Full Day</option>
                </select>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="max-width: 150px;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['search', 'status', 'shift', 'date']))
                    <a href="{{ route('admin.bookings') }}" class="btn btn-secondary btn-sm" style="color:var(--text-muted);">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Pass Code</th>
                        <th>Student</th>
                        <th>Seat &amp; Zone</th>
                        <th>Shift</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr>
                            <td>
                                <span style="font-family: monospace; font-weight: 500; color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #dbeafe; font-size:12px;">
                                    {{ $b->pass_code }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text-primary);">{{ $b->user->name ?? 'Guest User' }}</div>
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ $b->user->phone ?? ($b->user->email ?? '-') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text-primary);">{{ $b->seat->seat_number ?? '-' }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $b->seat->zone->name ?? '-' }}</div>
                            </td>
                            <td style="text-transform: capitalize;">
                                <span class="badge badge-member">{{ str_replace('_', ' ', $b->shift) }}</span>
                            </td>
                            <td>
                                <div style="font-size: 13px;">{{ $b->booking_date }}</div>
                                @if($b->booking_date === date('Y-m-d'))
                                    <span style="font-size: 11px; color: var(--success); font-weight: 500;">Today</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $b->status }}">{{ $b->status }}</span>
                            </td>
                            <td style="text-align:right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    @if($b->status === 'active')
                                        <form action="{{ route('admin.bookings.checkin', $b->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" style="padding:4px 8px; font-size:11px;" title="Check In">Check In</button>
                                        </form>
                                        <form action="{{ route('admin.bookings.cancel', $b->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm" style="padding:4px 8px; font-size:11px; color:var(--danger);" onclick="return confirm('Cancel booking and release seat?')" title="Cancel Pass">Cancel</button>
                                        </form>
                                    @else
                                        <span style="font-size: 11.5px; color: var(--text-muted);">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">No passes found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-color);">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

@endsection

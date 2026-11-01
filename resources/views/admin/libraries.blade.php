@extends('layouts.admin')

@section('title', 'Libraries & Owners')

@section('content')

    <!-- Metrics Row -->
    <div class="grid grid-cols-4" style="margin-bottom: 24px;">
        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Total Libraries</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">{{ $metrics['total'] }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15zM8 7h8M8 11h6"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Onboarded branches
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Active Branches</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:#15803d;">{{ $metrics['active'] }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:#f0fdf4; color:#15803d; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Discoverable in app
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Pending Payment</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:#b45309;">{{ $metrics['pending_payment'] }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:#fffbeb; color:#b45309; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Awaiting fee confirmation
            </div>
        </div>

        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:12px; font-weight:500; color:var(--text-muted);">Onboarding Revenue</div>
                    <div style="font-size:24px; font-weight:600; margin-top:4px; color:var(--text-primary);">₹{{ number_format($metrics['reg_revenue'], 2) }}</div>
                </div>
                <div style="width:36px; height:36px; border-radius:6px; background:var(--info-light); color:var(--info); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20M7 15h.01M11 15h2"/></svg>
                </div>
            </div>
            <div style="margin-top:10px; font-size:12px; color:var(--text-muted);">
                Registration fees collected
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 14px 20px;">
            <form action="{{ route('admin.libraries') }}" method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <div style="flex:1; min-width:220px;">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by library name, owner, city, phone...">
                </div>

                <div style="width:160px;">
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Status: All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending_payment" {{ request('status') === 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <div style="width:160px;">
                    <select name="payment_status" class="form-control" onchange="this.form.submit()">
                        <option value="">Payment: All</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['search', 'status', 'payment_status']))
                    <a href="{{ route('admin.libraries') }}" class="btn btn-secondary btn-sm" style="color:var(--text-muted);">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Libraries Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">All Libraries ({{ $libraries->total() }})</div>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Library</th>
                        <th>Owner / Manager</th>
                        <th>Location</th>
                        <th>Seats</th>
                        <th>Registration Fee</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($libraries as $lib)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:34px; height:34px; border-radius:6px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:14px; border:1px solid #dbeafe; overflow:hidden;">
                                        @if($lib->image)
                                            <img src="{{ asset('storage/' . $lib->image) }}" alt="{{ $lib->name }}" style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            {{ strtoupper(substr($lib->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight: 500; color:var(--text-primary);">{{ $lib->name }}</div>
                                        <div style="font-size:11px; color:var(--text-muted);">Slug: {{ $lib->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($lib->owner)
                                    <div>
                                        <div style="font-weight: 500; color:var(--text-primary);">{{ $lib->owner->name }}</div>
                                        <div style="font-size:12px; color:var(--text-muted);">{{ $lib->owner->phone }}</div>
                                        <div style="font-size:11px; color:var(--text-muted); margin-top:1px;">
                                            {{ $lib->owner->email }}
                                            @if($lib->owner->isEmailVerified())
                                                <span style="color:var(--success); font-size:11px; font-weight:500;">(Verified)</span>
                                            @else
                                                <span style="color:var(--warning); font-size:11px;">(Unverified)</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span style="color:var(--text-muted);">No owner</span>
                                @endif
                            </td>
                            <td>
                                <div style="color:var(--text-primary); font-size:12.5px;">{{ $lib->city ?? 'N/A' }}{{ $lib->state ? ', ' . $lib->state : '' }}</div>
                                <div style="font-size:11px; color:var(--text-muted); max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $lib->address }}">{{ $lib->address }}</div>
                            </td>
                            <td>
                                <div style="font-weight:500; color:var(--text-primary);">
                                    {{ $lib->seats_count ?? $lib->total_seats }} total
                                </div>
                                <div style="font-size:11.5px; color:var(--success);">
                                    {{ $lib->available_seats_count }} available
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:500; color:var(--text-primary);">₹{{ number_format($lib->registration_fee, 2) }}</div>
                                <div style="margin-top:2px;">
                                    <span class="badge badge-{{ $lib->payment_status }}">
                                        {{ $lib->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $lib->status }}">
                                    {{ str_replace('_', ' ', $lib->status) }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:6px; align-items:center;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="showLibraryModal({{ json_encode($lib) }})">
                                        View
                                    </button>

                                    @if($lib->payment_status !== 'paid')
                                        <form action="{{ route('admin.libraries.verify-payment', $lib->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Verify payment and activate {{ $lib->name }}?')">
                                                Verify
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.libraries.toggle-status', $lib->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Change status for {{ $lib->name }}?')">
                                            {{ $lib->status === 'active' ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 32px; color: var(--text-muted);">
                                No registered libraries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($libraries->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-color);">
                {{ $libraries->links() }}
            </div>
        @endif
    </div>

    <!-- Library Details Modal -->
    <div id="libraryDetailsModal" class="modal">
        <div class="modal-content" style="max-width: 540px;">
            <div class="modal-header">
                <h3 id="modalLibraryName">Library Details</h3>
                <button type="button" class="modal-close" onclick="closeModal('libraryDetailsModal')">&times;</button>
            </div>
            <div class="modal-body" id="modalLibraryBody">
                <!-- Dynamically populated -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('libraryDetailsModal')">Close</button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function showLibraryModal(lib) {
        document.getElementById('modalLibraryName').innerText = lib.name;
        
        let amenitiesHtml = '<span style="color:var(--text-muted); font-size:12px;">None specified</span>';
        if (lib.amenities && Array.isArray(lib.amenities) && lib.amenities.length > 0) {
            amenitiesHtml = lib.amenities.map(a => `<span style="background:#f1f5f9; padding:2px 8px; border-radius:4px; font-size:12px; border:1px solid #e2e8f0; color:#334155;">${a}</span>`).join(' ');
        }

        let ownerHtml = lib.owner ? `
            <div style="margin-top:4px; font-size:13px; color:var(--text-primary);">
                <strong>${lib.owner.name}</strong> • ${lib.owner.phone}<br>
                <span style="color:var(--text-muted);">${lib.owner.email}</span>
            </div>
        ` : 'N/A';

        let html = `
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="font-size:11px; text-transform:uppercase; color:var(--text-muted); font-weight:600;">Owner / Manager</label>
                    ${ownerHtml}
                </div>
                <div>
                    <label style="font-size:11px; text-transform:uppercase; color:var(--text-muted); font-weight:600;">Full Address</label>
                    <div style="font-size:13px; color:var(--text-primary); margin-top:2px;">
                        ${lib.address}<br>
                        ${lib.city || ''} ${lib.state || ''} ${lib.pincode || ''}
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:11px; text-transform:uppercase; color:var(--text-muted); font-weight:600;">Coordinates</label>
                        <div style="font-size:13px; color:var(--text-primary); margin-top:2px;">
                            ${lib.latitude || '0.00'}, ${lib.longitude || '0.00'}
                        </div>
                    </div>
                    <div>
                        <label style="font-size:11px; text-transform:uppercase; color:var(--text-muted); font-weight:600;">Phone / Email</label>
                        <div style="font-size:13px; color:var(--text-primary); margin-top:2px;">
                            ${lib.phone || 'N/A'}<br>${lib.email || 'N/A'}
                        </div>
                    </div>
                </div>
                <div>
                    <label style="font-size:11px; text-transform:uppercase; color:var(--text-muted); font-weight:600;">Amenities</label>
                    <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:4px;">
                        ${amenitiesHtml}
                    </div>
                </div>
                <div>
                    <label style="font-size:11px; text-transform:uppercase; color:var(--text-muted); font-weight:600;">Description</label>
                    <div style="font-size:13px; color:var(--text-secondary); margin-top:2px; line-height:1.4;">
                        ${lib.description || 'No description provided.'}
                    </div>
                </div>
            </div>
        `;

        document.getElementById('modalLibraryBody').innerHTML = html;
        openModal('libraryDetailsModal');
    }
</script>
@endsection

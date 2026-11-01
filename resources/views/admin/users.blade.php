@extends('layouts.admin')

@section('title', 'Users & Managers')

@section('topbar_actions')
    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addUserModal')">+ Add User</button>
@endsection

@section('content')

    <!-- Metrics -->
    <div class="grid grid-cols-4" style="margin-bottom: 24px;">
        <div class="card" style="padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 500; color: var(--text-muted);">Total Accounts</div>
            <div style="font-size: 24px; font-weight: 600; margin-top: 4px; color: var(--text-primary);">{{ $counts['total'] }}</div>
        </div>
        <div class="card" style="padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 500; color: var(--text-muted);">Library Managers</div>
            <div style="font-size: 24px; font-weight: 600; margin-top: 4px; color: #0284c7;">{{ $counts['managers'] }}</div>
        </div>
        <div class="card" style="padding: 16px 20px;">
            <div style="font-size: 12px; font-weight: 500; color: var(--text-muted);">Students / Members</div>
            <div style="font-size: 24px; font-weight: 600; margin-top: 4px; color: #4f46e5;">{{ $counts['members'] }}</div>
        </div>
        <div class="card" style="padding: 16px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-size: 12px; font-weight: 500; color: var(--text-muted);">Staff &amp; Admins</div>
                    <div style="font-size: 24px; font-weight: 600; margin-top: 4px; color: #7e22ce;">{{ $counts['staff'] + $counts['admin'] }}</div>
                </div>
                <div style="font-size:11.5px; color:var(--text-muted); text-align:right;">
                    {{ $counts['staff'] }} staff<br>{{ $counts['admin'] }} admin
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
            <form method="GET" action="{{ route('admin.users') }}" style="display: flex; gap: 10px; flex-wrap: wrap; flex: 1;">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="{{ request('search') }}" style="max-width: 260px;">
                <select name="role" class="form-control" style="max-width: 160px;">
                    <option value="">All Roles</option>
                    <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Library Manager</option>
                    <option value="member" {{ request('role') == 'member' ? 'selected' : '' }}>Member / Student</option>
                    <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <select name="status" class="form-control" style="max-width: 140px;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm" style="color:var(--text-muted);">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Activity</th>
                        <th>Joined</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; border:1px solid #dbeafe; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; color: #2563eb;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary);">{{ $user->name }}</div>
                                        <div style="font-size: 12px; color: var(--text-muted);">{{ $user->email }}</div>
                                        @if($user->library)
                                            <div style="font-size: 11px; color: #0284c7; margin-top: 1px;">
                                                🏛️ {{ $user->library->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $user->role }}">{{ $user->role }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->status ?? 'active' }}">{{ $user->status ?? 'active' }}</span>
                            </td>
                            <td style="font-size: 12px;">
                                <span>{{ $user->bookings_count }} passes</span> • 
                                <span>{{ $user->payments_count }} payments</span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}
                            </td>
                            <td style="text-align:right;">
                                @if($user->id !== Auth::id())
                                    <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @if(($user->status ?? 'active') === 'active')
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--danger);" onclick="return confirm('Block user {{ $user->name }}?')">Block</button>
                                        @else
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--success);">Activate</button>
                                        @endif
                                    </form>
                                @else
                                    <span style="font-size: 11px; color: var(--text-muted);">(You)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-color);">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add User Account</h3>
                <button type="button" class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="e.g. rahul@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="e.g. 9876543210" required>
                    </div>

                    <div class="form-group">
                        <label for="role">User Role</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="member">Library Member (Student)</option>
                            <option value="manager">Library Manager (Owner)</option>
                            <option value="staff">Library Staff (Reception)</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">Password (min 6 characters)</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>

@endsection

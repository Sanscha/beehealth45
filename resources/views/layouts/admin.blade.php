<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | SeatSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #f8fafc;
            --bg-surface: #ffffff;
            --bg-elevated: #f1f5f9;
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --success: #16a34a;
            --success-light: #f0fdf4;
            --danger: #dc2626;
            --danger-light: #fef2f2;
            --warning: #d97706;
            --warning-light: #fffbeb;
            --info: #0284c7;
            --info-light: #f0f9ff;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 10px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 14px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
        }

        /* Clean Sidebar */
        .sidebar {
            width: 240px;
            background: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 40;
        }

        .sidebar-brand {
            padding: 20px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .brand-text h1 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.2px;
        }

        .brand-text span {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
            display: block;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow-y: auto;
        }

        .nav-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 12px 10px 6px;
            letter-spacing: 0.5px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: all 0.15s ease;
        }

        .nav-item:hover {
            color: var(--text-primary);
            background: var(--bg-elevated);
        }

        .nav-item.active {
            color: var(--primary);
            background: var(--primary-light);
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--border-color);
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
        }

        .user-info {
            font-size: 12.5px;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-info strong {
            color: var(--text-primary);
            font-weight: 500;
        }

        .user-info span {
            display: block;
            font-size: 11px;
            color: var(--text-muted);
            text-transform: capitalize;
        }

        .btn-logout {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 4px;
            transition: color 0.15s;
        }

        .btn-logout:hover {
            color: var(--danger);
        }

        /* Main Content */
        .main-wrapper {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background: var(--bg-base);
        }

        .topbar {
            height: 56px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-surface);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .page-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge-live {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid #bbf7d0;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pulse-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--success);
        }

        .content-container {
            padding: 24px 28px;
            flex: 1;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid transparent;
        }

        .alert-success {
            background: var(--success-light);
            color: #15803d;
            border-color: #bbf7d0;
        }

        .alert-error {
            background: var(--danger-light);
            color: #b91c1c;
            border-color: #fecaca;
        }

        /* Buttons & Forms */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-secondary {
            background: var(--bg-surface);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-elevated);
            border-color: var(--border-hover);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            background: var(--bg-surface);
            border: 1px solid var(--border-hover);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 13.5px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        /* Card System */
        .card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
        }

        .card-title {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .card-body {
            padding: 20px;
        }

        /* Grid Utilities */
        .grid { display: grid; gap: 16px; }
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        th {
            text-align: left;
            padding: 10px 16px;
            font-size: 11.5px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            background: #f8fafc;
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-secondary);
        }

        tr:hover td {
            background: #fafafa;
            color: var(--text-primary);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 500;
            text-transform: capitalize;
            border: 1px solid transparent;
        }

        .badge-available, .badge-active, .badge-paid {
            background: var(--success-light);
            color: #15803d;
            border-color: #bbf7d0;
        }

        .badge-occupied, .badge-blocked {
            background: var(--danger-light);
            color: #b91c1c;
            border-color: #fecaca;
        }

        .badge-reserved, .badge-unpaid, .badge-pending_payment {
            background: var(--warning-light);
            color: #b45309;
            border-color: #fde68a;
        }

        .badge-maintenance, .badge-suspended {
            background: var(--bg-elevated);
            color: var(--text-muted);
            border-color: var(--border-color);
        }

        .badge-admin {
            background: #faf5ff;
            color: #7e22ce;
            border-color: #e9d5ff;
        }

        .badge-staff {
            background: #f5f3ff;
            color: #6d28d9;
            border-color: #ddd6fe;
        }

        .badge-manager {
            background: var(--info-light);
            color: #0369a1;
            border-color: #bae6fd;
        }

        .badge-member, .badge-user {
            background: #f8fafc;
            color: #334155;
            border-color: var(--border-color);
        }

        /* Modal Dialog */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(2px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 500px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 18px;
            cursor: pointer;
            padding: 2px 6px;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 14px 20px;
            background: #f8fafc;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        @media (max-width: 1024px) {
            .grid-cols-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-cols-3 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .grid-cols-4, .grid-cols-3, .grid-cols-2 { grid-template-columns: 1fr; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Clean Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">S</div>
            <div class="brand-text">
                <h1>SeatSync</h1>
                <span>Library Management</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Operations</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                Overview
            </a>
            <a href="{{ route('admin.libraries') }}" class="nav-item {{ request()->routeIs('admin.libraries*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15zM8 7h8M8 11h6"/></svg>
                Libraries & Owners
            </a>
            <a href="{{ route('admin.seats') }}" class="nav-item {{ request()->routeIs('admin.seats*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 18v3h3v-3h10v3h3v-3h1a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-1V3H4v3H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h1z"/></svg>
                Seats & Zones
            </a>
            <a href="{{ route('admin.bookings') }}" class="nav-item {{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Passes & Bookings
            </a>

            <div class="nav-label" style="margin-top: 10px;">Finance & Users</div>
            <a href="{{ route('admin.payments') }}" class="nav-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20M7 15h.01M11 15h2"/></svg>
                Payments & Plans
            </a>
            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                User Management
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-pill">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                <div class="user-info">
                    <strong>{{ Auth::user()->name ?? 'Staff' }}</strong>
                    <span>{{ Auth::user()->role ?? 'Staff' }}</span>
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout" title="Sign Out">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="page-title">@yield('title', 'Dashboard')</div>
            <div class="topbar-actions">
                <div class="badge-live">
                    <span class="pulse-dot"></span>
                    Live Sync
                </div>
                @yield('topbar_actions')
            </div>
        </header>

        <main class="content-container">
            @if(session('success'))
                <div class="alert alert-success">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:16px;">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:16px;">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="padding-left: 18px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active'));
            }
        });
    </script>
    @yield('scripts')
</body>
</html>

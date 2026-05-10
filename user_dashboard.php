<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION["role"] ?? "User";
$username = $_SESSION["username"] ?? "User";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #111;
            color: white;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #bbb;
            text-decoration: none;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #ff7a00;
            color: white;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .card-box {
            border-radius: 12px;
            padding: 20px;
            background: white;
        }

        .gradient-header {
            background: linear-gradient(90deg, #ff7a00, #ff9a3c);
            color: white;
            border-radius: 10px;
            padding: 15px;
        }

        .status-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-active   { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
        .badge-pending  { background: #fff3cd; color: #856404; }

        #loadingSpinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 0;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4 class="p-3">MarketHub</h4>

        <a href="#" class="menu active" onclick="loadPage('dashboard', event)">
            <i class="ri-dashboard-line"></i> Dashboard
        </a>

        <a href="#" class="menu" onclick="loadPage('payments', event)">
            <i class="ri-money-dollar-circle-line"></i> Payments
        </a>

        <a href="#" class="menu" onclick="loadPage('violations', event)">
            <i class="ri-error-warning-line"></i> My Violations
        </a>

        <a href="index.php">
            <i class="ri-logout-box-line"></i> Logout
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="content">

        <!-- TOP BAR -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 id="pageTitle">Dashboard</h4>
            <div class="text-end">
                <strong><?php echo htmlspecialchars($username); ?></strong><br>
                <small><?php echo htmlspecialchars($role); ?></small>
            </div>
        </div>

        <div id="mainContent">

            <!-- DASHBOARD -->
            <div id="dashboard" class="page">
                <div class="gradient-header mb-4">
                    Welcome back, <?php echo htmlspecialchars($username); ?>!
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card-box">
                            <h5 id="myStallCount">—</h5>
                            <small>My Stalls</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box">
                            <h5 id="myTotalPaid">—</h5>
                            <small>Total Paid</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box">
                            <h5 id="myPendingCount">—</h5>
                            <small>Pending Payments</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box">
                            <h5 id="myViolationCount">—</h5>
                            <small>My Violations</small>
                        </div>
                    </div>
                </div>

                <!-- Stall info cards -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card-box">
                            <h6 class="mb-3">My Stalls</h6>
                            <div id="myStallsBody">
                                <div class="text-muted text-center py-3">Loading...</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-box">
                            <h6 class="mb-3">Recent Payments</h6>
                            <div id="recentPaymentsBody">
                                <div class="text-muted text-center py-3">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAYMENTS -->
            <div id="payments" class="page d-none">
                <div class="card-box mb-3">
                    <h5 class="mb-0">Payment History</h5>
                </div>

                <!-- SUMMARY CARDS -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card-box text-center summary-card">
                            <h6 id="sumCollected">₱0.00</h6>
                            <small>Total Paid</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box text-center summary-card">
                            <h6 id="sumPending">₱0.00</h6>
                            <small>Pending</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box text-center summary-card">
                            <h6 id="sumTransactions">0</h6>
                            <small>Transactions</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box text-center summary-card">
                            <h6 id="sumOverdue">0</h6>
                            <small>Overdue</small>
                        </div>
                    </div>
                </div>

                <div class="card-box">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Stall No.</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="paymentTableBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VIOLATIONS -->
            <div id="violations" class="page d-none">
                <div class="card-box">
                    <h5 class="mb-3">My Violations</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Stall</th>
                                    <th>Violation Type</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Resolution</th>
                                </tr>
                            </thead>
                            <tbody id="violationTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- end #mainContent -->
    </div><!-- end .content -->

    <script>
        const API_BASE   = 'http://localhost:3000/api';
        // Username passed from PHP session — used to match vendor records
        const SESSION_USERNAME = '<?php echo addslashes($username); ?>';

        // Vendor record found for this user (populated on load)
        let myVendorId = null;

        // ─── PAGE NAVIGATION ──────────────────────────────────────
        function loadPage(page, event) {
            document.querySelectorAll('.page').forEach(p => p.classList.add('d-none'));
            document.getElementById(page).classList.remove('d-none');
            document.getElementById('pageTitle').innerText =
                page.charAt(0).toUpperCase() + page.slice(1);

            document.querySelectorAll('.menu').forEach(m => m.classList.remove('active'));
            if (event && event.target) event.target.closest('a').classList.add('active');

            if (page === 'dashboard')  loadDashboard();
            if (page === 'payments')   loadMyPayments();
            if (page === 'violations') loadMyViolations();
        }

        // ─── HELPERS ──────────────────────────────────────────────
        function escHtml(str) {
            if (str == null) return '—';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function formatDate(dateStr) {
            if (!dateStr) return '—';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function paymentStatusBadge(status) {
            const cls = {
                'Complete': 'badge-active',
                'Pending':  'badge-pending',
                'Failed':   'badge-inactive'
            }[status] || 'badge-pending';
            return `<span class="status-badge ${cls}">${escHtml(status)}</span>`;
        }

        function violationStatusBadge(status) {
            const cls = {
                'Resolved': 'badge-active',
                'Open':     'badge-inactive',
                'Warning':  'badge-pending'
            }[status] || 'badge-pending';
            return `<span class="status-badge ${cls}">${escHtml(status)}</span>`;
        }

        function stallStatusBadge(status) {
            const cls = {
                'Available':   'badge-active',
                'Occupied':    'badge-pending',
                'Maintenance': 'badge-inactive'
            }[status] || 'badge-pending';
            return `<span class="status-badge ${cls}">${escHtml(status)}</span>`;
        }

        // ─── RESOLVE VENDOR ID FROM SESSION USERNAME ──────────────
        // Matches the logged-in user's username against vendor names
        async function resolveMyVendorId() {
            if (myVendorId !== null) return myVendorId;
            try {
                const res = await fetch(`${API_BASE}/vendor`);
                if (!res.ok) throw new Error('Failed to fetch vendors');
                const vendors = await res.json();
                // Match by name field (vendor.name === session username)
                const match = vendors.find(v =>
                    v.name.toLowerCase() === SESSION_USERNAME.toLowerCase()
                );
                myVendorId = match ? match.vendor_id : null;
                return myVendorId;
            } catch (err) {
                console.error('Could not resolve vendor ID:', err);
                return null;
            }
        }

        // ─── DASHBOARD ────────────────────────────────────────────
        async function loadDashboard() {
            await resolveMyVendorId();

            await Promise.all([
                loadMyStallsDashboard(),
                loadMyPaymentsDashboard(),
                loadMyViolationsDashboard()
            ]);
        }

        async function loadMyStallsDashboard() {
            const stallsBody = document.getElementById('myStallsBody');
            const stallCountEl = document.getElementById('myStallCount');

            try {
                const res = await fetch(`${API_BASE}/stall`);
                if (!res.ok) throw new Error('Failed to fetch stalls');
                const stalls = await res.json();

                // Filter stalls assigned to this vendor
                const myStalls = myVendorId
                    ? stalls.filter(s => Number(s.vendor_id) === Number(myVendorId))
                    : [];

                stallCountEl.textContent = myStalls.length;

                if (myStalls.length === 0) {
                    stallsBody.innerHTML = `<p class="text-muted text-center">No stalls assigned.</p>`;
                    return;
                }

                stallsBody.innerHTML = myStalls.map(s => `
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <strong>Stall #${escHtml(String(s.stall_no))}</strong>
                        </div>
                        <div>${stallStatusBadge(s.status)}</div>
                    </div>
                `).join('');

            } catch (err) {
                console.error(err);
                stallCountEl.textContent = '—';
                stallsBody.innerHTML = `<p class="text-danger text-center">Failed to load stalls.</p>`;
            }
        }

        async function loadMyPaymentsDashboard() {
            const recentBody   = document.getElementById('recentPaymentsBody');
            const totalPaidEl  = document.getElementById('myTotalPaid');
            const pendingEl    = document.getElementById('myPendingCount');

            try {
                const res = await fetch(`${API_BASE}/payment`);
                if (!res.ok) throw new Error('Failed to fetch payments');
                const payments = await res.json();

                const mine = myVendorId
                    ? payments.filter(p => Number(p.vendor_id) === Number(myVendorId))
                    : [];

                const totalPaid   = mine.filter(p => p.status === 'Complete')
                                        .reduce((sum, p) => sum + Number(p.amount), 0);
                const pendingCount = mine.filter(p => p.status === 'Pending').length;

                totalPaidEl.textContent  = `₱${totalPaid.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
                pendingEl.textContent    = pendingCount;

                // Show latest 5 on dashboard
                const recent = mine.slice(0, 5);

                if (recent.length === 0) {
                    recentBody.innerHTML = `<p class="text-muted text-center">No payment records found.</p>`;
                    return;
                }

                recentBody.innerHTML = recent.map(p => `
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <small class="text-muted">${formatDate(p.payment_date)}</small><br>
                            <strong>₱${Number(p.amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</strong>
                            <small class="text-muted ms-1">(${escHtml(p.payment_method)})</small>
                        </div>
                        <div>${paymentStatusBadge(p.status)}</div>
                    </div>
                `).join('');

            } catch (err) {
                console.error(err);
                totalPaidEl.textContent = '—';
                pendingEl.textContent   = '—';
                recentBody.innerHTML    = `<p class="text-danger text-center">Failed to load payments.</p>`;
            }
        }

        async function loadMyViolationsDashboard() {
            const violationCountEl = document.getElementById('myViolationCount');

            try {
                const res = await fetch(`${API_BASE}/violation`);
                if (!res.ok) throw new Error('Failed to fetch violations');
                const violations = await res.json();

                const mine = myVendorId
                    ? violations.filter(v => Number(v.vendor_id) === Number(myVendorId))
                    : [];

                violationCountEl.textContent = mine.length;
            } catch (err) {
                console.error(err);
                violationCountEl.textContent = '—';
            }
        }

        // ─── PAYMENTS PAGE ────────────────────────────────────────
        async function loadMyPayments() {
            const tbody = document.getElementById('paymentTableBody');
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>`;

            await resolveMyVendorId();

            try {
                const res = await fetch(`${API_BASE}/payment`);
                if (!res.ok) throw new Error('Failed to fetch payments');
                const payments = await res.json();

                const mine = myVendorId
                    ? payments.filter(p => Number(p.vendor_id) === Number(myVendorId))
                    : [];

                updateSummaryCards(mine);

                if (mine.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No payment records found.</td></tr>`;
                    return;
                }

                tbody.innerHTML = mine.map((p, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${escHtml(p.stall_no)}</td>
                        <td>₱${Number(p.amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                        <td>${formatDate(p.payment_date)}</td>
                        <td>${escHtml(p.payment_method)}</td>
                        <td>${paymentStatusBadge(p.status)}</td>
                    </tr>
                `).join('');

            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="6" class="text-danger text-center">Failed to load payments.</td></tr>`;
            }
        }

        function updateSummaryCards(payments) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let totalCollected = 0, totalPending = 0, overdue = 0;

            payments.forEach(p => {
                const amount = Number(p.amount) || 0;
                if (p.status === 'Complete') {
                    totalCollected += amount;
                } else if (p.status === 'Pending') {
                    totalPending += amount;
                    const d = new Date(p.payment_date);
                    d.setHours(0, 0, 0, 0);
                    if (d < today) overdue++;
                } else if (p.status === 'Failed') {
                    const d = new Date(p.payment_date);
                    d.setHours(0, 0, 0, 0);
                    if (d < today) overdue++;
                }
            });

            document.getElementById('sumCollected').textContent =
                `₱${totalCollected.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
            document.getElementById('sumPending').textContent =
                `₱${totalPending.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
            document.getElementById('sumTransactions').textContent = payments.length;
            document.getElementById('sumOverdue').textContent = overdue;
        }

        // ─── VIOLATIONS PAGE ──────────────────────────────────────
        async function loadMyViolations() {
            const tbody = document.getElementById('violationTableBody');
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">Loading...</td></tr>`;

            await resolveMyVendorId();

            try {
                const res = await fetch(`${API_BASE}/violation`);
                if (!res.ok) throw new Error('Failed to fetch violations');
                const violations = await res.json();

                const mine = myVendorId
                    ? violations.filter(v => Number(v.vendor_id) === Number(myVendorId))
                    : [];

                if (mine.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No violations found.</td></tr>`;
                    return;
                }

                tbody.innerHTML = mine.map((v, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${escHtml(String(v.stall_no))}</td>
                        <td>${escHtml(v.violation_type)}</td>
                        <td>${escHtml(v.description)}</td>
                        <td>${formatDate(v.violation_date)}</td>
                        <td>${violationStatusBadge(v.status)}</td>
                        <td>${escHtml(v.resolution) !== '—' ? escHtml(v.resolution) : '<span class="text-muted">Pending review</span>'}</td>
                    </tr>
                `).join('');

            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center">Failed to load violations.</td></tr>`;
            }
        }

        // ─── INIT ─────────────────────────────────────────────────
        window.onload = function () {
            loadDashboard();
        };
    </script>

</body>
</html>
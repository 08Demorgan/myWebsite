<?php
session_start();

if (
    !isset($_SESSION["role"]) ||
    ($_SESSION["role"] !== "Admin" && $_SESSION["role"] !== "Market Manager")
) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"] ?? "Admin";
$role = $_SESSION["role"] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

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
            background: white;
            border-radius: 12px;
            padding: 20px;
        }

        .gradient-header {
            background: linear-gradient(90deg, #ff7a00, #ff9a3c);
            color: white;
            border-radius: 10px;
            padding: 15px;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .modal-box {
            background: #fff;
            padding: 20px;
            width: 400px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close-btn {
            position: absolute;
            right: 15px;
            top: 10px;
            cursor: pointer;
            font-size: 18px;
        }

        /* Vendor table styles */
        #vendorsTable th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #555;
        }

        #vendorsTable td {
            vertical-align: middle;
            font-size: 0.93rem;
        }

        .badge-active {
            background: #d4edda;
            color: #155724;
        }

        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        #vendorTableWrapper {
            overflow-x: auto;
        }

        #vendorLoadingMsg {
            text-align: center;
            color: #999;
            padding: 30px 0;
        }

        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1rem;
            padding: 2px 6px;
        }

        .action-btn.edit {
            color: #ff7a00;
        }

        .action-btn.delete {
            color: #dc3545;
        }

        .action-btn:hover {
            opacity: 0.75;
        }

        #vendorFeedback {
            display: none;
            margin-top: 10px;
        }

        #vio_vendor,
        #p_vendor,
        #vio_stall-e,
        #vio_vendor-e,
        {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4 class="p-3">MarketHub</h4>

        <a href="#" class="menu active" onclick="loadPage('dashboard')">
            <i class="ri-dashboard-line"></i> Dashboard
        </a>

        <a href="#" class="menu" onclick="loadPage('stalls')">
            <i class="ri-store-2-line"></i> Stall Management
        </a>

        <a href="#" class="menu" onclick="loadPage('vendors')">
            <i class="ri-group-line"></i> Vendors
        </a>

        <a href="#" class="menu" onclick="loadPage('payments')">
            <i class="ri-money-dollar-circle-line"></i> Payments
        </a>

        <a href="#" class="menu" onclick="loadPage('violations')">
            <i class="ri-error-warning-line"></i> Violations
        </a>

        <a href="index.php">
            <i class="ri-logout-box-line"></i> Logout
        </a>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- TOP BAR -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 id="pageTitle">Dashboard</h4>
            <div>
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

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card-box">
                            <h5 id="stallCount"></h5>
                            <small>Total Stalls</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box">
                            <h5 id="activeVendorCount"></h5>
                            <small>Active Vendors</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box">
                            <h5 id="pendingPaymentCount"></h5>
                            <small>Pending Payments</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box">
                            <h5 id="openViolationPayment">7</h5>
                            <small>Open Violations</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card-box">
                            <h6>Recent Activity</h6>
                            <p>New vendor registered</p>
                            <p>Payment received</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-box">
                            <h6>Stall Occupancy</h6>
                            <p id="dashboardAvailable">Available</p>
                            <div class="progress mb-2">
                                <div id="availableProgress" class="progress-bar bg-success"></div>
                            </div>

                            <p id="dashboardOccupied">Occupied</p>
                            <div class="progress mb-2">
                                <div id="occupiedProgress" class="progress-bar bg-warning"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STALLS -->
            <div id="stalls" class="page d-none">
                <div class="card-box">
                    <h5>Stall Management</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Stall #</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="stallTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VENDORS -->
            <div id="vendors" class="page d-none">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Vendors</h5>
                        <button class="btn btn-sm text-white" style="background:#ff7a00;" onclick='toggleForm()'>
                            <i class="ri-add-line"></i> Add Vendor
                        </button>
                    </div>

                    <!-- Feedback alert -->
                    <div id="vendorFeedback" class="alert" role="alert"></div>

                    <!-- Vendors Table -->
                    <div id="vendorTableWrapper">
                        <div id="vendorLoadingMsg">Loading vendors...</div>
                        <table class="table table-hover d-none" id="vendorsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Owner</th>
                                    <th>Contact No.</th>
                                    <th>Email</th>
                                    <th>Stall Management</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="vendorTableBody"></tbody>
                        </table>
                        <p id="vendorEmptyMsg" class="text-muted text-center py-4 d-none">No vendors found.</p>
                    </div>
                </div>
            </div>

            <!-- PAYMENTS -->
            <div id="payments" class="page d-none">
                <div class="card-box mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Payments</h5>
                        <button class="btn btn-sm text-white" style="background:#ff7a00;"
                            onclick="togglePaymentModal()">
                            <i class="ri-add-line"></i> Add Payment
                        </button>
                    </div>
                </div>

                <!-- SUMMARY CARDS -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card-box text-center summary-card">
                            <h6>₱0.00</h6>
                            <small>Total Collected</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box text-center summary-card">
                            <h6>₱0.00</h6>
                            <small>Pending</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box text-center summary-card">
                            <h6>0</h6>
                            <small>Transactions</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-box text-center summary-card">
                            <h6>0</h6>
                            <small>Overdue</small>
                        </div>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendor</th>
                                <th>Stall No.</th>
                                <th>Amount</th>
                                <th>Payment Date</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="paymentTableBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted">No payment records found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VIOLATIONS -->
            <!-- VIOLATIONS -->
            <div id="violations" class="page d-none">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Vendor Violations</h5>

                        <button class="btn btn-sm text-white" style="background:#ff7a00;"
                            onclick="toggleViolationModal()">
                            <i class="ri-add-line"></i>
                            Add Violation
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vendor</th>
                                    <th>Stall</th>
                                    <th>Violation</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody id="violationTableBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        No violations found.
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div><!-- end #mainContent -->
    </div><!-- end .content -->

    <!-- ═══════════════════════════════════════════════════════════
         MODALS — all outside page divs so d-none never hides them
         ═══════════════════════════════════════════════════════════ -->

    <!-- Stall Edit Modal -->
    <form id="stallModal" class="modal-overlay d-none">
        <div class="modal-box">
            <span class="close-btn" onclick="stallModal()">&times;</span>
            <h4 class="mb-3">Edit Stall</h4>
            <div class="mb-2">
                <label>Stall Number</label>
                <input type="text" id="stall_no" class="form-control" placeholder="e.g. A12" required>
            </div>
            <div class="mb-2">
                <label>Status</label>
                <select id="stall_status" class="form-select">
                    <option>Available</option>
                    <option>Occupied</option>
                    <option>Maintenance</option>
                </select>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-secondary me-2" onclick="stallModal()">Cancel</button>
                <button type="button" id="saveStallBtn" class="btn btn-success" onclick="editStall()">Save</button>
            </div>
        </div>
    </form>

    <!-- Add / Edit Vendor Modal -->
    <div id="vendorModal" class="modal-overlay d-none">
        <div class="modal-box">
            <span class="close-btn" onclick="toggleForm()">&times;</span>
            <h4 class="mb-3">Register New Vendor</h4>
            <input hidden id="hidden_stall_no" />
            <div class="mb-2">
                <label>Owner Name</label>
                <input type="text" id="v_name" class="form-control" placeholder="Full name" readonly>
            </div>
            <div class="mb-2">
                <label>Contact Number</label>
                <input type="text" id="v_contact_no" class="form-control" placeholder="09XX-XXX-XXXX" required>
            </div>
            <div class="mb-2">
                <label>Email Address</label>
                <input type="email" id="v_email" class="form-control" placeholder="email@example.com" required>
            </div>
            <div class="mb-2">
                <label>Assigned Stall</label>
                <select id="v_stall_no" class="form-select" required>
                    <option value="">Loading stalls...</option>
                </select>
            </div>
            <div class="mb-2">
                <label>Stall Type</label>
                <select id="v_stall_type" class="form-select">
                    <option>Food</option>
                    <option>Merchandise</option>
                    <option>Vegetables</option>
                    <option>Fruits</option>
                    <option>Clothing</option>
                    <option>Condiments</option>
                </select>
            </div>
            <div class="mb-2">
                <label>Status</label>
                <select id="v_status" class="form-select">
                    <option>Active</option>
                    <option>Inactive</option>
                    <option>Pending</option>
                </select>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <button class="btn btn-secondary" onclick="toggleForm()">Cancel</button>
                <button class="btn btn-success" id="saveVendorBtn" onclick="submitVendor()">
                    <span id="saveVendorSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Stall management modal for vendor -->
    <div id="stallManagementModal" class="modal-overlay d-none">
        <div class="modal-box">
            <span class="close-btn" onclick="toggleStallManagement()">&times;</span>
            <h4 class="mb-3">Manage Stalls</h4>
            <div class="mb-2">
                <input type="hidden" id="vs_vendor_id">
                <label>Owner Name</label>
                <input type="text" id="vs_name" class="form-control" placeholder="Full name" disabled>
            </div>
            </br>
            <h5>Stalls</h5>
            <div id="stallContainer">
                <div class="stall-group row mb-2">
                    <div class="col-6">
                        <label>Assigned Stall</label>
                        <select class="form-select stall-no" name="stall_no[]" onchange="handleChange()">
                        </select>
                    </div>

                    <div class="col-6">
                        <label>Stall Type</label>
                        <select class="form-select stall-type" name="stall_type[]">
                            <option>Food</option>
                            <option>Merchandise</option>
                            <option>Vegetables</option>
                            <option>Fruits</option>
                            <option>Clothing</option>
                            <option>Condiments</option>
                        </select>
                    </div>

                    <div class="col-12 mt-2">
                        <button type="button" class="btn btn-danger btn-sm remove-btn" onclick="removeStall(this)">
                            Remove
                        </button>
                    </div>

                </div>
            </div>

            <button type="button" class="btn btn-primary mt-2" onclick="addStall()">
                Add Stall
            </button>
            <div class="d-flex justify-content-between mt-3">
                <button class="btn btn-secondary" onclick="">Cancel</button>
                <button class="btn btn-success" id="saveVendorBtn" onclick="saveStalls()">
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div id="editPaymentModal" class="modal-overlay d-none">
        <form class="modal-box" onsubmit="submitEditPayment(event)">
            <span class="close-btn" onclick="closeEditPaymentModal()">&times;</span>
            <h4 class="mb-3">Edit Payment</h4>
            <div class="mb-2">
                <label>Amount</label>
                <input type="number" id="ep_amount" class="form-control">
            </div>
            <div class="mb-2">
                <label>Payment Date</label>
                <input type="date" id="ep_date" class="form-control">
            </div>
            <div class="mb-2">
                <label>Method</label>
                <select id="ep_method" class="form-select">
                    <option>Cash</option>
                    <option>GCash</option>
                    <option>Bank</option>
                </select>
            </div>
            <div class="mb-2">
                <label>Status</label>
                <select id="ep_status" class="form-select">
                    <option value="Pending">Pending</option>
                    <option value="Complete">Complete</option>
                    <option value="Failed">Failed</option>
                </select>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-secondary me-2" onclick="closeEditPaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </form>
    </div>

    <!-- Add Payment Modal -->
    <div id="paymentModal" class="modal-overlay d-none">
        <form class="modal-box" onsubmit="submitPayment(event)">
            <span class="close-btn" onclick="togglePaymentModal()">&times;</span>
            <h4 class="mb-3">Add Payment</h4>

            <div class="mb-2">
                <label>Vendor</label>
                <select type="text" id="p_vendor" class="form-control" disabled></select>
                </select>
            </div>
            <div class="mb-2">
                <label>Stall</label>
                <select id="p_stall" class="form-select"></select>
            </div>
            <div class="mb-2">
                <label>Amount</label>
                <input type="number" id="p_amount" class="form-control">
            </div>
            <div class="mb-2">
                <label>Payment Date</label>
                <input type="date" id="p_date" class="form-control">
            </div>
            <div class="mb-2">
                <label>Method</label>
                <select id="p_method" class="form-select">
                    <option>Cash</option>
                    <option>GCash</option>
                    <option>Bank</option>
                </select>
            </div>
            <div class="mb-2">
                <label>Status</label>
                <select id="p_status" class="form-select">
                    <option value="Pending">Pending</option>
                    <option value="Complete">Complete</option>
                    <option value="Failed">Failed</option>
                </select>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-secondary me-2" onclick="togglePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
    <!-- Add Violation Modal -->
    <div id="violationModal" class="modal-overlay d-none">
        <form class="modal-box" onsubmit="submitViolation(event)">
            <span class="close-btn" onclick="toggleViolationModal()">
                &times;
            </span>
            <h4 class="mb-3">Add Violation</h4>

            <div class="mb-2">
                <label>Vendor</label>
                <select id="vio_vendor" class="form-select" disabled></select>
            </div>

            <div class="mb-2">
                <label>Stall</label>
                <select id="vio_stall" class="form-select"></select>
            </div>

            <div class="mb-2">
                <label>Violation Type</label>
                <input type="text" id="vio_type" class="form-control" placeholder="Violation type">
            </div>

            <div class="mb-2">
                <label>Description</label>
                <textarea id="vio_description" class="form-control"></textarea>
            </div>

            <div class="mb-2">
                <label>Date</label>
                <input type="date" id="vio_date" class="form-control">
            </div>

            <div class="mb-2">
                <label>Status</label>
                <select id="vio_status" class="form-select">
                    <option value="Open">Open</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Warning">Warning</option>
                </select>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-secondary me-2" onclick="toggleViolationModal()">
                    Cancel
                </button>

                <button type="submit" class="btn btn-success">
                    Save
                </button>
            </div>

        </form>
    </div>

    <!-- Edit Violation Modal -->
    <div id="editViolationModal" class="modal-overlay d-none">
        <form class="modal-box" onsubmit="submitViolation(event, true)">
            <span class="close-btn" onclick="toggleEditViolationModal()">
                &times;
            </span>
            <h4 class="mb-3">Edit Violation</h4>

            <div class="mb-2">
                <label>Vendor</label>
                <input id="vio_vendor-e" class="form-select" disabled />
            </div>

            <div class="mb-2">
                <label>Stall</label>
                <input id="vio_stall-e" class="form-select" disabled />
            </div>

            <div class="mb-2">
                <label>Status</label>
                <select id="vio_status-e" class="form-select">
                    <option value="Open">Open</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Warning">Warning</option>
                </select>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-secondary me-2" onclick="toggleEditViolationModal()">
                    Cancel
                </button>

                <button type="submit" class="btn btn-success">
                    Save
                </button>
            </div>

        </form>
    </div>

    <script>
        const API_BASE = 'http://localhost:3000/api';
        let vendorIdGlobal;
        let stallIdGlobal;
        let violationIdGlobal;

        // ─── PAGE NAVIGATION ──────────────────────────────────────
        function loadPage(page) {
            document.querySelectorAll('.page').forEach(p => p.classList.add('d-none'));
            document.getElementById(page).classList.remove('d-none');
            document.getElementById("pageTitle").innerText =
                page.charAt(0).toUpperCase() + page.slice(1);
            document.querySelectorAll('.menu').forEach(m => m.classList.remove('active'));
            event.target.closest('a').classList.add('active');

            if (page === 'payments') loadPayments();
            if (page === 'vendors') loadVendors();
            if (page === 'stalls') loadStalls();
            if (page === 'violations') loadViolations();
            if (page === 'dashboard') loadDashboard();
        }

        // ─── VENDOR MODAL ─────────────────────────────────────────
        function toggleForm(isEdit = false, vendor = null, vendorId = null) {
            const modal = document.getElementById("vendorModal");
            modal.classList.toggle("d-none");

            if (isEdit && vendor) {
                document.getElementById("vendorModal").querySelector('h4').innerText = "Edit Vendor";
                document.getElementById("saveVendorBtn").innerHTML = `
                    <span id="saveVendorSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    Update
                `;
                vendorIdGlobal = vendorId;
                document.getElementById('v_name').value = vendor.name || '';
                document.getElementById('v_contact_no').value = vendor.contact_no || '';
                document.getElementById('v_email').value = vendor.email || '';
                document.getElementById('v_stall_type').value = vendor.stall_type || '';
                document.getElementById('v_status').value = vendor.status || '';
                loadAvailableStalls(vendor.stall_no);
            } else {
                document.getElementById("vendorModal").querySelector('h4').innerText = "Register New Vendor";
                document.getElementById("saveVendorBtn").innerHTML = `
                    <span id="saveVendorSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    Save
                `;
                clearVendorForm();
                loadAvailableStalls();
            }
        }

        // ─── STALL MODAL ──────────────────────────────────────────
        function stallModal(stall = null, stallId = null) {
            const modal = document.getElementById("stallModal");
            modal.classList.toggle("d-none");

            if (stall) {
                stallIdGlobal = stallId;
                document.getElementById('stall_no').value = stall.stall_no || '';
                document.getElementById('stall_status').value = stall.status || 'Available';
            }
        }

        async function editStall() {
            const stall_no = document.getElementById('stall_no').value.trim();
            const status = document.getElementById('stall_status').value;

            if (!stall_no || !status) {
                alert('Please fill in all required fields.');
                return;
            }

            const btn = document.getElementById('saveStallBtn');
            btn.disabled = true;

            try {
                const res = await fetch(`${API_BASE}/stall/${stallIdGlobal}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ stall_no, status })
                });

                const data = await res.json();

                if (!res.ok) {
                    showFeedback(data.message || 'Failed to update stall.', 'danger');
                    return;
                }

                stallModal();
                loadStalls();
            } catch (err) {
                showFeedback('Network error. Could not reach the server.', 'danger');
                console.error(err);
            } finally {
                btn.disabled = false;
            }
        }

        async function loadAvailableStalls(selected = null) {
            try {
                selected = Number(selected);
                const res = await fetch(`${API_BASE}/stall`);
                if (!res.ok) throw new Error("Failed to fetch stalls");
                document.getElementById('hidden_stall_no').value = selected || '';

                const stalls = await res.json();
                const availableStalls = stalls.filter(s => s.stall_no === selected || s.status === 'Available');
                const select = document.getElementById('v_stall_no');

                select.innerHTML = '<option value="">Select Stall</option>';
                availableStalls.forEach(s => {
                    const isSelected = selected === s.stall_no ? 'selected' : '';
                    select.innerHTML += `<option value="${s.stall_no}" data-stall-id="${s.stall_id}" ${isSelected}>${s.stall_no} ${isSelected ? ' (Currently assigned)' : ''}</option>`;
                });
            } catch (err) {
                console.error("Error loading stalls:", err);
                document.getElementById('v_stall_no').innerHTML = '<option value="">Failed to load stalls</option>';
            }
        }

        window.onclick = function (e) {
            const vendorModal = document.getElementById("vendorModal");
            if (e.target === vendorModal) vendorModal.classList.add("d-none");

            const paymentModal = document.getElementById("paymentModal");
            if (e.target === paymentModal) paymentModal.classList.add("d-none");
        };

        // ─── FEEDBACK ─────────────────────────────────────────────
        function showFeedback(msg, type = 'success') {
            const el = document.getElementById('vendorFeedback');
            el.className = `alert alert-${type}`;
            el.textContent = msg;
            el.style.display = 'block';
            setTimeout(() => { el.style.display = 'none'; }, 4000);
        }

        // ─── STATUS BADGES ────────────────────────────────────────
        function statusBadge(status) {
            const cls = {
                'Maintenance': 'badge-inactive',
                'Occupied': 'badge-pending',
                'Available': 'badge-active',
            }[status] || 'badge-pending';
            return `<span class="status-badge ${cls}">${status}</span>`;
        }

        function paymentStatusBadge(status) {
            const cls = {
                'Complete': 'badge-active',
                'Pending': 'badge-pending',
                'Failed': 'badge-inactive'
            }[status] || 'badge-pending';
            return `<span class="status-badge ${cls}">${status}</span>`;
        }

        // ─── RENDER VENDORS ───────────────────────────────────────
        function renderVendors(vendors) {
            const loading = document.getElementById('vendorLoadingMsg');
            const table = document.getElementById('vendorsTable');
            const empty = document.getElementById('vendorEmptyMsg');
            const tbody = document.getElementById('vendorTableBody');

            loading.classList.add('d-none');

            if (!vendors || vendors.length === 0) {
                table.classList.add('d-none');
                empty.classList.remove('d-none');
                return;
            }

            empty.classList.add('d-none');
            table.classList.remove('d-none');

            tbody.innerHTML = vendors.map((v, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${escHtml(v.name)}</td>
                    <td>${escHtml(v.contact_no)}</td>
                    <td>${escHtml(v.email)}</td>
                    <td><button class="btn btn-success" onclick='toggleStallManagement(${JSON.stringify(v)})'>Manage stalls</button></td>
                    <td>${statusBadge(v.status)}</td>
                    <td>
                        <button class="action-btn delete" title="Delete vendor" onclick="deleteVendor(${v.vendor_id})">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                        <button class="action-btn edit" title="Edit vendor" onclick='toggleForm(true, ${JSON.stringify(v)}, ${v.vendor_id})'>
                            <i class="ri-edit-line"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // ─── RENDER STALLS ────────────────────────────────────────
        function renderStalls(stalls) {
            const tbody = document.getElementById('stallTableBody');

            if (!stalls || stalls.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No stalls found.</td></tr>`;
                return;
            }

            tbody.innerHTML = stalls.map(s => `
                <tr>
                    <td>${escHtml(s.stall_no)}</td>
                    <td>${escHtml(s.vendor_name || '—')}</td>
                    <td>${statusBadge(s.status || 'Available')}</td>
                    <td>
                        <button class="action-btn edit" title="Edit stall" onclick='stallModal(${JSON.stringify(s)}, ${s.stall_id})'>
                            <i class="ri-edit-line"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // ─── RENDER PAYMENTS ──────────────────────────────────────
        function renderPayments(payments) {
            const tbody = document.getElementById('paymentTableBody');

            if (!payments || payments.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted">No payment records found.</td>
                    </tr>
                `;
                updateSummaryCards([]);
                return;
            }

            tbody.innerHTML = payments.map((p, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${escHtml(p.vendor_name)}</td>
                    <td>${escHtml(p.stall_no)}</td>
                    <td>₱${Number(p.amount).toLocaleString()}</td>
                    <td>${formatDate(p.payment_date)}</td>
                    <td>${paymentStatusBadge(p.status)}</td>
                    <td>${escHtml(p.payment_method)}</td>
                    <td>
                        <button class="action-btn edit" title="Edit payment" onclick='openEditPaymentModal(${JSON.stringify(p)})'>
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="action-btn delete" onclick="deletePayment(${p.payment_id})">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

            updateSummaryCards(payments);
        }

        // ─── SUMMARY CARDS ────────────────────────────────────────
        function updateSummaryCards(payments) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let totalCollected = 0;
            let totalPending = 0;
            let transactions = payments.length;
            let overdue = 0;

            payments.forEach(p => {
                const amount = Number(p.amount) || 0;
                const status = p.status || '';

                if (status === 'Complete') {
                    totalCollected += amount;
                } else if (status === 'Pending') {
                    totalPending += amount;
                    const paymentDate = new Date(p.payment_date);
                    paymentDate.setHours(0, 0, 0, 0);
                    if (paymentDate < today) overdue++;
                } else if (status === 'Failed') {
                    const paymentDate = new Date(p.payment_date);
                    paymentDate.setHours(0, 0, 0, 0);
                    if (paymentDate < today) overdue++;
                }
            });

            document.querySelectorAll('.summary-card')[0].querySelector('h6').textContent =
                `₱${totalCollected.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
            document.querySelectorAll('.summary-card')[1].querySelector('h6').textContent =
                `₱${totalPending.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
            document.querySelectorAll('.summary-card')[2].querySelector('h6').textContent = transactions;
            document.querySelectorAll('.summary-card')[3].querySelector('h6').textContent = overdue;
        }

        // ─── FORMAT DATE ──────────────────────────────────────────
        function formatDate(dateStr) {
            if (!dateStr) return '—';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // ─── EDIT PAYMENT MODAL ───────────────────────────────────
        let editPaymentId = null;

        function openEditPaymentModal(payment) {
            editPaymentId = payment.payment_id;
            document.getElementById('ep_amount').value = payment.amount;
            document.getElementById('ep_date').value = payment.payment_date
                ? payment.payment_date.substring(0, 10) : '';
            document.getElementById('ep_method').value = payment.payment_method || 'Cash';
            document.getElementById('ep_status').value = payment.status || 'Pending';
            document.getElementById('editPaymentModal').classList.remove('d-none');
        }

        function closeEditPaymentModal() {
            document.getElementById('editPaymentModal').classList.add('d-none');
            editPaymentId = null;
        }

        async function submitEditPayment(e) {
            e.preventDefault();

            const amount = document.getElementById('ep_amount').value;
            const payment_date = document.getElementById('ep_date').value;
            const payment_method = document.getElementById('ep_method').value;
            const status = document.getElementById('ep_status').value;

            if (!amount || !payment_date) {
                alert("Please fill all fields");
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/payment/${editPaymentId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ amount, payment_date, payment_method, status })
                });

                const data = await res.json();

                if (!res.ok) {
                    alert(data.message || "Failed to update payment");
                    return;
                }

                closeEditPaymentModal();
                loadPayments();
            } catch (err) {
                console.error(err);
                alert("Network error");
            }
        }
        function toggleViolationModal(violation = null) {
            const modal = document.getElementById("violationModal");

            modal.classList.toggle("d-none");

            if (!modal.classList.contains("d-none")) {
                loadViolationDropdowns();
            }
        }

        function toggleEditViolationModal(violation = null) {
            const modal = document.getElementById("editViolationModal");

            modal.classList.toggle("d-none");

            if (!modal.classList.contains("d-none")) {
                violationIdGlobal = violation.violation_id;
                document.getElementById('vio_stall-e').value = violation.stall_no || '';
                document.getElementById('vio_vendor-e').value = violation.vendor_name || '';
                document.getElementById('vio_status-e').value = violation.status || 'Open';
            }
        }

        async function loadViolationDropdowns(selected = null) {
            try {
                selected = Number(selected);
                const [vendorsRes, stallsRes] = await Promise.all([
                    fetch(`${API_BASE}/vendor`),
                    fetch(`${API_BASE}/stall`)
                ]);

                const vendors = await vendorsRes.json();
                const stalls = await stallsRes.json();

                const vendorSelect = document.getElementById('vio_vendor');
                vendorSelect.innerHTML = '<option value="">Select Vendor</option>';
                const stallSelect = document.getElementById('vio_stall');
                stallSelect.innerHTML = '<option value="">Select Stall</option>';

                vendors.forEach(v => {
                    const isSelected = selected === v.vendor_id ? 'selected' : '';
                    vendorSelect.innerHTML += `<option value="${v.vendor_id}" ${isSelected}>${v.name}</option>`;
                });

                stalls.forEach(s => {
                    if (s.status !== 'Occupied') return;
                    const isSelected = selected === s.stall_id ? 'selected' : '';
                    stallSelect.innerHTML += `<option value="${s.stall_id}" data-vendor-name="${s.vendor_name || ''}" ${isSelected}>${s.stall_no}</option>`;
                });
            } catch (err) {
                console.error(err);
            }
        }

        document.getElementById('vio_stall').addEventListener('change', function () {
            const selectedOption = this.selectedOptions[0];
            const vendorName = selectedOption?.dataset.vendorName || '';
            const vendorSelect = document.getElementById('vio_vendor');

            if (vendorName) {
                for (let i = 0; i < vendorSelect.options.length; i++) {
                    if (vendorSelect.options[i].text === vendorName) {
                        vendorSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });

        async function submitViolation(e, isEdit = false) {
            if (isEdit) {
                const status = document.getElementById('vio_status-e').value;

                try {
                    const res = await fetch(`${API_BASE}/violation/${violationIdGlobal}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            status
                        })
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        alert(data.message || 'Failed to edit violation');
                        return;
                    }

                    toggleEditViolationModal();
                    loadViolations();

                } catch (err) {
                    console.error(err);
                }
            } else {
                e.preventDefault();

                const vendor_id = document.getElementById('vio_vendor').value;
                const stall_id = document.getElementById('vio_stall').value;
                const violation_type = document.getElementById('vio_type').value;
                const description = document.getElementById('vio_description').value;
                const violation_date = document.getElementById('vio_date').value;
                const status = document.getElementById('vio_status').value;

                try {

                    const res = await fetch(`${API_BASE}/violation`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            vendor_id,
                            stall_id,
                            violation_type,
                            description,
                            violation_date,
                            status
                        })
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        alert(data.message || 'Failed to add violation');
                        return;
                    }

                    toggleViolationModal();
                    loadViolations();

                } catch (err) {
                    console.error(err);
                }
            }
        }

        async function loadViolations() {
            try {
                const res = await fetch(`${API_BASE}/violation`);

                const violations = await res.json();

                const tbody =
                    document.getElementById('violationTableBody');
                if (!violations.length) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8"
                                class="text-center text-muted">
                                No violations found.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = violations.map((v, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${v.vendor_name}</td>
                        <td>${v.stall_no}</td>
                        <td>${v.violation_type}</td>
                        <td>${v.description}</td>
                        <td>${formatDate(v.violation_date)}</td>
                        <td>${v.status}</td>

                        <td>
                            <button
                                class="action-btn delete"
                                onclick="deleteViolation(${v.violation_id})">

                                <i class="ri-delete-bin-line"></i>
                            </button>

                            <button
                                class="action-btn edit"
                                onclick='toggleEditViolationModal(${JSON.stringify(v)})'>

                                <i class="ri-edit-line"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');

            } catch (err) {
                console.error(err);
            }
        }

        async function deleteViolation(id) {

            if (!confirm('Delete this violation?')) return;

            try {

                await fetch(`${API_BASE}/violation/${id}`, {
                    method: 'DELETE'
                });

                loadViolations();

            } catch (err) {
                console.error(err);
            }
        }

        // ─── LOAD DATA ────────────────────────────────────────────
        async function loadVendors() {
            const loading = document.getElementById('vendorLoadingMsg');
            const table = document.getElementById('vendorsTable');
            const empty = document.getElementById('vendorEmptyMsg');

            loading.classList.remove('d-none');
            table.classList.add('d-none');
            empty.classList.add('d-none');

            try {
                const res = await fetch(`${API_BASE}/vendor`);
                if (!res.ok) throw new Error(`Server error: ${res.status}`);
                const data = await res.json();
                renderVendors(data);
            } catch (err) {
                loading.textContent = 'Failed to load vendors. Check your API connection.';
                console.error(err);
            }
        }

        async function loadStalls(isFetch = false) {
            try {
                const res = await fetch(`${API_BASE}/stall`);
                if (!res.ok) throw new Error(`Server error: ${res.status}`);
                const data = await res.json();
                if (isFetch) {
                    return data;
                } else {
                    renderStalls(data);
                }
            } catch (err) {
                console.error("Failed to load stalls:", err);
                document.getElementById('stallTableBody').innerHTML =
                    `<tr><td colspan="4" class="text-danger text-center">Failed to load stalls</td></tr>`;
            }
        }

        async function loadPayments() {
            try {
                const res = await fetch(`${API_BASE}/payment`);
                if (!res.ok) throw new Error("Failed to fetch payments");
                const data = await res.json();
                renderPayments(data);
            } catch (err) {
                console.error("Error loading payments:", err);
                document.getElementById('paymentTableBody').innerHTML = `
                    <tr>
                        <td colspan="8" class="text-danger text-center">Failed to load payments</td>
                    </tr>
                `;
            }
        }

        // ─── PAYMENT MODAL ────────────────────────────────────────
        function togglePaymentModal() {
            const modal = document.getElementById("paymentModal");
            modal.classList.toggle("d-none");

            if (!modal.classList.contains("d-none")) {
                loadPaymentDropdowns();
            }
        }

        async function loadPaymentDropdowns(selected = null) {
            try {
                const [vendorsRes, stallsRes] = await Promise.all([
                    fetch(`${API_BASE}/vendor`),
                    fetch(`${API_BASE}/stall`)
                ]);

                const vendors = await vendorsRes.json();
                const stalls = await stallsRes.json();

                const vendorSelect = document.getElementById('p_vendor');
                vendorSelect.innerHTML = '<option value="">Select Vendor</option>';
                const stallSelect = document.getElementById('p_stall');
                stallSelect.innerHTML = '<option value="">Select Stall</option>';

                vendors.forEach(v => {
                    const isSelected = selected === v.vendor_id ? 'selected' : '';
                    vendorSelect.innerHTML += `<option value="${v.vendor_id}" ${isSelected}>${v.name}</option>`;
                });

                stalls.forEach(s => {
                    if (s.status !== 'Occupied') return;
                    const isSelected = selected === s.stall_id ? 'selected' : '';
                    stallSelect.innerHTML += `<option value="${s.stall_id}" data-vendor-name="${s.vendor_name || ''}" ${isSelected}>${s.stall_no}</option>`;
                });
            } catch (err) {
                console.error(err);
            }
        }

        document.getElementById('p_stall').addEventListener('change', function () {
            const selectedOption = this.selectedOptions[0];
            const vendorName = selectedOption?.dataset.vendorName || '';
            const vendorSelect = document.getElementById('p_vendor');

            if (vendorName) {
                for (let i = 0; i < vendorSelect.options.length; i++) {
                    if (vendorSelect.options[i].text === vendorName) {
                        vendorSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });

        async function submitPayment(e) {
            e.preventDefault();

            const vendor_id = document.getElementById('p_vendor').value;
            const stall_id = document.getElementById('p_stall').value;
            const amount = document.getElementById('p_amount').value;
            const payment_date = document.getElementById('p_date').value;
            const payment_method = document.getElementById('p_method').value;
            const status = document.getElementById('p_status').value;

            if (!vendor_id || !stall_id || !amount || !payment_date) {
                alert("Please fill all fields");
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/payment`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ vendor_id, stall_id, amount, payment_date, payment_method, status })
                });

                const data = await res.json();

                if (!res.ok) {
                    alert(data.message || "Failed to add payment");
                    return;
                }

                togglePaymentModal();
                loadPayments();
            } catch (err) {
                console.error(err);
                alert("Network error");
            }
        }

        async function deletePayment(id) {
            if (!confirm('Delete this payment?')) return;

            try {
                const res = await fetch(`${API_BASE}/payment/${id}`, { method: 'DELETE' });
                const data = await res.json();

                if (!res.ok) {
                    alert(data.message || 'Failed to delete');
                    return;
                }

                loadPayments();
            } catch (err) {
                console.error(err);
                alert("Network error");
            }
        }

        // ─── VENDOR CRUD ──────────────────────────────────────────
        async function submitVendor() {
            const isEdit = document.getElementById("vendorModal").querySelector('h4').textContent.includes("Edit Vendor");

            if (isEdit) {
                await editVendor(vendorIdGlobal);
                vendorIdGlobal = null;
            } else {
                await addVendor();
            }
        }

        async function addVendor() {
            const name = document.getElementById('v_name').value.trim();
            const contact_no = document.getElementById('v_contact_no').value.trim();
            const email = document.getElementById('v_email').value.trim();
            const stall_no = document.getElementById('v_stall_no').value.trim();
            const stall_id = document.getElementById('v_stall_no').selectedOptions[0]?.dataset.stallId;
            const status = document.getElementById('v_status').value;
            const stall_type = document.getElementById('v_stall_type').value.trim();

            if (!name || !contact_no || !email || !stall_no || !status || !stall_type) {
                showFeedback('Please fill in all required fields.', 'warning');
                return;
            }

            const btn = document.getElementById('saveVendorBtn');
            const spinner = document.getElementById('saveVendorSpinner');
            btn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const res = await fetch(`${API_BASE}/vendor`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, contact_no, email, stall_no, status, stall_type, stall_id })
                });

                const data = await res.json();

                if (!res.ok) {
                    showFeedback(data.message || 'Failed to add vendor.', 'danger');
                    return;
                }

                toggleForm();
                showFeedback('Vendor added successfully!', 'success');
                loadVendors();
            } catch (err) {
                showFeedback('Network error. Could not reach the server.', 'danger');
                console.error(err);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        async function editVendor(id) {
            const name = document.getElementById('v_name').value.trim();
            const contact_no = document.getElementById('v_contact_no').value.trim();
            const email = document.getElementById('v_email').value.trim();
            const stall_no = document.getElementById('v_stall_no').value.trim();
            const status = document.getElementById('v_status').value;
            const stall_type = document.getElementById('v_stall_type').value.trim();
            const hiddenStallNo = document.getElementById('hidden_stall_no').value.trim();

            if (!name || !contact_no || !email || !stall_no || !status || !stall_type) {
                showFeedback('Please fill in all required fields.', 'warning');
                return;
            }

            console.log("idhere", id)
            const btn = document.getElementById('saveVendorBtn');
            const spinner = document.getElementById('saveVendorSpinner');
            btn.disabled = true;
            spinner.classList.remove('d-none');

            try {
                const res = await fetch(`${API_BASE}/vendor/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, contact_no, email, stall_no, status, stall_type })
                });

                const data = await res.json();

                if (!res.ok) {
                    showFeedback(data.message || 'Failed to update vendor.', 'danger');
                    return;
                }

                const updateStallRes = await fetch(`${API_BASE}/stall/remove-from-vendor`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ stall_no: hiddenStallNo })
                });

                if (!updateStallRes.ok) {
                    showFeedback(data.message || 'Failed to update stall assignment.', 'danger');
                    return;
                }

                toggleForm();
                showFeedback('Vendor updated successfully!', 'success');
                loadVendors();
            } catch (err) {
                showFeedback('Network error. Could not reach the server.', 'danger');
                console.error(err);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        async function deleteVendor(id) {
            if (!confirm('Are you sure you want to delete this vendor?')) return;

            try {
                const res = await fetch(`${API_BASE}/vendor/${id}`, { method: 'DELETE' });
                const data = await res.json();

                if (!res.ok) {
                    showFeedback(data.message || 'Failed to delete vendor.', 'danger');
                    return;
                }

                showFeedback('Vendor deleted.', 'success');
                loadVendors();
            } catch (err) {
                showFeedback('Network error. Could not delete vendor.', 'danger');
                console.error(err);
            }
        }

        function clearVendorForm() {
            ['v_name', 'v_contact_no', 'v_email'].forEach(id => {
                document.getElementById(id).value = '';
            });
            document.getElementById('v_stall_type').selectedIndex = 0;
            document.getElementById('v_status').selectedIndex = 0;
        }

        function escHtml(str) {
            if (str == null) return '—';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        window.onload = function () {
            loadDashboard();
        };

        async function loadDashboard() {
            await loadStallBar();
            await loadActiveVendors();
            await loadPendingPaymentsCount();
            await loadOpenViolationsCount();
        }

        async function loadStallBar() {
            const stalls = await loadStalls(true);

            const availableCount = stalls.filter(s => s.status === 'Available').length;
            const occupiedCount = stalls.filter(s => s.status === 'Occupied').length;

            const total = stalls.length;

            const availablePercent = (availableCount / total) * 100;
            const occupiedPercent = (occupiedCount / total) * 100;

            document.getElementById('stallCount').textContent = total;
            document.getElementById('dashboardAvailable').textContent =
                `Available - ${availableCount}/${total}`;

            document.getElementById('dashboardOccupied').textContent =
                `Occupied - ${occupiedCount}/${total}`;

            // Update progress bar widths
            document.getElementById('availableProgress').style.width =
                `${availablePercent}%`;

            document.getElementById('occupiedProgress').style.width =
                `${occupiedPercent}%`;

            document.getElementById('availableProgress').textContent =
                `${availablePercent.toFixed(0)}%`;

            document.getElementById('occupiedProgress').textContent =
                `${occupiedPercent.toFixed(0)}%`;
        }
        async function loadActiveVendors() {
            try {
                const res = await fetch(`${API_BASE}/vendor`);
                if (!res.ok) throw new Error(`Server error: ${res.status}`);
                const data = await res.json();
                const activeVendors = data.filter(v => v.status === 'Active');
                document.getElementById('activeVendorCount').textContent = activeVendors.length;
            } catch (err) {
                console.error("Failed to load active vendors:", err);
                document.getElementById('activeVendorCount').textContent = '—';
            }
        }
        async function loadPendingPaymentsCount() {
            try {
                const res = await fetch(`${API_BASE}/payment`);
                if (!res.ok) throw new Error(`Server error: ${res.status}`);
                const data = await res.json();
                const pendingPayments = data.filter(p => p.status === 'Pending');
                document.getElementById('pendingPaymentCount').textContent = pendingPayments.length;
            } catch (err) {
                console.error("Failed to load pending payments:", err);
                document.getElementById('pendingPaymentCount').textContent = '—';
            }
        }
        async function loadOpenViolationsCount() {
            try {
                const res = await fetch(`${API_BASE}/violation`);
                if (!res.ok) throw new Error(`Server error: ${res.status}`);
                const data = await res.json();
                const openViolations = data.filter(v => v.status === 'Open');
                document.getElementById('openViolationPayment').textContent = openViolations.length;
            } catch (err) {
                console.error("Failed to load open violations:", err);
                document.getElementById('openViolationPayment').textContent = '—';
            }
        }

        let allStalls = [];
        let selectedStalls = new Set();

        async function toggleStallManagement(vendor = null) {
            const modal = document.getElementById("stallManagementModal");
            modal.classList.toggle("d-none");

            if (vendor) {
                await loadStallsManagement(vendor)
            }
        }

        function addStall() {
            const container = document.getElementById("stallContainer");

            const firstGroup = container.querySelector(".stall-group");
            const clone = firstGroup.cloneNode(true);

            const stallSelect = clone.querySelector(".stall-select");
            const typeSelect = clone.querySelector(".stall-type");

            // reset safely
            if (stallSelect) {
                stallSelect.value = "";
                stallSelect.onchange = (e) => handleChange(e.target);
                renderOptions(stallSelect);
            }

            if (typeSelect) {
                typeSelect.value = "Food";
            }

            container.appendChild(clone);

            refreshAllDropdowns();
            updateRemoveButtons();
        }

        function renderOptions(select) {
            const currentValue = Number(select.value || 0);

            select.innerHTML = `<option value="">Select Stall</option>`;

            allStalls.forEach(stall => {
                console.log("stallid", stall.stall_id)
                const id = stall.stall_id;

                // allow current selection
                if (selectedStalls.has(id) && id !== currentValue) {
                    return;
                }

                const option = document.createElement("option");
                option.value = id;
                option.textContent = stall.stall_no;

                select.appendChild(option);
            });

            select.value = currentValue || "";
        }

        function removeStall(button) {
            const group = button.closest(".stall-group");
            group.remove();

            updateRemoveButtons();
        }

        function updateRemoveButtons() {
            const groups = document.querySelectorAll(".stall-group");

            groups.forEach(group => {
                const btn = group.querySelector(".remove-btn");

                if (groups.length === 1) {
                    btn.disabled = true;   // disable if only 1
                } else {
                    btn.disabled = false;  // enable if more than 1
                }
            });
        }

        async function loadStallsManagement(vendor = null) {
            document.getElementById('vs_vendor_id').value = vendor.vendor_id || '';
            document.getElementById('vs_name').value = vendor.name || '';
            allStalls = await loadStalls(true);
            const vendorStalls = allStalls.filter(stall => stall.vendor_id === vendor.vendor_id)
            allStalls = allStalls.filter(stall => stall.status === "Available")

            console.log("herehere", vendorStalls)

            document.querySelectorAll(".stall-no").forEach(select => {
                populateStallDropdown(select);
            });
        }

        function populateStallDropdown(select) {
            select.innerHTML = `<option value="">Select Stall</option>`;

            allStalls.forEach(stall => {
                const option = document.createElement("option");
                option.value = stall.stall_id;
                option.textContent = stall.stall_no;

                select.appendChild(option);
            });
        }

        function createDropdown() {
            const select = document.createElement("select");
            select.className = "form-select stall-select";
            select.name = "stall_no[]";

            select.onchange = (e) => handleChange(e.target);

            return select;
        }

        function refreshAllDropdowns() {
            document.querySelectorAll(".stall-select").forEach(select => {
                renderOptions(select);
            });
        }

        function handleChange() {
            selectedStalls = new Set();

            document.querySelectorAll(".stall-select").forEach(select => {
                if (select.value) {
                    selectedStalls.add(Number(select.value));
                }
            });

            refreshAllDropdowns();
        }
        // run on page load
        updateRemoveButtons();

        async function saveStalls() {
            try {

                const vendorId = document.getElementById("vs_vendor_id").value;

                const stallSelects = document.querySelectorAll(".stall-no");
                const typeSelects = document.querySelectorAll(".stall-type");

                let stalls = [];

                stallSelects.forEach((select, index) => {
                    if (select.value) {
                        stalls.push({
                            stall_id: Number(select.value),
                            stall_type: typeSelects[index].value
                        });
                    }
                });

                const data = {
                    stalls: stalls
                };

                const res = await fetch(`${API_BASE}/vendor/addStall/${vendorId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const response = await res.json();

                if (!response.ok) {
                    showFeedback(data.message || 'Failed to add vendor.', 'danger');
                    return;
                }
                toggleStallManagement();
            } catch (error) {
                console.log("errorhere", error)
            }
        }
    </script>

</body>

</html>
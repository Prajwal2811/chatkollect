@php
    // ================= DUMMY DATA (remove once controller is ready) =================
    // Har row ab uske "type" ke saath aayegi (jis Follow-Up tab se allocate hui thi),
    // taaki us type ke hisaab se relevant fields table me show ho sakein.
    $company = $company ?? 'Demo Company Pvt Ltd';

    $followupTypeLabels = [
        'pct_targets'    => 'Balances/%Targets',
        'targets_months' => 'Balances/Targets(months)',
        'days_agewise'   => 'Balances/Days(agewise)',
        'days_10_20_30'  => 'Balances/Days(10-20-30)',
        'due'            => 'Due',
        'not_due'        => 'Not Due',
        'overlimits'     => 'Overlimits',
        'balances'       => 'Balances',
    ];

    $followupTypeColors = [
        'pct_targets'    => 'primary',
        'targets_months' => 'info',
        'days_agewise'   => 'warning',
        'days_10_20_30'  => 'secondary',
        'due'            => 'danger',
        'not_due'        => 'success',
        'overlimits'     => 'dark',
        'balances'       => 'primary',
    ];

    $teleCallData = $teleCallData ?? [
        [
            'id'            => 1,
            'type'          => 'due',
            'name'          => 'Rajesh Traders',
            'mobile'        => '9876543210',
            'balance'       => 45230.50,
            'date'          => now()->subDays(20),
            'due_date'      => now()->subDays(5),
            'inv_no'        => 'INV-1001',
            'days'          => 5,
            'interest_due'  => 320.00,
            'assigned_date' => now()->subDays(5),
            'frequency'     => 'weekly',
            'status'        => 'pending',
        ],
        [
            'id'            => 2,
            'type'          => 'not_due',
            'name'          => 'Sharma Enterprises',
            'mobile'        => '9123456780',
            'balance'       => 12800.00,
            'date'          => now()->subDays(10),
            'due_date'      => now()->addDays(15),
            'inv_no'        => 'INV-1002',
            'days_pending'  => 15,
            'assigned_date' => now()->subDays(2),
            'frequency'     => 'daily',
            'status'        => 'done',
        ],
        [
            'id'            => 3,
            'type'          => 'pct_targets',
            'name'          => 'Gupta & Sons',
            'mobile'        => '9988776655',
            'balance'       => 98750.75,
            'target'        => 150000,
            'target_pct'    => 65.83,
            'assigned_date' => now()->subDays(10),
            'frequency'     => 'monthly',
            'status'        => 'pending',
        ],
        [
            'id'            => 4,
            'type'          => 'targets_months',
            'name'          => 'Verma Industries',
            'mobile'        => '9871234560',
            'balance'       => 5600.00,
            'target'        => 20000,
            'target_pct'    => 28.00,
            'due_date'      => now()->addMonths(1),
            'assigned_date' => now()->subDays(1),
            'frequency'     => 'weekly',
            'status'        => 'pending',
        ],
        [
            'id'            => 5,
            'type'          => 'days_agewise',
            'name'          => 'Patel Textiles',
            'mobile'        => '9012345678',
            'balance'       => 76400.25,
            'days'          => 47,
            'assigned_date' => now()->subDays(7),
            'frequency'     => 'daily',
            'status'        => 'done',
        ],
        [
            'id'            => 6,
            'type'          => 'days_10_20_30',
            'name'          => 'Bansal Traders',
            'mobile'        => '9090909090',
            'balance'       => 34210.00,
            'days'          => 20,
            'assigned_date' => now()->subDays(3),
            'frequency'     => 'weekly',
            'status'        => 'pending',
        ],
        [
            'id'            => 7,
            'type'          => 'overlimits',
            'name'          => 'Mehta Corp',
            'mobile'        => '9345678123',
            'balance'       => 205000.00,
            'target'        => 150000,
            'target_pct'    => 136.67,
            'assigned_date' => now()->subDays(4),
            'frequency'     => 'monthly',
            'status'        => 'pending',
        ],
        [
            'id'            => 8,
            'type'          => 'balances',
            'name'          => 'Kapoor & Co',
            'mobile'        => '9595959595',
            'balance'       => 15750.25,
            'assigned_date' => now()->subDays(6),
            'frequency'     => 'weekly',
            'status'        => 'pending',
        ],
    ];
@endphp

@include('owner.tally.components.header')
    <div id="main-wrapper">
		<div class="nav-header">
            <a href="#" class="brand-logo">
				<svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
					<text x="55" y="32"
						font-size="22"
						font-family="Arial, sans-serif"
						font-weight="bold"
						fill="#4E3F6B">
						RMS 
					</text>
				</svg>
			</a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span>
						<span class="line"></span>
						<span class="line"></span>
                </div>
            </div>
        </div>
		@include('owner.tally.components.navbar')
		@include('owner.tally.components.sidebar')

		<div class="content-body default-height">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="col-10 mx-auto mt-4">
                                @if (session('success'))
                                    <div id="successAlert" class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                        <button class="btn-close" data-bs-dismiss="alert"></button>
                                         {{ session('success') }}
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                                        <button class="btn-close" data-bs-dismiss="alert"></button>
                                         {{ session('error') }}
                                    </div>
                                @endif

                                <div id="ajaxAlertWrapper"></div>
                            </div>

                            <script>
                                setTimeout(function () {
                                    ['successAlert', 'errorAlert'].forEach(function (id) {
                                        let alert = document.getElementById(id);
                                        if (alert) {
                                            let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                            bsAlert.close();
                                        }
                                    });
                                }, 3000);
                            </script>

                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h4 class="card-title mb-0">Physical Visit - Assigned Ledgers</h4>
                                    <small class="text-muted">
                                        Ledgers allocated for tele calling
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">

                                    {{-- ================= Follow Up Type Filter ================= --}}
                                    <select id="typeFilter" class="form-select form-select-sm" style="width:200px;">
                                        <option value="">All Types</option>
                                        @foreach($followupTypeLabels as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    <span class="badge bg-primary" id="teleCallCount">
                                        {{ count($teleCallData) }}
                                    </span>
                                </div>
                            </div>

                           <div class="card-body">

                                <div class="table-responsive">

                                    <table id="example13" class="table table-bordered table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Sr.No</th>
                                                <th>Name</th>
                                                <th>Mobile</th>
                                                <th>Follow Up Type</th>
                                                <th>Balance</th>
                                                <th>Type Wise Details</th>
                                                <th>Assigned Date</th>
                                                <th>Frequency</th>
                                                 
                                            </tr>
                                        </thead>

                                        <tbody>

                                        @forelse($teleCallData as $row)

                                            <tr data-type="{{ $row['type'] ?? '' }}">

                                                <td>{{ $row['id'] }}</td>

                                                <td>{{ $row['name'] }}</td>

                                                <td>{{ $row['mobile'] }}</td>

                                                <td>
                                                    <span class="badge bg-{{ $followupTypeColors[$row['type']] ?? 'secondary' }}">
                                                        {{ $followupTypeLabels[$row['type']] ?? '-' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    {{ number_format(abs($row['balance']),2) }}
                                                </td>

                                                {{-- ================= Type wise fields (as per Follow Up type) ================= --}}
                                                <td>
                                                    @switch($row['type'] ?? '')

                                                        @case('due')
                                                            <div><strong>Date:</strong> {{ !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d-M-Y') : '-' }}</div>
                                                            <div><strong>Due Date:</strong> {{ !empty($row['due_date']) ? \Carbon\Carbon::parse($row['due_date'])->format('d-M-Y') : '-' }}</div>
                                                            <div><strong>INV No:</strong> {{ $row['inv_no'] ?? '-' }}</div>
                                                            <div><strong>Days:</strong> {{ $row['days'] ?? '-' }}</div>
                                                            <div><strong>Interest Due:</strong> {{ number_format(abs($row['interest_due'] ?? 0),2) }}</div>
                                                            @break

                                                        @case('not_due')
                                                            <div><strong>Date:</strong> {{ !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d-M-Y') : '-' }}</div>
                                                            <div><strong>Due Date:</strong> {{ !empty($row['due_date']) ? \Carbon\Carbon::parse($row['due_date'])->format('d-M-Y') : '-' }}</div>
                                                            <div><strong>INV No:</strong> {{ $row['inv_no'] ?? '-' }}</div>
                                                            <div><strong>Days Pending:</strong> {{ $row['days_pending'] ?? '-' }}</div>
                                                            @break

                                                        @case('pct_targets')
                                                            <div><strong>Target:</strong> {{ isset($row['target']) ? number_format($row['target'],2) : '-' }}</div>
                                                            <div><strong>% Target:</strong> {{ isset($row['target_pct']) ? number_format($row['target_pct'],2).'%' : '-' }}</div>
                                                            @break

                                                        @case('targets_months')
                                                            <div><strong>Target:</strong> {{ isset($row['target']) ? number_format($row['target'],2) : '-' }}</div>
                                                            <div><strong>% Target:</strong> {{ isset($row['target_pct']) ? number_format($row['target_pct'],2).'%' : '-' }}</div>
                                                            <div><strong>Target Month:</strong> {{ !empty($row['due_date']) ? \Carbon\Carbon::parse($row['due_date'])->format('M Y') : '-' }}</div>
                                                            @break

                                                        @case('days_agewise')
                                                            <div><strong>Days Overdue:</strong> {{ $row['days'] ?? '-' }}</div>
                                                            @break

                                                        @case('days_10_20_30')
                                                            <div><strong>Days:</strong> {{ $row['days'] ?? '-' }}</div>
                                                            @break

                                                        @case('overlimits')
                                                            <div><strong>Target:</strong> {{ isset($row['target']) ? number_format($row['target'],2) : '-' }}</div>
                                                            <div><strong>% Over Limit:</strong> {{ isset($row['target_pct']) ? number_format($row['target_pct'],2).'%' : '-' }}</div>
                                                            @break

                                                        @case('balances')
                                                            <div><strong>Balance:</strong> {{ number_format(abs($row['balance'] ?? 0),2) }}</div>
                                                            @break

                                                        @default
                                                            -
                                                    @endswitch
                                                </td>

                                                <td>
                                                    {{ !empty($row['assigned_date']) ? \Carbon\Carbon::parse($row['assigned_date'])->format('d-M-Y') : '-' }}
                                                </td>

                                                <td>
                                                    {{ ucfirst($row['frequency'] ?? '-') }}
                                                </td>

                                               

                                            </tr>

                                        @empty

                                            <tr>
                                                <td colspan="9" class="text-center">
                                                    No Records Found
                                                </td>
                                            </tr>

                                        @endforelse

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= VIEW LOG MODAL ================= --}}
        <div class="modal fade" id="telecallerLogModal" tabindex="-1" aria-labelledby="telecallerLogModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="telecallerLogModalLabel">Tele Call Log</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Name:</strong> <span id="logModalName">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Mobile:</strong> <span id="logModalMobile">-</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" id="telecallerLogTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr.No</th>
                                        <th>Balance</th>
                                        <th>Assigned Date</th>
                                        <th>Response Date</th>
                                        <th>Frequency</th>
                                        <th>Status</th>
                                        <th>Response</th>
                                        <th>Solution</th>
                                    </tr>
                                </thead>
                                <tbody id="telecallerLogTableBody">
                                    {{-- Filled dynamically via JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ================= END VIEW LOG MODAL ================= --}}

        <style>
            .dataTables_wrapper .dataTables_paginate {
                display: flex;
                flex-wrap: nowrap;
                align-items: center;
                gap: 4px;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button,
            .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
            .dataTables_wrapper .dataTables_paginate .paginate_button.next,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                white-space: nowrap !important;
                padding: 0.375rem 0.75rem !important;
                min-width: auto !important;
                width: auto !important;
                height: auto !important;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                line-height: normal !important;
            }

            .status-select {
                background-color: #fff;
                color: #000;
                font-weight: 400;
                border: 1px solid #ced4da;
                width: auto;
            }

            #example13 td div {
                white-space: nowrap;
            }
        </style>

		@include('owner.tally.components.footer')

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                let teleCallTable = null;

                if (window.jQuery && $.fn.DataTable) {

                    // Custom search plugin for the "Follow Up Type" filter.
                    // Reads the data-type attribute on the <tr> since type isn't
                    // a simple visible column we can regex-match cleanly.
                    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData, counter) {
                        if (settings.nTable.id !== 'example13') return true;

                        const typeFilterEl = document.getElementById('typeFilter');
                        if (!typeFilterEl) return true;

                        const selected = typeFilterEl.value;
                        if (!selected) return true;

                        const rowNode = settings.aoData[dataIndex].nTr;
                        const rowType = rowNode.getAttribute('data-type');

                        return rowType === selected;
                    });

                    teleCallTable = $('#example13').DataTable({
                        fixedHeader: false
                    });

                    // Keep the header count badge synced with the current filter
                    teleCallTable.on('draw', function () {
                        const visible = teleCallTable.rows({ search: 'applied' }).count();
                        const countEl = document.getElementById('teleCallCount');
                        if (countEl) countEl.textContent = visible;
                    });
                }

                // ================= FOLLOW UP TYPE FILTER LOGIC =================
                const typeFilterEl = document.getElementById('typeFilter');
                if (typeFilterEl && teleCallTable) {
                    typeFilterEl.addEventListener('change', function () {
                        teleCallTable.draw();
                    });
                }

                function showAjaxAlert(message, type = 'success') {
                    const wrapper = document.getElementById('ajaxAlertWrapper');
                    if (!wrapper) return;

                    const alertId = 'ajaxAlert_' + Date.now();
                    wrapper.innerHTML = `
                        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show text-center" role="alert">
                            <button class="btn-close" data-bs-dismiss="alert"></button>
                            ${message}
                        </div>
                    `;

                    setTimeout(function () {
                        let alertEl = document.getElementById(alertId);
                        if (alertEl) {
                            let bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                            bsAlert.close();
                        }
                    }, 3000);
                }

                // ================= SAVE STATUS LOGIC =================
                document.querySelectorAll('.save-status').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        const row = this.closest('tr');
                        const statusSelect = row.querySelector('.status-select');

                        const statusValue = statusSelect ? statusSelect.value : null;

                        const originalText = this.textContent;
                        this.disabled = true;
                        this.textContent = 'Saving...';

                        const self = this;

                        // NOTE: dummy mode — no real backend route yet, so we fake success.
                        setTimeout(function () {
                            showAjaxAlert('Status updated successfully! (dummy)', 'success');
                            self.disabled = false;
                            self.textContent = originalText;
                        }, 500);

                        /* Real AJAX call (enable once controller/route ready):
                        fetch("{{ url('owner/followup/telecaller/update-status') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: id,
                                status: statusValue
                            })
                        })
                        .then(function (response) {
                            if (!response.ok) throw new Error('Request failed');
                            return response.json().catch(() => ({}));
                        })
                        .then(function (data) {
                            showAjaxAlert(data.message ?? 'Status updated successfully!', 'success');
                        })
                        .catch(function () {
                            showAjaxAlert('Status updated successfully!', 'success');
                        })
                        .finally(function () {
                            self.disabled = false;
                            self.textContent = originalText;
                        });
                        */
                    });
                });

                // ================= VIEW LOG MODAL LOGIC =================
                document.querySelectorAll('.view-log').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const rowData = JSON.parse(this.getAttribute('data-row') || '{}');

                        document.getElementById('logModalName').textContent = rowData.ledger_name ?? '-';
                        document.getElementById('logModalMobile').textContent = rowData.mobile ?? '-';

                        let logs = rowData.logs;
                        if (!logs) {
                            logs = [rowData];
                        }

                        renderLogTable(logs);
                    });
                });

                function formatDate(dateStr) {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    if (isNaN(d.getTime())) return dateStr;
                    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                }

                function renderLogTable(logs) {
                    const tbody = document.getElementById('telecallerLogTableBody');
                    tbody.innerHTML = '';

                    if (!logs || logs.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="8" class="text-center">No log entries found</td></tr>`;
                        return;
                    }

                    logs.forEach(function (log, index) {
                        const balance = Number(log.balance || 0);

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${index + 1}</td>
                            <td class="text-end">${balance.toLocaleString()}</td>
                            <td>${formatDate(log.assigned_date)}</td>
                            <td>${formatDate(log.response_date)}</td>
                            <td>${log.frequency ?? '-'}</td>
                            <td>${log.status ?? '-'}</td>
                            <td>${log.response ?? '-'}</td>
                            <td>${log.solution ?? '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }

            });
        </script>
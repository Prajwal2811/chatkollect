@php
    $company = $company ?? '';
    $teleCallData = $teleCallData ?? [];

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

    // Follow Up Hub me jo label dikhta h wahi pill me dikhana h
    $followupTypeFull = [
        'balances'       => 'Follow Up-Balances',
        'pct_targets'    => 'Follow Up-Balances/%Targets',
        'targets_months' => 'Follow Up-Balances/Targets(months)',
        'days_agewise'   => 'Follow Up-Balances/Days(agewise)',
        'days_10_20_30'  => 'Follow Up-Balances/Days(10-20-30)',
        'due'            => 'Follow Up-Due',
        'not_due'        => 'Follow Up-Not Due',
        'overlimits'     => 'Follow Up Overlimits',
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
                                    <h4 class="card-title mb-0">Tele Caller - Assigned Ledgers</h4>
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
                                                <th style="width: 150px;">Name</th>
                                                <th>Mobile</th>
                                                <th style="width: 250px;">Follow Up Type</th>
                                                <th>Type Details</th>
                                                <th>Accountant Status</th>
                                                <th>Assigned Date</th>
                                                <th>Frequency</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                        {{-- Empty state DataTables khud handle karta hai --}}
                                        @foreach($teleCallData as $row)
                                            @php
                                                $rowType = $row['type'] ?? '';
                                                $fmtDate = fn($d) => !empty($d) ? \Carbon\Carbon::parse($d)->format('d-M-Y') : '-';
                                                $chips   = [];

                                                if ($rowType === 'pct_targets') {
                                                    $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];
                                                    $chips[] = ['label' => 'Target', 'value' => number_format($row['target'] ?? 0, 2)];
                                                    $chips[] = ['label' => '%Target', 'value' => ($row['target_pct'] ?? 0) . '%'];

                                                } elseif ($rowType === 'balances') {
                                                    $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];

                                                } elseif ($rowType === 'targets_months') {
                                                    $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];
                                                    $chips[] = ['label' => 'Target', 'value' => number_format($row['target'] ?? 0, 2)];
                                                    $chips[] = ['label' => '%Target', 'value' => ($row['target_pct'] ?? 0) . '%'];
                                                    $chips[] = ['label' => 'Target Month', 'value' => !empty($row['target_month']) ? \Carbon\Carbon::createFromFormat('Y-m', $row['target_month'])->format('M Y') : '-'];

                                                } elseif ($rowType === 'days_agewise') {
                                                    $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];
                                                    $chips[] = ['label' => 'Days', 'value' => $row['days'] ?? '-'];

                                                } elseif ($rowType === 'days_10_20_30') {
                                                    $chips[] = ['label' => 'Date', 'value' => $fmtDate($row['date'] ?? null)];
                                                    $chips[] = ['label' => 'Due Date', 'value' => $fmtDate($row['due_date'] ?? null)];
                                                    $chips[] = ['label' => 'INV No', 'value' => $row['inv_no'] ?? '-'];
                                                    $chips[] = ['label' => 'Days', 'value' => $row['days'] ?? '-'];
                                                    $chips[] = ['label' => 'Amount', 'value' => number_format(abs($row['balance']), 2)];
                                                    $chips[] = ['label' => 'Interest Due', 'value' => number_format($row['interest_due'] ?? 0, 2)];

                                                } elseif ($rowType === 'due') {
                                                    $chips[] = ['label' => 'Date', 'value' => $fmtDate($row['date'] ?? null)];
                                                    $chips[] = ['label' => 'Due Date', 'value' => $fmtDate($row['due_date'] ?? null)];
                                                    $chips[] = ['label' => 'INV No', 'value' => $row['inv_no'] ?? '-'];
                                                    $chips[] = ['label' => 'Days', 'value' => $row['days'] ?? '-'];
                                                    $chips[] = ['label' => 'Amount', 'value' => number_format($row['due'] ?? 0, 2)];
                                                    $chips[] = ['label' => 'Interest Due', 'value' => number_format($row['interest_due'] ?? 0, 2)];

                                                } elseif ($rowType === 'not_due') {
                                                    $chips[] = ['label' => 'Date', 'value' => $fmtDate($row['date'] ?? null)];
                                                    $chips[] = ['label' => 'Due Date', 'value' => $fmtDate($row['due_date'] ?? null)];
                                                    $chips[] = ['label' => 'INV No', 'value' => $row['inv_no'] ?? '-'];
                                                    $chips[] = ['label' => 'Days Pending', 'value' => $row['days_pending'] ?? '-'];
                                                    $chips[] = ['label' => 'Amount', 'value' => number_format($row['not_due'] ?? 0, 2)];

                                                } elseif ($rowType === 'overlimits') {
                                                    $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];

                                                } else {
                                                    $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance'] ?? 0), 2)];
                                                }

                                                $accountantStatus = $row['status'] ?: 'Not Assign Yet';
                                                $statusClass = match ($accountantStatus) {
                                                    'Pending'   => 'bg-warning',
                                                    'Responded' => 'bg-success',
                                                    default     => 'bg-secondary',
                                                };
                                            @endphp

                                            <tr data-type="{{ $rowType }}">

                                                <td>{{ $loop->iteration }}</td>

                                                <td>{{ $row['name'] }}</td>

                                                <td>{{ $row['mobile'] }}</td>

                                                <td class="followup-type-cell">
                                                    <span class="badge type-pill">{{ $followupTypeFull[$rowType] ?? '-' }}</span>
                                                </td>

                                                <td class="followup-details-cell">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($chips as $chip)
                                                            <span class="detail-chip">
                                                                <strong>{{ $chip['label'] }}:</strong> {{ $chip['value'] }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="badge accountant-status-badge {{ $statusClass }}">
                                                        {{ $accountantStatus }}
                                                    </span>
                                                </td>

                                                <td>{{ $fmtDate($row['assigned_date'] ?? null) }}</td>

                                                <td>{{ ucfirst($row['frequency'] ?? '-') }}</td>

                                            </tr>

                                        @endforeach

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

            /* ===== Follow Up Type pill + Type Details chips (same as Follow Up Hub) ===== */
            .type-pill {
                display: inline-block;
                background: #ece9f5;
                color: #402c67;
                font-weight: 600;
                font-size: 0.8rem;
                padding: 8px 14px;
                border-radius: 999px;
                white-space: normal;
                text-align: center;
            }

            .detail-chip {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                background: #fff;
                border: 1px solid #e2e6ea;
                border-radius: 8px;
                padding: 6px 12px;
                font-size: 0.8rem;
                color: #333;
                white-space: nowrap;
            }

            .detail-chip strong {
                color: #555;
                font-weight: 600;
            }

            .accountant-status-badge.bg-secondary {
                background-color: #6c757d !important;
            }
        </style>

		@include('owner.tally.components.footer')

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                let teleCallTable = null;

                if (window.jQuery && $.fn.DataTable) {

                    // Custom search plugin for the "Follow Up Type" filter.
                    // Reads the data-type attribute on the <tr>.
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
@include('owner.tally.components.header')
<div id="main-wrapper">
    <div class="nav-header">
        <a href="#" class="brand-logo">
            <svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
                <text x="55" y="32" font-size="22" font-family="Arial, sans-serif" font-weight="bold"
                    fill="#4E3F6B">RMS </text>
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

            @php
                $historyCollection = collect($history ?? []);
                $totalLogs    = $historyCollection->count();
                $totalLedgers = $historyCollection->pluck('ledger_id')->unique()->count();

                $isRowCleared = fn($r) => (int) ($r['clear_status'] ?? 0) === 1;
                $isRowPending = fn($r) => !$isRowCleared($r)
                    && empty($r['response'])
                    && strtolower((string) ($r['status'] ?? '')) !== 'responded';

                $clearedCount   = $historyCollection->filter($isRowCleared)->count();
                $pendingCount   = $historyCollection->filter($isRowPending)->count();
                $respondedCount = $totalLogs - $pendingCount - $clearedCount;

                $actionIconMap = [
                    'allocate_tele_call' => ['icon' => 'bi-telephone-plus', 'color' => '#6f42c1'],
                    'call' => ['icon' => 'bi-telephone', 'color' => '#0d6efd'],
                    'whatsapp' => ['icon' => 'bi-whatsapp', 'color' => '#198754'],
                    'physical_visit' => ['icon' => 'bi-geo-alt', 'color' => '#fd7e14'],
                    'escalation' => ['icon' => 'bi-exclamation-triangle', 'color' => '#dc3545'],
                    'no_action' => ['icon' => 'bi-dash-circle', 'color' => '#6c757d'],
                ];

                $buildTypeDetails = function ($row) {
                    $type = $row['type'] ?? '';
                    $fmt  = fn($v) => $v !== null && $v !== '' ? number_format(abs((float) $v), 2) : '-';
                    $dt   = fn($v) => !empty($v) ? \Carbon\Carbon::parse($v)->format('d-M-Y') : '-';
                    $sub  = !empty($row['sub_option']) ? $row['sub_option'] : '-';

                    return match ($type) {
                        'due' => [
                            'Date'         => $dt($row['date'] ?? null),
                            'Due Date'     => $dt($row['due_date'] ?? null),
                            'INV No'       => $row['inv_no'] ?? '-',
                            'Days'         => $row['days'] ?? '-',
                            'Amount'       => $fmt($row['balance'] ?? 0),
                            'Interest Due' => $fmt($row['interest_due'] ?? 0),
                        ],
                        'not_due' => [
                            'Date'         => $dt($row['date'] ?? null),
                            'Due Date'     => $dt($row['due_date'] ?? null),
                            'INV No'       => $row['inv_no'] ?? '-',
                            'Days Pending' => $row['days_pending'] ?? '-',
                            'Amount'       => $fmt($row['balance'] ?? 0),
                        ],
                        'overdue_10_20_30' => [
                            'Date'         => $dt($row['date'] ?? null),
                            'Due Date'     => $dt($row['due_date'] ?? null),
                            'INV No'       => $row['inv_no'] ?? '-',
                            'Days Overdue' => $sub,
                            'Amount'       => $fmt($row['balance'] ?? 0),
                            'Interest Due' => $fmt($row['interest_due'] ?? 0),
                        ],
                        'not_due_10_20_30' => [
                            'Date'      => $dt($row['date'] ?? null),
                            'Due Date'  => $dt($row['due_date'] ?? null),
                            'INV No'    => $row['inv_no'] ?? '-',
                            'Days Left' => $row['days_pending'] ?? '-',
                            'Amount'    => $fmt($row['balance'] ?? 0),
                        ],
                        'days_agewise' => [
                            'Balance' => $fmt($row['balance'] ?? 0),
                            'Days'    => $sub,
                        ],
                        'pct_targets' => [
                            'Balance' => $fmt($row['balance'] ?? 0),
                            'Target'  => number_format((float) ($row['target'] ?? 0), 2),
                            '%Target' => number_format((float) ($row['target_pct'] ?? 0), 2) . '%',
                        ],
                        'targets_months' => [
                            'Balance'      => $fmt($row['balance'] ?? 0),
                            'Target'       => number_format((float) ($row['target'] ?? 0), 2),
                            '%Target'      => number_format((float) ($row['target_pct'] ?? 0), 2) . '%',
                            'Target Month' => $sub,
                        ],
                        default => [
                            'Balance' => $fmt($row['balance'] ?? 0),
                        ],
                    };
                };
            @endphp

            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#402c67;">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ $totalLedgers }}</div>
                            <div class="stat-label">Ledgers Tracked</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#0d6efd;">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ $totalLogs }}</div>
                            <div class="stat-label">Total Log Entries</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#ffc107;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ $pendingCount }}</div>
                            <div class="stat-label">Pending Response</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#198754;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ $respondedCount }}</div>
                            <div class="stat-label">Responded</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card history-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-0">Follow Up History</h4>
                        <small class="text-muted">Ledger-wise assigned actions &amp; accountant responses — captured as a log</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary" id="historyCountBadge">{{ count($history ?? []) }}</span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="filter-card mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Follow Up Type</label>
                                <select id="typeFilter" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="balances">Balances</option>
                                    <option value="pct_targets">Balances/%Targets</option>
                                    <option value="targets_months">Balances/Targets(months)</option>
                                    <option value="days_agewise">Balances/Days(agewise)</option>
                                    <option value="overdue_10_20_30">Balances/Overdue(10-20-30)</option>
                                    <option value="not_due_10_20_30">Balances/Not Due(10-20-30)</option>
                                    <option value="due">Due</option>
                                    <option value="not_due">Not Due</option>
                                    <option value="overlimits">Overlimits</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Action</label>
                                <select id="actionFilter" class="form-select form-select-sm">
                                    <option value="">All Actions</option>
                                    <option value="allocate_tele_call">Allocate to tele call</option>
                                    <option value="call">Call</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="physical_visit">Physical visit</option>
                                    <option value="escalation">Escalation</option>
                                    <option value="no_action">No action</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="pending">Pending Response</option>
                                    <option value="responded">Responded</option>
                                    <option value="cleared">Cleared</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button type="button" id="clearFiltersBtn"
                                    class="btn btn-sm btn-outline-secondary w-100" title="Reset filters">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="tableView">
                        <div class="table-responsive">
                            <table id="historyTable" class="table table-bordered table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Sr.No</th>
                                        <th>Ledger Name</th>
                                        <th>Mobile</th>
                                        <th>Follow Up Type</th>
                                        <th style="min-width:220px;">Type Details</th>
                                        <th>Assigned Date</th>
                                        <th>Action Taken</th>
                                        <th>Frequency</th>
                                        <th>Response Date</th>
                                        <th>Response</th>
                                        <th>Solution</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($history ?? []) as $index => $row)
                                        @php
                                            $iconInfo    = $actionIconMap[$row['action'] ?? ''] ?? ['icon' => 'bi-clock-history', 'color' => '#6c757d'];
                                            $isCleared   = $isRowCleared($row);
                                            $isPending   = $isRowPending($row);
                                            $rowStatus   = $isCleared ? 'cleared' : ($isPending ? 'pending' : 'responded');
                                            $typeDetails = $buildTypeDetails($row);
                                        @endphp
                                        <tr data-ledger="{{ $row['ledger_name'] }}" data-type="{{ $row['type'] }}"
                                            data-action="{{ $row['action'] }}"
                                            data-status="{{ $rowStatus }}"
                                            data-allocation-date="{{ $row['allocation_date'] ?? '' }}"
                                            class="{{ $isCleared ? 'row-cleared' : ($isPending ? 'row-pending' : '') }}">

                                            <td>{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $row['ledger_name'] }}</td>
                                            <td>{{ $row['mobile'] }}</td>
                                            <td>
                                                <span
                                                    class="badge type-badge">{{ $row['type_label'] ?? $row['type'] }}</span>
                                            </td>

                                            <td>
                                                <div class="type-details">
                                                    @foreach($typeDetails as $label => $value)
                                                        <span class="detail-pill">
                                                            <span class="detail-label">{{ $label }}:</span> {{ $value }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>

                                            <td>{{ !empty($row['allocation_date']) ? \Carbon\Carbon::parse($row['allocation_date'])->format('d-M-Y') : '-' }}
                                            </td>
                                            <td>
                                                <span class="action-badge">
                                                    <span class="action-dot" style="background:{{ $iconInfo['color'] }};">
                                                        <i class="bi {{ $iconInfo['icon'] }}"></i>
                                                    </span>
                                                    {{ $row['action_label'] ?? '-' }}
                                                </span>
                                            </td>
                                            <td>{{ ucfirst($row['frequency'] ?? '-') }}</td>
                                            <td>{{ !empty($row['response_date']) ? \Carbon\Carbon::parse($row['response_date'])->format('d-M-Y') : '-' }}
                                            </td>
                                            <td class="truncate-cell" title="{{ $row['response'] ?? '' }}">
                                                {{ $row['response'] ?? '-' }}</td>
                                            <td class="truncate-cell" title="{{ $row['solution'] ?? '' }}">
                                                {{ $row['solution'] ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($isCleared)
                                                    <span class="badge bg-secondary"><i class="bi bi-eraser"></i> Cleared</span>
                                                    <small class="d-block text-muted">
                                                        was {{ ucfirst(strtolower((string) ($row['status'] ?? '-'))) }}
                                                        @if(!empty($row['cleared_at']))
                                                            &middot; {{ \Carbon\Carbon::parse($row['cleared_at'])->format('d-M-Y') }}
                                                        @endif
                                                    </small>
                                                @elseif($isPending)
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>
                                                @else
                                                    <span class="badge bg-success"><i class="bi bi-check2"></i> Responded</span>
                                                @endif
                                            </td>
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

  @include('owner.tally.components.footer')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.4.1/css/rowGroup.dataTables.min.css">
    <script src="https://cdn.datatables.net/rowgroup/1.4.1/js/dataTables.rowGroup.min.js"></script>


    <style>
        /* ============ Stat cards ============ */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
            height: 100%;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1;
            color: #212529;
        }

        .stat-label {
            font-size: .75rem;
            color: #6c757d;
            margin-top: 2px;
        }

        /* ============ Card / filter bar ============ */
        .history-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
        }

        .filter-card {
            background: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 14px 14px 8px;
        }

        .filter-card .form-label {
            font-weight: 500;
        }

        /* ============ Table ============ */
        #historyTable thead th {
            text-align: center;
            vertical-align: middle;
            background: #402c67;
            color: #fff;
            font-weight: 600;
            font-size: .82rem;
            white-space: nowrap;
        }

        #historyTable tbody td {
            font-size: .85rem;
            vertical-align: middle;
        }

        #historyTable tbody tr.row-pending {
            background-color: #fffbea;
        }

        .type-badge {
            background: #eef0ff;
            color: #402c67;
            font-weight: 600;
            font-size: .72rem;
            border: 1px solid #d9d4f0;
        }

        .action-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .82rem;
        }

        .action-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            flex-shrink: 0;
        }

        .truncate-cell {
            max-width: 160px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: help;
        }

        /* ============ Type-specific detail pills ============ */
        .type-details {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            max-width: 100%;
            margin-top: 4px;
        }

        .detail-pill {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: .75rem;
            white-space: nowrap;
            color: #333;
        }

        .detail-label {
            color: #6c757d;
            font-weight: 600;
        }

        table.dataTable tr.dtrg-group td {
            background-color: #402c67 !important;
            color: #fff !important;
            font-weight: 700;
        }

        .dataTables_wrapper .dataTables_paginate {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 4px;
        }

        #historyTable tbody tr.row-cleared {
            background-color: #f1f3f5;
            color: #6c757d;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let historyTable = null;

            function initDataTable() {
                if (!window.jQuery || !$.fn.DataTable || historyTable) return;

                historyTable = $('#historyTable').DataTable({
                    order: [[1, 'asc']],
                    columnDefs: [{ visible: false, targets: 1 }],
                    language: { emptyTable: 'No history found' },
                    rowGroup: {
                        dataSrc: 1,
                        startRender: function (rows, group) {
                            return group + ' (' + rows.count() + ' log' + (rows.count() > 1 ? 's' : '') + ')';
                        }
                    },
                    fixedHeader: false
                });

                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData, counter) {
                    if (settings.nTable.id !== 'historyTable') return true;
                    const row = settings.aoData[dataIndex].nTr;
                    const typeVal = document.getElementById('typeFilter').value;
                    const actionVal = document.getElementById('actionFilter').value;
                    const statusVal = document.getElementById('statusFilter').value;

                    if (typeVal && row.getAttribute('data-type') !== typeVal) return false;
                    if (actionVal && row.getAttribute('data-action') !== actionVal) return false;
                    if (statusVal && row.getAttribute('data-status') !== statusVal) return false;
                    return true;
                });
            }

            initDataTable();

            // ---- Filtering ----
            function applyFilters() {
                let visibleCount = 0;

                if (historyTable) {
                    historyTable.draw();
                    visibleCount = historyTable.rows({ search: 'applied' }).count();
                }

                document.getElementById('historyCountBadge').textContent = visibleCount;
            }

            ['typeFilter', 'actionFilter', 'statusFilter'].forEach(function (id) {
                document.getElementById(id).addEventListener('change', applyFilters);
            });

            document.getElementById('clearFiltersBtn').addEventListener('click', function () {
                document.getElementById('typeFilter').value = '';
                document.getElementById('actionFilter').value = '';
                document.getElementById('statusFilter').value = '';
                applyFilters();
            });

            applyFilters();
        });
    </script>
</div>
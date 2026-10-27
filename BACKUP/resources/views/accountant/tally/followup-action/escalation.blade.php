@include('accountant.components.header')
    <div id="main-wrapper">
        <div class="nav-header">
            <a href="#" class="brand-logo">
                <svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
                    <text x="55" y="32" font-size="22" font-family="Arial, sans-serif" font-weight="bold" fill="#4E3F6B">RMS</text>
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

@include('accountant.components.navbar')
@include('accountant.components.sidebar')

@php
    $type   = "";
    $allLedgers = [
        [
            'name' => 'Dummy Ledger 1', 'under' => 'Sundry Debtors', 'mobile' => '9876543210',
            'type' => 'Follow Up-Balances/%Targets',
            'balance' => 54321.10, 'target' => 50000,
            'status' => 'Pending', 'action' => 'call', 'frequency' => 'daily',
        ],
        [
            'name' => 'Dummy Ledger 2', 'under' => 'Sundry Debtors', 'mobile' => '9123456780',
            'type' => 'Follow Up-Balances/Targets(months)',
            'balance' => 72500.00, 'target' => 80000,
            'target_month' => now()->subMonths(1)->format('Y-m'),
            'status' => 'Responded', 'action' => 'whatsapp', 'frequency' => 'weekly',
            'allocation_date' => now()->subDays(10)->format('Y-m-d'),
            'response_date'   => now()->subDays(3)->format('Y-m-d'),
            'response' => 'promised_to_pay',
            'solution' => 'Payment committed by next week',
        ],
        [
            'name' => 'Dummy Ledger 3', 'under' => 'Sundry Debtors', 'mobile' => '9988776655',
            'type' => 'Follow Up-Balances/Days(agewise)',
            'balance' => 18200.50, 'age_bucket' => '31-60 Days', 'days' => 45,
            'status' => 'Pending', 'action' => 'escalation', 'frequency' => 'monthly',
        ],
        [
            'name' => 'Dummy Ledger 4', 'under' => 'Sundry Creditors', 'mobile' => '9871234567',
            'type' => 'Follow Up-Balances/Days(10-20-30)',
            'balance' => 9800.00, 'days' => 20,
            'date' => now()->subDays(20)->format('Y-m-d'),
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'inv_no' => 'INV-2204',
            'interest_due' => 150.00,
            'status' => 'Responded', 'action' => 'allocate_tele_call', 'frequency' => 'daily',
            'allocation_date' => now()->subDays(6)->format('Y-m-d'),
            'response_date'   => now()->subDays(1)->format('Y-m-d'),
            'response' => 'paid',
            'solution' => 'Full amount received via NEFT',
        ],
        [
            'name' => 'Dummy Ledger 5', 'under' => 'Sundry Creditors', 'mobile' => '9012345678',
            'type' => 'Follow Up-Due',
            'date' => now()->subDays(21)->format('Y-m-d'),
            'due_date' => now()->addDays(21)->format('Y-m-d'),
            'inv_no' => 'INV-2201', 'days' => 12,
            'due' => 32000.00, 'interest_due' => 480.00,
            'status' => 'Pending', 'action' => 'physical_visit', 'frequency' => 'weekly',
        ],
        [
            'name' => 'Dummy Ledger 6', 'under' => 'Sundry Debtors', 'mobile' => '9090909090',
            'type' => 'Follow Up-Not Due',
            'date' => now()->subDays(5)->format('Y-m-d'),
            'due_date' => now()->addDays(35)->format('Y-m-d'),
            'inv_no' => 'INV-2202', 'days_pending' => 5,
            'not_due' => 27500.00,
            'status' => 'Responded', 'action' => 'no_action', 'frequency' => 'monthly',
            'allocation_date' => now()->subDays(15)->format('Y-m-d'),
            'response_date'   => now()->subDays(9)->format('Y-m-d'),
            'response' => 'no_response',
            'solution' => 'Not reachable, will retry next cycle',
        ],
        [
            'name' => 'Dummy Ledger 7', 'under' => 'Sundry Creditors', 'mobile' => '9191919191',
            'type' => 'Follow Up Overlimits',
            'balance' => 12500.00,
            'status' => 'Pending', 'action' => 'escalation', 'frequency' => 'daily',
        ],
        [
            'name' => 'Dummy Ledger 8', 'under' => 'Sundry Debtors', 'mobile' => '9898989898',
            'type' => 'Follow Up-Balances/%Targets',
            'balance' => 61230.00, 'target' => 55000,
            'status' => 'Responded', 'action' => 'call', 'frequency' => 'weekly',
            'allocation_date' => now()->subDays(8)->format('Y-m-d'),
            'response_date'   => now()->subDays(2)->format('Y-m-d'),
            'response' => 'disputed',
            'solution' => 'Customer disputes 5k, verifying invoice',
        ],
        [
            'name' => 'Dummy Ledger 9', 'under' => 'Sundry Creditors', 'mobile' => '9797979797',
            'type' => 'Follow Up-Due',
            'date' => now()->subDays(10)->format('Y-m-d'),
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'inv_no' => 'INV-2203', 'days' => 18,
            'due' => 41500.00, 'interest_due' => 620.00,
            'status' => 'Pending', 'action' => 'call', 'frequency' => 'daily',
        ],
        [
            'name' => 'Dummy Ledger 10', 'under' => 'Sundry Debtors', 'mobile' => '9696969696',
            'type' => 'Follow Up-Balances/Days(agewise)',
            'balance' => 9450.75, 'age_bucket' => '0-30 Days', 'days' => 15,
            'status' => 'Responded', 'action' => 'whatsapp', 'frequency' => 'weekly',
            'allocation_date' => now()->subDays(12)->format('Y-m-d'),
            'response_date'   => now()->subDays(4)->format('Y-m-d'),
            'response' => 'paid',
            'solution' => 'Cleared full outstanding',
        ],
        [
            'name' => 'Dummy Ledger 11', 'under' => 'Sundry Debtors', 'mobile' => '9595959595',
            'type' => 'Follow Up-Balances',
            'balance' => 15750.25,
            'status' => 'Pending', 'action' => 'physical_visit', 'frequency' => 'monthly',
        ],
    ];

    if (!empty($ledger)) {
        $allLedgers = collect($allLedgers)
            ->filter(fn($l) => $l['name'] === $ledger)
            ->values()
            ->all();
    } elseif (!empty($under)) {
        $allLedgers = collect($allLedgers)
            ->filter(fn($l) => ($l['under'] ?? null) === $under)
            ->values()
            ->all();
    }

    $followUps = collect($allLedgers)->values()->map(function ($l, $index) {
        $balance   = $l['balance'] ?? 0;
        $target    = $l['target'] ?? 0;
        $targetPct = $target > 0 ? round((abs($balance) / $target) * 100, 2) : 0;

        return [
            'id'              => $index + 1,
            'name'            => $l['name'] ?? '-',
            'mobile'          => $l['mobile'] ?? '-',
            'under'           => $l['under'] ?? '-',
            'type'            => $l['type'] ?? '-',
            'balance'         => $balance,
            'target'          => $target,
            'target_pct'      => $targetPct,
            'target_month'    => $l['target_month'] ?? null,
            'age_bucket'      => $l['age_bucket'] ?? null,
            'days'            => $l['days'] ?? null,
            'days_pending'    => $l['days_pending'] ?? null,
            'date'            => $l['date'] ?? null,
            'due_date'        => $l['due_date'] ?? null,
            'inv_no'          => $l['inv_no'] ?? null,
            'due'             => $l['due'] ?? null,
            'not_due'         => $l['not_due'] ?? null,
            'interest_due'    => $l['interest_due'] ?? null,
            'status'          => $l['status'] ?? 'Pending',
            'action'          => $l['action'] ?? null,
            'frequency'       => $l['frequency'] ?? null,
            'allocation_date' => $l['allocation_date'] ?? null,
            'response_date'   => $l['response_date'] ?? null,
            'response'        => $l['response'] ?? null,
            'solution'        => $l['solution'] ?? null,
        ];
    });

 

@endphp
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
                                <div class="d-flex align-items-start">
                                    <div>
                                        <h4 class="card-title mb-0">Escalation - Assigned Ledgers</h4>
                                        <small class="text-muted">
                                            Ledgers allocated for escalation
                                        </small>

                                        <small class="text-muted">
                                            @if(!empty($selectedLedger))
                                                &nbsp;|&nbsp; Ledger: <span class="fw-bold">{{ $selectedLedger }}</span>
                                            @endif

                                            @if(!empty($selectedUnder))
                                                &nbsp;|&nbsp; Under: <span class="fw-bold">{{ $selectedUnder }}</span>
                                            @endif
                                        </small>

                                        <div class="small text-muted mt-1">
                                            <i class="fa fa-info-circle me-1"></i>
                                            Click on a <span class="fw-bold text-warning">Pending</span> row to submit your response. Click a <span class="fw-bold text-success">Responded</span> row to view its log.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        // Build a unique, sorted list of follow up types present in the data
                                        $followUpTypes = collect($followUps)
                                            ->map(function ($row) use ($type) {
                                                return $row['type'] ?? $type ?? '';
                                            })
                                            ->filter()
                                            ->unique()
                                            ->sort()
                                            ->values();
                                    @endphp

                                    <select id="typeFilter" class="form-select form-select-sm" style="width:220px;">
                                        <option value="">All Follow Up Types</option>
                                        @foreach($followUpTypes as $followUpType)
                                            <option value="{{ $followUpType }}">{{ $followUpType }}</option>
                                        @endforeach
                                    </select>

                                    <select id="statusFilter" class="form-select form-select-sm" style="width:160px;">
                                        <option value="">All Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Responded">Responded</option>
                                    </select>

                                    <span class="badge bg-primary" id="totalLedgerBadge">
                                        {{ count($followUps) }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body">
                                <input type="hidden" value="{{ $company }}">

                                <div class="table-responsive">
                                    <table id="example12" class="table table-bordered table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Sr.No</th>
                                                <th style="width: 150px;">Name</th>
                                                <th>Mobile</th>
                                                <th style="width: 250px;">Follow Up Type</th>
                                                <th>Type Details</th>
                                                <th>Allocation Date</th>
                                                <th>Response Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($followUps as $row)
                                                @php
                                                    $rowType = $row['type'] ?? $type ?? '';
                                                    $chips   = [];

                                                    if ($rowType === 'Follow Up-Balances/%Targets') {
                                                        $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];
                                                        $chips[] = ['label' => 'Target', 'value' => number_format($row['target'], 2)];
                                                        $chips[] = ['label' => '%Target', 'value' => $row['target_pct'] . '%'];

                                                    } elseif ($rowType === 'Follow Up-Balances') {
                                                        $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];

                                                    } elseif ($rowType === 'Follow Up-Balances/Targets(months)') {
                                                        $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];
                                                        $chips[] = ['label' => 'Target', 'value' => number_format($row['target'], 2)];
                                                        $chips[] = ['label' => '%Target', 'value' => $row['target_pct'] . '%'];
                                                        $chips[] = ['label' => 'Target Month', 'value' => !empty($row['target_month']) ? \Carbon\Carbon::createFromFormat('Y-m', $row['target_month'])->format('M Y') : '-'];

                                                    } elseif ($rowType === 'Follow Up-Balances/Days(agewise)') {
                                                        $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];
                                                        $chips[] = ['label' => 'Days', 'value' => $row['days'] ?? '-'];

                                                    } elseif ($rowType === 'Follow Up-Balances/Days(10-20-30)') {
                                                        $chips[] = ['label' => 'Date', 'value' => !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d-M-Y') : '-'];
                                                        $chips[] = ['label' => 'Due Date', 'value' => !empty($row['due_date']) ? \Carbon\Carbon::parse($row['due_date'])->format('d-M-Y') : '-'];
                                                        $chips[] = ['label' => 'INV No', 'value' => $row['inv_no'] ?? '-'];
                                                        $chips[] = ['label' => 'Days', 'value' => $row['days'] ?? '-'];
                                                        $chips[] = ['label' => 'Amount', 'value' => number_format(abs($row['balance']), 2)];
                                                        $chips[] = ['label' => 'Interest Due', 'value' => number_format($row['interest_due'] ?? 0, 2)];

                                                    } elseif ($rowType === 'Follow Up-Due') {
                                                        $chips[] = ['label' => 'Date', 'value' => !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d-M-Y') : '-'];
                                                        $chips[] = ['label' => 'Due Date', 'value' => !empty($row['due_date']) ? \Carbon\Carbon::parse($row['due_date'])->format('d-M-Y') : '-'];
                                                        $chips[] = ['label' => 'INV No', 'value' => $row['inv_no'] ?? '-'];
                                                        $chips[] = ['label' => 'Days', 'value' => $row['days'] ?? '-'];
                                                        $chips[] = ['label' => 'Amount', 'value' => number_format($row['due'] ?? 0, 2)];
                                                        $chips[] = ['label' => 'Interest Due', 'value' => number_format($row['interest_due'] ?? 0, 2)];

                                                    } elseif ($rowType === 'Follow Up-Not Due') {
                                                        $chips[] = ['label' => 'Date', 'value' => !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d-M-Y') : '-'];
                                                        $chips[] = ['label' => 'Due Date', 'value' => !empty($row['due_date']) ? \Carbon\Carbon::parse($row['due_date'])->format('d-M-Y') : '-'];
                                                        $chips[] = ['label' => 'INV No', 'value' => $row['inv_no'] ?? '-'];
                                                        $chips[] = ['label' => 'Days Pending', 'value' => $row['days_pending'] ?? '-'];
                                                        $chips[] = ['label' => 'Amount', 'value' => number_format($row['not_due'] ?? 0, 2)];

                                                    } elseif ($rowType === 'Follow Up Overlimits') {
                                                        $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance']), 2)];

                                                    } else {
                                                        $chips[] = ['label' => 'Balance', 'value' => number_format(abs($row['balance'] ?? 0), 2)];
                                                    }

                                                    $status = $row['status'] ?? 'Pending';
                                                @endphp
                                                <tr
                                                    class="ledger-row"
                                                    style="cursor:pointer;"
                                                    data-id="{{ $row['id'] }}"
                                                    data-name="{{ $row['name'] }}"
                                                    data-mobile="{{ $row['mobile'] }}"
                                                    data-balance="{{ $row['balance'] ?? 0 }}"
                                                    data-target="{{ $row['target'] ?? 0 }}"
                                                    data-followup-type="{{ $rowType }}"
                                                    data-action="{{ $row['action'] ?? '-' }}"
                                                    data-frequency="{{ $row['frequency'] ?? '-' }}"
                                                    data-allocation-date="{{ $row['allocation_date'] ?? '-' }}"
                                                    data-response-date="{{ $row['response_date'] ?? '-' }}"
                                                    data-status="{{ $status }}">
                                                    <td>{{ $row['id'] }}</td>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td>{{ $row['mobile'] }}</td>

                                                    <td class="followup-type-cell">
                                                        <span class="badge type-pill">{{ $rowType }}</span>
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
                                                        {{ !empty($row['allocation_date']) ? \Carbon\Carbon::parse($row['allocation_date'])->format('d-M-Y') : '-' }}
                                                    </td>
                                                    <td>
                                                        @if($status === 'Responded' && !empty($row['response_date']))
                                                            {{ \Carbon\Carbon::parse($row['response_date'])->format('d-M-Y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <span class="badge accountant-status-badge {{ $status === 'Pending' ? 'bg-warning' : 'bg-success' }}">
                                                            {{ $status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No Records Found</td>
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

        {{-- ================= ACCOUNTANT RESPONSE MODAL ================= --}}
        <div class="modal fade" id="followupResponseModal" tabindex="-1" aria-labelledby="followupResponseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="followupResponseModalLabel">Submit Follow Up Response</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Name:</strong> <span id="respModalName">-</span></div>
                            <div class="col-md-6"><strong>Mobile:</strong> <span id="respModalMobile">-</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4"><strong>Follow Up Type:</strong> <span id="respModalType">-</span></div>
                            <div class="col-md-4"><strong>Action:</strong> <span id="respModalAction">-</span></div>
                            <div class="col-md-4"><strong>Frequency:</strong> <span id="respModalFrequency">-</span></div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Response</label>
                                <select id="respModalResponse" class="form-select form-select-sm">
                                    <option value="" selected>Select response</option>
                                    <option value="paid_cleared">PAID/CLEARED</option>
                                    <option value="send_ledger">SEND LEDGER</option>
                                    <option value="wrong_no">Wrong NO</option>
                                    <option value="sir_involvement">SIR INVOLVEMENT</option>
                                    <option value="will_pay_shortly">Will pay shortly</option>
                                    <option value="will_come_to_office">Will come to office</option>
                                    <option value="dispute">Dispute</option>
                                    <option value="send_invoices">Send invoices</option>
                                    <option value="no_response_avoiding">No response/Avoiding calls</option>
                                    <option value="send_someone_to_collect">Send someone to collect</option>
                                    <option value="accountant_se_bat_karta_hu">Accountant se bat karta hu</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Solution</label>
                                <select id="respModalSolution" class="form-select form-select-sm">
                                    <option value="" selected>Select solution</option>
                                    <option value="ok">OK</option>
                                    <option value="pending">PENDING</option>
                                    <option value="ledger_sent">LEDGER SENT</option>
                                    <option value="mobile_no_ok">MOBILE NO.OK</option>
                                    <option value="dispute_ok">DISPUTE OK</option>
                                    <option value="invoice_sent">INVOICE SENT</option>
                                    <option value="accountant_call_ok">ACCOUNTANT CALL OK</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Clear Solution (ADMIN)</label>
                            <select id="respModalAdminSolution" class="form-select form-select-sm">
                                <option value="" selected>Select status</option>
                                <option value="clear">CLEAR</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="respModalSubmitBtn" class="btn btn-success">Submit Response</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ================= END ACCOUNTANT RESPONSE MODAL ================= --}}

        {{-- ================= VIEW LOG MODAL ================= --}}
        <div class="modal fade" id="followupLogModal" tabindex="-1" aria-labelledby="followupLogModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="followupLogModalLabel">Follow Up Log</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Name:</strong> <span id="logModalName">-</span></div>
                            <div class="col-md-6"><strong>Mobile:</strong> <span id="logModalMobile">-</span></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle" id="followupLogTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr.No</th>
                                        <th>Balance</th>
                                        <th>Target</th>
                                        <th>% Target</th>
                                        <th>Allocation date</th>
                                        <th>Response date</th>
                                        <th>Action</th>
                                        <th>Frequency</th>
                                        <th>Response</th>
                                        <th>Solution</th>
                                    </tr>
                                </thead>
                                <tbody id="followupLogTableBody"></tbody>
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
            .dataTables_wrapper .dataTables_paginate { display: flex; flex-wrap: nowrap; align-items: center; gap: 4px; }
            .dataTables_wrapper .dataTables_paginate .paginate_button,
            .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
            .dataTables_wrapper .dataTables_paginate .paginate_button.next,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                white-space: nowrap !important; padding: 0.375rem 0.75rem !important; min-width: auto !important;
                width: auto !important; height: auto !important; display: inline-flex !important;
                align-items: center; justify-content: center; line-height: normal !important;
            }
            #example12 tbody tr.ledger-row:hover { background-color: rgba(64, 44, 103, 0.08); }

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
        </style>

        @include('accountant.components.footer')

       <script>
    document.addEventListener('DOMContentLoaded', function () {

        let followupTable = null;

        function updateTotalLedgerBadge(count) {
            const badge = document.getElementById('totalLedgerBadge');
            if (badge) badge.textContent = count;
        }

        if (window.jQuery && $.fn.DataTable) {
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData, counter) {
                if (settings.nTable.id !== 'example12') return true;

                const rowNode = settings.aoData[dataIndex].nTr;

                const statusFilterEl = document.getElementById('statusFilter');
                if (statusFilterEl && statusFilterEl.value) {
                    if (rowNode.getAttribute('data-status') !== statusFilterEl.value) return false;
                }

                const typeFilterEl = document.getElementById('typeFilter');
                if (typeFilterEl && typeFilterEl.value) {
                    if (rowNode.getAttribute('data-followup-type') !== typeFilterEl.value) return false;
                }

                return true;
            });

            followupTable = $('#example12').DataTable({ fixedHeader: false });

            // Har draw (filter/search/page change) ke baad badge ko filtered count se update karo
            followupTable.on('draw', function () {
                const info = followupTable.page.info();
                updateTotalLedgerBadge(info.recordsDisplay);
            });

            // Initial load pe bhi ek baar sahi count dikhado
            updateTotalLedgerBadge(followupTable.page.info().recordsDisplay);
        }

        const statusFilterEl = document.getElementById('statusFilter');
        if (statusFilterEl && followupTable) {
            statusFilterEl.addEventListener('change', function () { followupTable.draw(); });
        }

        const typeFilterEl = document.getElementById('typeFilter');
        if (typeFilterEl && followupTable) {
            typeFilterEl.addEventListener('change', function () { followupTable.draw(); });
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

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function renderLogTable(logs) {
            const tbody = document.getElementById('followupLogTableBody');
            tbody.innerHTML = '';

            if (!logs || logs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center">No log entries found</td></tr>`;
                return;
            }

            logs.forEach(function (log, index) {
                const balance = Number(log.balance || 0);
                const target = Number(log.target || 0);
                const pct = target > 0 ? ((Math.abs(balance) / target) * 100).toFixed(2) : '0.00';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td class="text-end">${balance.toLocaleString()}</td>
                    <td class="text-end">${target.toLocaleString()}</td>
                    <td class="text-end">${pct}%</td>
                    <td>${formatDate(log.allocation_date)}</td>
                    <td>${formatDate(log.response_date)}</td>
                    <td>${log.action ?? '-'}</td>
                    <td>${log.frequency ?? '-'}</td>
                    <td>${log.response ?? '-'}</td>
                    <td>${log.solution ?? '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        // ================= RESPONSE MODAL SETUP =================
        const responseModalEl = document.getElementById('followupResponseModal');
        const responseModal = responseModalEl ? new bootstrap.Modal(responseModalEl) : null;

        let respLedgerId = null;
        let respLedgerRow = null;

        function openLogForRow(row) {
            const rowData = {
                name: row.getAttribute('data-name'),
                mobile: row.getAttribute('data-mobile'),
                balance: row.getAttribute('data-balance'),
                target: row.getAttribute('data-target'),
                action: row.getAttribute('data-action'),
                frequency: row.getAttribute('data-frequency'),
            };

            document.getElementById('logModalName').textContent = rowData.name ?? '-';
            document.getElementById('logModalMobile').textContent = rowData.mobile ?? '-';

            // ideally fetch actual history from server; using row data as fallback
            renderLogTable([rowData]);

            new bootstrap.Modal(document.getElementById('followupLogModal')).show();
        }

        // ================= ROW CLICK -> STATUS KE HISAAB SE MODAL =================
        document.addEventListener('click', function (e) {
            const row = e.target.closest('#example12 tbody tr.ledger-row');
            if (!row) return;

            const status = row.getAttribute('data-status');

            if (status === 'Responded') {
                openLogForRow(row);
                return;
            }

            respLedgerId = row.getAttribute('data-id');
            respLedgerRow = row;

            document.getElementById('respModalName').textContent = row.getAttribute('data-name') || '-';
            document.getElementById('respModalMobile').textContent = row.getAttribute('data-mobile') || '-';
            document.getElementById('respModalType').textContent = row.getAttribute('data-followup-type') || '-';
            document.getElementById('respModalAction').textContent = row.getAttribute('data-action') || '-';
            document.getElementById('respModalFrequency').textContent = row.getAttribute('data-frequency') || '-';

            document.getElementById('respModalResponse').value = '';
            document.getElementById('respModalSolution').value = '';
            document.getElementById('respModalAdminSolution').value = '';

            if (responseModal) responseModal.show();
        });

        // ================= SUBMIT RESPONSE =================
        const respModalSubmitBtn = document.getElementById('respModalSubmitBtn');
        if (respModalSubmitBtn) {
            respModalSubmitBtn.addEventListener('click', function () {
                const responseValue      = document.getElementById('respModalResponse').value;
                const solutionValue      = document.getElementById('respModalSolution').value;
                const adminSolutionValue = document.getElementById('respModalAdminSolution').value;

                if (!respLedgerId) {
                    showAjaxAlert('Ledger select nahi hui.', 'danger');
                    return;
                }
                if (!responseValue) {
                    showAjaxAlert('Response select karo.', 'danger');
                    return;
                }
                if (!solutionValue) {
                    showAjaxAlert('Solution select karo.', 'danger');
                    return;
                }
                if (!adminSolutionValue) {
                    showAjaxAlert('Clear Solution (ADMIN) select karo.', 'danger');
                    return;
                }

                const originalText = this.textContent;
                this.disabled = true;
                this.textContent = 'Submitting...';
                const self = this;

                fetch("{{ url('accountant/followup/submit-response') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        id: respLedgerId,
                        response: responseValue,
                        solution: solutionValue,
                        admin_solution: adminSolutionValue
                    })
                })
                .then(function (r) {
                    if (!r.ok) throw new Error('Request failed');
                    return r.json().catch(() => ({}));
                })
                .then(function (data) {
                    showAjaxAlert(data.message ?? 'Response submit ho gaya!', 'success');

                    if (respLedgerRow) {
                        respLedgerRow.setAttribute('data-status', 'Responded');
                        const badge = respLedgerRow.querySelector('.accountant-status-badge');
                        if (badge) {
                            badge.textContent = 'Responded';
                            badge.classList.remove('bg-warning');
                            badge.classList.add('bg-success');
                        }
                    }

                    responseModal.hide();
                    respLedgerId = null;
                    respLedgerRow = null;
                })
                .catch(function () {
                    showAjaxAlert('Response submit karte waqt error aaya. Dobara try karo.', 'danger');
                })
                .finally(function () {
                    self.disabled = false;
                    self.textContent = originalText;
                });
            });
        }

    });
</script>
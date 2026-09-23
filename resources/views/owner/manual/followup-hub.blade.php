@include('owner.components.header')
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

@include('owner.components.navbar')
@include('owner.components.sidebar')

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

                            {{-- ================= QUICK / SMART FOLLOW-UP TABS (UNCHANGED) ================= --}}
                            @if($type === 'Follow Up-Quick /Smart - Follow -up')
                                @php
                                    $followupTabs = [
                                        'balances'         => 'Follow Up-Balances',
                                        'pct_targets'      => 'Follow Up-Balances/%Targets',
                                        'targets_months'   => 'Follow Up-Balances/Targets(months)',
                                        'days_agewise'     => 'Follow Up-Balances/Days(agewise)',
                                        'days_10_20_30'    => 'Follow Up-Balances/Days(10-20-30)',
                                        'due'              => 'Follow Up-Due',
                                        'not_due'          => 'Follow Up-Not Due',
                                        'overlimits'       => 'Follow Up Overlimits',
                                    ];
                                    $activeTabKey = collect($followupTabs)->search($type) ?: array_key_first($followupTabs);
                                @endphp

                                <ul class="nav nav-pills followup-tabs px-3 pt-3 gap-2 flex-nowrap overflow-auto pb-3" id="followupTabsNav" role="tablist">
                                    @foreach($followupTabs as $key => $label)
                                        <li class="nav-item" role="presentation">
                                            <button
                                                class="nav-link {{ $key === $activeTabKey ? 'active' : '' }}"
                                                id="tab-{{ $key }}"
                                                data-bs-toggle="tab"
                                                data-bs-target="#pane-{{ $key }}"
                                                type="button"
                                                role="tab"
                                                aria-controls="pane-{{ $key }}"
                                                aria-selected="{{ $key === $activeTabKey ? 'true' : 'false' }}">
                                                {{ $label }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <style>
                                .followup-tabs .nav-link { border-radius: 999px; padding: 15px 1rem; font-size: 0.85rem; font-weight: 500; color: #495057; background: #f1f3f5; white-space: nowrap; transition: all 0.15s ease-in-out; }
                                .followup-tabs .nav-link:hover { background: #e2e6ea; }
                                .followup-tabs .nav-link.active { background: #402c67; color: #fff; box-shadow: 0 2px 6px rgba(13,110,253,.35); }
                                .followup-tabs::-webkit-scrollbar { height: 4px; }
                                </style>
                            @endif

                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                @if($type != 'Follow Up-Quick /Smart - Follow -up')
                                    <div class="d-flex align-items-start">
                                        <div>
                                            <h4 class="card-title mb-0">Follow Up List</h4>

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
                                                Click on any ledger row to allocate its Follow Up.
                                            </div>
                                        </div>

                                       
                                    </div>

                                     <!-- Right End -->
                                    <button type="button"
                                            class="btn btn-outline-warning btn-sm ms-auto"
                                            id="clearPendingBtn">
                                        <i class="fa fa-refresh me-1"></i> Clear Pending
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-success btn-sm"
                                            id="clearRespondedBtn">
                                        <i class="fa fa-refresh me-1"></i> Clear Responded
                                    </button>
                                    <div class="d-flex align-items-center gap-2">

                                        @if($type === 'Follow Up-Balances/Targets(months)')
                                            @php
                                                $monthOptions = [];
                                                for ($i = 0; $i < 6; $i++) {
                                                    $monthOptions[] = \Carbon\Carbon::now()->subMonths($i)->format('M Y');
                                                }
                                            @endphp
                                            <select id="monthFilter" class="form-select form-select-sm" style="width:160px;">
                                                <option value="">All Months</option>
                                                @foreach($monthOptions as $m)
                                                    <option value="{{ $m }}">{{ $m }}</option>
                                                @endforeach
                                            </select>
                                        @endif

                                        @if($type === 'Follow Up-Balances/Days(10-20-30)')
                                            <select id="daysFilter" class="form-select form-select-sm" style="width:160px;">
                                                <option value="">All Days</option>
                                                <option value="10">10 Days</option>
                                                <option value="20">20 Days</option>
                                                <option value="30">30 Days</option>
                                            </select>
                                        @endif

                                        @if($type === 'Follow Up-Balances/Days(agewise)')
                                            <select id="agewiseDaysFilter" class="form-select form-select-sm" style="width:180px;">
                                                <option value="">All Days</option>
                                                <option value="15">More than 15 Days</option>
                                                <option value="45">More than 45 Days</option>
                                                <option value="90">More than 90 Days</option>
                                            </select>
                                        @endif

                                        <span class="badge bg-primary" id="totalLedgerBadge">
                                            {{ count($followUps) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="card-body">
                                <input type="hidden" value="{{ $company }}">
                                <input type="hidden" id="hiddenType" value="{{ $type }}">

                                {{-- ================= SIMPLE UNIFIED LEDGER LISTING (for all non-Quick/Smart types) ================= --}}
                                @if($type !== 'Follow Up-Quick /Smart - Follow -up')
                                    <div class="table-responsive">
                                        <table id="example12" class="table table-bordered table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Sr.No</th>
                                                    <th style="width: 150px;">Name</th>
                                                    <th>Mobile</th>
                                                    <th style="width: 250px;">Follow Up Type</th>
                                                    <th>Type Details</th>
                                                    <th>Accountant Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($followUps as $row)
                                                    @php
                                                        $rowMonth = !empty($row['due_date']) ? \Carbon\Carbon::parse($row['due_date'])->format('Y-m') : '';
                                                        $rowType  = $row['type'] ?? $type;
                                                        $chips    = [];

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
                                                    @endphp
                                                    <tr
                                                        class="ledger-row"
                                                        style="cursor:pointer;"
                                                        data-id="{{ $row['id'] }}"
                                                        data-days="{{ $row['days'] ?? '' }}"
                                                        data-month="{{ $rowMonth }}"
                                                        data-name="{{ $row['name'] }}"
                                                        data-mobile="{{ $row['mobile'] }}"
                                                        data-balance="{{ $row['balance'] ?? 0 }}"
                                                        data-target="{{ $row['target'] ?? 0 }}"
                                                        data-followup-type="{{ $rowType }}">
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
                                                            @php
                                                                $accountantStatus = rand(0, 1) ? 'Pending' : 'Responded';
                                                            @endphp

                                                            <span class="badge accountant-status-badge {{ $accountantStatus === 'Pending' ? 'bg-warning' : 'bg-success' }}">
                                                                {{ $accountantStatus }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">No Records Found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                {{-- ================= QUICK / SMART TAB CONTENT (UNCHANGED) ================= --}}
                                @if($type === 'Follow Up-Quick /Smart - Follow -up')
                                    <div class="tab-content" id="followupTabsContent">
                                        @foreach($followupTabs as $key => $label)
                                            <div
                                                class="tab-pane fade {{ $key === $activeTabKey ? 'show active' : '' }}"
                                                id="pane-{{ $key }}"
                                                role="tabpanel"
                                                aria-labelledby="tab-{{ $key }}">

                                                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                    <div>
                                                        <h4 class="card-title mb-0">{{ $label }}</h4>

                                                        <div class="small text-muted mt-1 accountant-status-legend">
                                                            <i class="fa fa-info-circle me-1"></i>
                                                            <span class="badge bg-primary">Already Assigned</span> — Follow up already given to accountant
                                                            &nbsp;|&nbsp;
                                                            <span class="badge bg-warning">Pending</span> — Waiting for accountant's response
                                                            &nbsp;|&nbsp;
                                                            <span class="badge bg-success">Responded</span> — Accountant has responded
                                                            &nbsp;|&nbsp;
                                                            <span class="badge bg-secondary">Not Assign Yet</span> — No follow up allocated yet

                                                            <div class="mt-1">
                                                                <i class="fa fa-exclamation-circle me-1"></i>
                                                                Note: Bulk assignment only applies to ledgers with status <strong>"Not Assign Yet"</strong>.
                                                                Ledgers with any other status must first have their status cleared before they can be bulk assigned again.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card-body">
                                                    <div class="d-flex align-items-center gap-2 mb-3 bulk-action-bar" data-pane="pane-{{ $key }}">
                                                        <span class="small text-muted">
                                                            Applies to <span class="visible-count">0</span> ledgers currently shown
                                                        </span>

                                                        <select class="form-select form-select-sm bulk-action-select" style="width:200px;">
                                                            <option value="" selected>Select action</option>
                                                            <option value="allocate_tele_call">Allocate to tele call</option>
                                                            <option value="call">Call</option>
                                                            <option value="whatsapp">WhatsApp</option>
                                                            <option value="physical_visit">Physical visit</option>
                                                            <option value="escalation">Escalation</option>
                                                            <option value="no_action">No action</option>
                                                        </select>

                                                        {{-- WhatsApp Template dropdown for bulk actions, hidden by default --}}
                                                        <select class="form-select form-select-sm bulk-template-select" style="width:180px; display:none;">
                                                            <option value="" selected>Select template</option>
                                                            <option value="template_1">Template 1</option>
                                                            <option value="template_2">Template 2</option>
                                                            <option value="template_3">Template 3</option>
                                                            <option value="template_4">Template 4</option>
                                                            <option value="template_5">Template 5</option>
                                                        </select>

                                                        <div class="d-flex align-items-center gap-2">
                                                            <label class="mb-0 small text-muted" for="pctTargetFilter-{{ $key }}">Show:</label>

                                                            @if($key === 'targets_months')
                                                                <select id="pctTargetFilter-{{ $key }}" class="form-select form-select-sm pct-target-filter" style="width:160px;" data-pane="pane-{{ $key }}" data-filter-type="month">
                                                                    @foreach(range(0, 5) as $m)
                                                                        @php
                                                                            $monthValue = now()->subMonths($m)->format('Y-m');
                                                                            $monthLabel = now()->subMonths($m)->format('M Y');
                                                                        @endphp
                                                                        <option value="{{ $monthValue }}" {{ $m == 0 ? 'selected' : '' }}>{{ $monthLabel }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @elseif($key === 'days_agewise')
                                                                <select id="pctTargetFilter-{{ $key }}" class="form-select form-select-sm pct-target-filter" style="width:170px;" data-pane="pane-{{ $key }}" data-filter-type="agewise">
                                                                    <option value="total" selected>Total</option>
                                                                    <option value="15">More than 15 days</option>
                                                                    <option value="45">More than 45 days</option>
                                                                    <option value="90">More than 90 days</option>
                                                                </select>
                                                            @elseif($key === 'days_10_20_30')
                                                                <select id="pctTargetFilter-{{ $key }}" class="form-select form-select-sm pct-target-filter" style="width:170px;" data-pane="pane-{{ $key }}" data-filter-type="days3">
                                                                    <option value="all" selected>All Days</option>
                                                                    <option value="10">More than 10 days</option>
                                                                    <option value="20">More than 20 days</option>
                                                                    <option value="30">More than 30 days</option>
                                                                </select>
                                                            @else
                                                                <select id="pctTargetFilter-{{ $key }}" class="form-select form-select-sm pct-target-filter" style="width:160px;" data-pane="pane-{{ $key }}" data-filter-type="pct">
                                                                    <option value="top_25">Top -25 %</option>
                                                                    <option value="top_50">Top -50 %</option>
                                                                    <option value="top_75">Top -75 %</option>
                                                                    <option value="all" selected>All</option>
                                                                    <option value="bottom_75">Bottom -75 %</option>
                                                                    <option value="bottom_50">Bottom -50 %</option>
                                                                    <option value="bottom_25">Bottom -25 %</option>
                                                                </select>
                                                            @endif
                                                        </div>

                                                        <button type="button" class="btn btn-success btn-sm bulk-apply-btn">Submit</button>
                                                    </div>

                                                    <div class="table-responsive">
                                                        @php
                                                            $dummyStudentNames = [
                                                                'Rahul Sharma', 'Priya Verma', 'Aman Gupta', 'Sneha Patel', 'Vikram Singh',
                                                                'Anjali Mehta', 'Karan Malhotra', 'Neha Joshi', 'Rohan Desai', 'Ishita Rao',
                                                            ];

                                                            $dummyRows = collect(range(1, 10))->map(function ($dummyIndex) use ($key, $dummyStudentNames) {
                                                                $dummyTarget  = 50000;
                                                                $dummyBalance = $dummyIndex * 5432.10;
                                                                $dummyPct     = round(($dummyBalance / $dummyTarget) * 100, 2);
                                                                $dummyDays    = $dummyIndex * 9;
                                                                $dummyMonth   = now()->subMonths($dummyIndex % 6)->format('Y-m');

                                                                $accountantStatuses = ['Already Assigned', 'Pending', 'Responded','Not assign Yet'];
                                                                $accountantStatus   = $accountantStatuses[array_rand($accountantStatuses)];

                                                                return [
                                                                    'id'                => $key . '-' . $dummyIndex,
                                                                    'sr'                => $dummyIndex,
                                                                    'name'              => $dummyStudentNames[$dummyIndex - 1] ?? ('Student ' . $dummyIndex),
                                                                    'mobile'            => '90000000' . $dummyIndex,
                                                                    'balance'           => $dummyBalance,
                                                                    'target'            => $dummyTarget,
                                                                    'pct'               => $dummyPct,
                                                                    'days'              => $dummyDays,
                                                                    'month'             => $dummyMonth,
                                                                    'accountant_status' => $accountantStatus,
                                                                ];
                                                            })->sortBy('pct')->values();
                                                        @endphp
                                                        <table id="example-{{ $key }}" class="table table-bordered table-striped align-middle followup-target-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr.No</th>
                                                                    <th>Name</th>
                                                                    <th>Mobile</th>
                                                                    <th>Balance</th>
                                                                    <th>Target</th>
                                                                    @if($key === 'targets_months')
                                                                        <th>Month</th>
                                                                    @elseif($key === 'days_agewise' || $key === 'days_10_20_30')
                                                                        <th>Days Overdue</th>
                                                                    @else
                                                                        <th>% Target</th>
                                                                    @endif
                                                                    <th>Accountant Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($dummyRows as $row)
                                                                    <tr
                                                                        data-target-pct="{{ $row['pct'] }}"
                                                                        data-days="{{ $row['days'] }}"
                                                                        data-month="{{ $row['month'] }}"
                                                                        data-id="{{ $row['id'] }}">
                                                                        <td>{{ $row['sr'] }}</td>
                                                                        <td>{{ $row['name'] }}</td>
                                                                        <td>{{ $row['mobile'] }}</td>
                                                                        <td>{{ number_format($row['balance'], 2) }}</td>
                                                                        <td>{{ number_format($row['target'], 2) }}</td>
                                                                        @if($key === 'targets_months')
                                                                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row['month'])->format('M Y') }}</td>
                                                                        @elseif($key === 'days_agewise' || $key === 'days_10_20_30')
                                                                            <td>{{ $row['days'] }}</td>
                                                                        @else
                                                                            <td>{{ $row['pct'] }}%</td>
                                                                        @endif
                                                                        <td>
                                                                           @php
                                                                                $badgeClass = match($row['accountant_status']) {
                                                                                    'Already Assigned' => 'bg-primary',
                                                                                    'Pending'           => 'bg-warning',
                                                                                    'Responded'         => 'bg-success',
                                                                                    'Not Assign Yet'    => 'bg-secondary',
                                                                                    default             => 'bg-secondary',
                                                                                };
                                                                            @endphp
                                                                             
                                                                            <span class="badge accountant-status-badge {{ $badgeClass }}">
                                                                                {{ $row['accountant_status'] }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <script>
                                    document.querySelectorAll('.pct-target-filter').forEach(function (selectEl) {
                                        const paneId = selectEl.getAttribute('data-pane');
                                        const filterType = selectEl.getAttribute('data-filter-type');
                                        const pane = document.getElementById(paneId);
                                        if (!pane) return;

                                        const tbody = pane.querySelector('tbody');
                                        if (!tbody) return;

                                        const bulkBar = pane.querySelector('.bulk-action-bar');
                                        const visibleCountEl = bulkBar ? bulkBar.querySelector('.visible-count') : null;
                                        const applyBtn = bulkBar ? bulkBar.querySelector('.bulk-apply-btn') : null;

                                        function applyFilter() {
                                            const selectedVal = selectEl.value;
                                            const rows = Array.from(tbody.querySelectorAll('tr'));
                                            let visibleCount = 0;

                                            if (filterType === 'pct') {
                                                const totalCount = rows.length;

                                                if (selectedVal === 'all') {
                                                    rows.forEach(function (row) { row.style.display = ''; });
                                                    visibleCount = totalCount;
                                                    if (applyBtn) applyBtn.textContent = 'Apply to All';
                                                } else {
                                                    const [direction, pctStr] = selectedVal.split('_');
                                                    const pct = parseInt(pctStr, 10);
                                                    const cutoff = Math.ceil((pct / 100) * totalCount);

                                                    rows.forEach(function (row, index) {
                                                        let show;
                                                        if (direction === 'top') {
                                                            show = index < cutoff;
                                                        } else {
                                                            show = index >= (totalCount - cutoff);
                                                        }
                                                        row.style.display = show ? '' : 'none';
                                                        if (show) visibleCount++;
                                                    });

                                                    const label = direction === 'top' ? 'Top' : 'Bottom';
                                                    if (applyBtn) applyBtn.textContent = 'Apply to ' + label + ' ' + pct + '%';
                                                }
                                            } else if (filterType === 'month') {
                                                rows.forEach(function (row) {
                                                    const show = row.getAttribute('data-month') === selectedVal;
                                                    row.style.display = show ? '' : 'none';
                                                    if (show) visibleCount++;
                                                });
                                                if (applyBtn) applyBtn.textContent = 'Apply to Shown';
                                            } else if (filterType === 'agewise' || filterType === 'days3') {
                                                rows.forEach(function (row) {
                                                    const days = parseInt(row.getAttribute('data-days'), 10) || 0;
                                                    const show = (selectedVal === 'total') ? true : (days > parseInt(selectedVal, 10));
                                                    row.style.display = show ? '' : 'none';
                                                    if (show) visibleCount++;
                                                });
                                                if (applyBtn) applyBtn.textContent = 'Apply to Shown';
                                            }

                                            if (visibleCountEl) visibleCountEl.textContent = visibleCount;
                                        }

                                        selectEl.addEventListener('change', applyFilter);
                                        applyFilter();
                                    });
                                    </script>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= FOLLOW UP ALLOCATE MODAL (type -> action -> frequency -> assign) ================= --}}
        <div class="modal fade" id="followupAllocateModal" tabindex="-1" aria-labelledby="followupAllocateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="followupAllocateModalLabel">Follow Up Allocate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <p class="small text-muted mb-2">
                            Ledger: <span id="allocateSelectedName" class="fw-bold">-</span>
                        </p>

                        {{-- Step 1: choose follow up type --}}
                        <div id="allocateStep1">
                            <label class="form-label fw-bold">Select Follow Up Type</label>
                            <div class="d-flex flex-column gap-2">
                                @php
                                    $allocateTypes = [
                                        'Follow Up-Balances/%Targets'      => 'Balances / %Targets',
                                        'Follow Up-Balances/Targets(months)'=> 'Balances / Targets (months)',
                                        'Follow Up-Balances/Days(agewise)' => 'Balances / Days (agewise)',
                                        'Follow Up-Balances/Days(10-20-30)'=> 'Balances / Days (10-20-30)',
                                        'Follow Up-Due'                    => 'Due',
                                        'Follow Up-Not Due'                => 'Not Due',
                                        'Follow Up-Balances'                => 'Balances', 
                                    ];
                                @endphp
                                @foreach($allocateTypes as $value => $label)
                                    <div class="form-check">
                                        <input class="form-check-input allocate-type-radio" type="radio" name="allocateType" id="allocateType-{{ $loop->index }}" value="{{ $value }}">
                                        <label class="form-check-label" for="allocateType-{{ $loop->index }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Step 2: action + frequency (+ WhatsApp template) revealed after a type is picked --}}
                        <div id="allocateStep2" class="mt-4" style="display:none;">
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Action</label>
                                    <select id="allocateAction" class="form-select form-select-sm">
                                        <option value="" selected>Select action</option>
                                        <option value="allocate_tele_call">Allocate to tele call</option>
                                        <option value="call">Call</option>
                                        <option value="whatsapp">WhatsApp</option>
                                        <option value="physical_visit">Physical visit</option>
                                        <option value="escalation">Escalation</option>
                                        <option value="no_action">No action</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Frequency</label>
                                    <select id="allocateFrequency" class="form-select form-select-sm">
                                        <option value="" selected>Select frequency</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>

                                {{-- WhatsApp Template dropdown, sirf action = whatsapp par dikhega --}}
                                <div class="col-md-6" id="allocateTemplateWrapper" style="display:none;">
                                    <label class="form-label fw-bold">WhatsApp Template</label>
                                    <select id="allocateTemplate" class="form-select form-select-sm">
                                        <option value="" selected>Select template</option>
                                        <option value="template_1">Template 1</option>
                                        <option value="template_2">Template 2</option>
                                        <option value="template_3">Template 3</option>
                                        <option value="template_4">Template 4</option>
                                        <option value="template_5">Template 5</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="allocateAssignBtn" class="btn btn-success" style="display:none;">Assign</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ================= END FOLLOW UP ALLOCATE MODAL ================= --}}

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
                                        <th>Clear Solution (Admin)</th>
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

        {{-- ================= CLEAR PENDING CONFIRMATION MODAL ================= --}}
        <div class="modal fade" id="clearPendingConfirmModal" tabindex="-1" aria-labelledby="clearPendingConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clearPendingConfirmModalLabel">
                            <i class="fa fa-exclamation-triangle text-warning me-2"></i>Are you sure?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            Are you sure you want to clear all <strong>Pending</strong> statuses?
                        </p>
                        <div class="alert alert-warning small mb-0">
                            <i class="fa fa-info-circle me-1"></i>
                            This action will reset the status of the selected ledgers to <strong>"Not Assigned Yet"</strong>
                            and clear their Follow Up Type / Type Details as well.
                            This action <strong>cannot be undone</strong>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="clearPendingConfirmBtn" class="btn btn-danger">
                            <i class="fa fa-refresh me-1"></i> Yes, Clear It
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ================= END CLEAR PENDING CONFIRMATION MODAL ================= --}}

        {{-- ================= CLEAR RESPONDED CONFIRMATION MODAL ================= --}}
        <div class="modal fade" id="clearRespondedConfirmModal" tabindex="-1" aria-labelledby="clearRespondedConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clearRespondedConfirmModalLabel">
                            <i class="fa fa-exclamation-triangle text-warning me-2"></i>Are you sure?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            Are you sure you want to clear all <strong>Responded</strong> statuses?
                        </p>
                        <div class="alert alert-warning small mb-0">
                            <i class="fa fa-info-circle me-1"></i>
                            This action will reset the status of the selected ledgers to <strong>"Not Assigned Yet"</strong>
                            and clear their Follow Up Type / Type Details as well.
                            This action <strong>cannot be undone</strong>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="clearRespondedConfirmBtn" class="btn btn-danger">
                            <i class="fa fa-refresh me-1"></i> Yes, Clear It
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ================= END CLEAR RESPONDED CONFIRMATION MODAL ================= --}}

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

            /* ===== Follow Up Type pill + Type Details chips ===== */
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

        @include('owner.components.footer')

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                let followupTable = null;

                if (window.jQuery && $.fn.DataTable) {
                    // Days filter plugin (10/20/30) — reads data-days on the <tr>
                    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData, counter) {
                        if (settings.nTable.id !== 'example12') return true;

                        const rowNode = settings.aoData[dataIndex].nTr;

                        const daysFilterEl = document.getElementById('daysFilter');
                        if (daysFilterEl && daysFilterEl.value) {
                            if (rowNode.getAttribute('data-days') !== daysFilterEl.value) return false;
                        }

                        const agewiseFilterEl = document.getElementById('agewiseDaysFilter');
                        if (agewiseFilterEl && agewiseFilterEl.value) {
                            const rowDays = parseInt(rowNode.getAttribute('data-days'), 10) || 0;
                            if (rowDays <= parseInt(agewiseFilterEl.value, 10)) return false;
                        }

                        // Month filter — reads data-month on the <tr>
                        const monthFilterEl = document.getElementById('monthFilter');
                        if (monthFilterEl && monthFilterEl.value) {
                            const selectedMonth = new Date(monthFilterEl.value + ' 1, 2000'); // just for parsing "M Y" -> month
                            // Compare using formatted month string stored on row's data-month (Y-m) vs selection (M Y)
                            const rowMonth = rowNode.getAttribute('data-month'); // e.g. 2026-08
                            if (rowMonth) {
                                const [y, m] = rowMonth.split('-');
                                const rowLabel = new Date(y, parseInt(m, 10) - 1, 1)
                                    .toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                                if (rowLabel !== monthFilterEl.value) return false;
                            }
                        }

                        return true;
                    });

                    followupTable = $('#example12').DataTable({ fixedHeader: false });
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

                // ================= HELPER: apply "Not assign yet" state to a single row =================
                function markRowAsNotAssigned(row) {
                    const badge = row.querySelector('.accountant-status-badge');
                    if (badge) {
                        badge.textContent = 'Not assign yet';
                        badge.classList.remove('bg-success', 'bg-warning');
                        badge.classList.add('bg-secondary');
                    }

                    const typeCell = row.querySelector('.followup-type-cell');
                    const detailsCell = row.querySelector('.followup-details-cell');

                    if (typeCell) typeCell.innerHTML = '';
                    if (detailsCell) detailsCell.innerHTML = '';
                }

                // ================= CLEAR ONLY MATCHING STATUS ROWS -> RESET THEM TO "NOT ASSIGN YET" =================
                // statusClass: 'bg-warning' for Pending, 'bg-success' for Responded
                function clearRowsByStatus(statusClass, successMessage, noRecordsMessage) {
                    const allRows = document.querySelectorAll('#example12 tbody tr.ledger-row');

                    if (allRows.length === 0) {
                        showAjaxAlert('No records found to clear.', 'danger');
                        return;
                    }

                    let matchedCount = 0;

                    allRows.forEach(function (row) {
                        const badge = row.querySelector('.accountant-status-badge');
                        if (badge && badge.classList.contains(statusClass)) {
                            markRowAsNotAssigned(row);
                            matchedCount++;
                        }
                    });

                    if (matchedCount === 0) {
                        showAjaxAlert(noRecordsMessage, 'danger');
                        return;
                    }

                    showAjaxAlert(successMessage, 'success');
                }

                // ================= CLEAR PENDING -> CONFIRM VIA ITS OWN MODAL =================
                const clearPendingModalEl = document.getElementById('clearPendingConfirmModal');
                const clearPendingModal = clearPendingModalEl ? new bootstrap.Modal(clearPendingModalEl) : null;
                const clearPendingConfirmBtn = document.getElementById('clearPendingConfirmBtn');

                const clearPendingBtn = document.getElementById('clearPendingBtn');
                if (clearPendingBtn) {
                    clearPendingBtn.addEventListener('click', function () {
                        if (clearPendingModal) clearPendingModal.show();
                    });
                }

                if (clearPendingConfirmBtn) {
                    clearPendingConfirmBtn.addEventListener('click', function () {
                        clearRowsByStatus(
                            'bg-warning',
                            'All Pending statuses set to Not assign yet.',
                            'No Pending records found to clear.'
                        );
                        if (clearPendingModal) clearPendingModal.hide();
                    });
                }

                // ================= CLEAR RESPONDED -> CONFIRM VIA ITS OWN MODAL =================
                const clearRespondedModalEl = document.getElementById('clearRespondedConfirmModal');
                const clearRespondedModal = clearRespondedModalEl ? new bootstrap.Modal(clearRespondedModalEl) : null;
                const clearRespondedConfirmBtn = document.getElementById('clearRespondedConfirmBtn');

                const clearRespondedBtn = document.getElementById('clearRespondedBtn');
                if (clearRespondedBtn) {
                    clearRespondedBtn.addEventListener('click', function () {
                        if (clearRespondedModal) clearRespondedModal.show();
                    });
                }

                if (clearRespondedConfirmBtn) {
                    clearRespondedConfirmBtn.addEventListener('click', function () {
                        clearRowsByStatus(
                            'bg-success',
                            'All Responded statuses set to Not assign yet.',
                            'No Responded records found to clear.'
                        );
                        if (clearRespondedModal) clearRespondedModal.hide();
                    });
                }

                // ================= ROW CLICK -> RESPONDED opens LOG MODAL, OTHERWISE opens ALLOCATE MODAL =================
                const allocateModalEl = document.getElementById('followupAllocateModal');
                const allocateModal = allocateModalEl ? new bootstrap.Modal(allocateModalEl) : null;
                const allocateSelectedNameEl = document.getElementById('allocateSelectedName');

                let selectedLedgerId = null; // holds the single ledger currently being allocated
                let selectedLedgerRow = null; // holds the <tr> element currently being allocated

                document.addEventListener('click', function (e) {
                    // ignore clicks on the "View Log" button — handled separately below
                    if (e.target.closest('.view-log')) return;

                    const row = e.target.closest('#example12 tbody tr.ledger-row');
                    if (!row) return;

                    // ---- Responded rows open the Log Modal instead of the Allocate Modal ----
                    const statusBadge = row.querySelector('.accountant-status-badge');
                    const isResponded = statusBadge && statusBadge.classList.contains('bg-success');

                    if (isResponded) {
                        const rowData = {
                            name: row.getAttribute('data-name') || '-',
                            mobile: row.getAttribute('data-mobile') || '-',
                            balance: row.getAttribute('data-balance') || 0,
                            target: row.getAttribute('data-target') || 0,
                        };

                        document.getElementById('logModalName').textContent = rowData.name;
                        document.getElementById('logModalMobile').textContent = rowData.mobile;

                        renderLogTable([rowData]);

                        const logModal = new bootstrap.Modal(document.getElementById('followupLogModal'));
                        logModal.show();
                        return;
                    }

                    // ---- Otherwise (Pending / Not assign yet / etc.) -> Allocate Modal (existing behaviour) ----
                    selectedLedgerId = row.getAttribute('data-id');
                    selectedLedgerRow = row;
                    const ledgerName = row.getAttribute('data-name') || '-';

                    if (allocateSelectedNameEl) allocateSelectedNameEl.textContent = ledgerName;

                    // reset modal state every time it opens
                    document.querySelectorAll('.allocate-type-radio').forEach(r => r.checked = false);
                    document.getElementById('allocateAction').value = '';
                    document.getElementById('allocateFrequency').value = '';
                    document.getElementById('allocateStep2').style.display = 'none';
                    document.getElementById('allocateAssignBtn').style.display = 'none';

                    // reset & hide the WhatsApp template field every time modal opens
                    const templateWrapperReset = document.getElementById('allocateTemplateWrapper');
                    const templateSelectReset = document.getElementById('allocateTemplate');
                    if (templateSelectReset) templateSelectReset.value = '';
                    if (templateWrapperReset) templateWrapperReset.style.display = 'none';

                    if (allocateModal) allocateModal.show();
                });

                // ================= STEP 1 -> STEP 2 REVEAL =================
                document.querySelectorAll('.allocate-type-radio').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        document.getElementById('allocateStep2').style.display = 'block';
                        document.getElementById('allocateAssignBtn').style.display = 'inline-block';
                    });
                });

                // ================= WHATSAPP TEMPLATE TOGGLE (Allocate Modal) =================
                const allocateActionSelect = document.getElementById('allocateAction');
                const allocateTemplateWrapper = document.getElementById('allocateTemplateWrapper');
                const allocateTemplateSelect = document.getElementById('allocateTemplate');

                if (allocateActionSelect) {
                    allocateActionSelect.addEventListener('change', function () {
                        if (this.value === 'whatsapp') {
                            if (allocateTemplateWrapper) allocateTemplateWrapper.style.display = 'block';
                        } else {
                            if (allocateTemplateWrapper) allocateTemplateWrapper.style.display = 'none';
                            if (allocateTemplateSelect) allocateTemplateSelect.value = '';
                        }
                    });
                }

                // ================= WHATSAPP TEMPLATE TOGGLE (Bulk Action Bar - all panes) =================
                document.querySelectorAll('.bulk-action-select').forEach(function (actionSelect) {
                    const bar = actionSelect.closest('.bulk-action-bar');
                    const templateSelect = bar ? bar.querySelector('.bulk-template-select') : null;
                    if (!templateSelect) return;

                    actionSelect.addEventListener('change', function () {
                        if (this.value === 'whatsapp') {
                            templateSelect.style.display = 'inline-block';
                        } else {
                            templateSelect.style.display = 'none';
                            templateSelect.value = '';
                        }
                    });
                });

                // ================= BULK APPLY BUTTON VALIDATION (template required for WhatsApp) =================
                document.querySelectorAll('.bulk-apply-btn').forEach(function (applyBtn) {
                    applyBtn.addEventListener('click', function () {
                        const bar = applyBtn.closest('.bulk-action-bar');
                        if (!bar) return;

                        const actionSelect = bar.querySelector('.bulk-action-select');
                        const templateSelect = bar.querySelector('.bulk-template-select');

                        const actionValue = actionSelect ? actionSelect.value : '';
                        const templateValue = templateSelect ? templateSelect.value : '';

                        if (!actionValue) {
                            showAjaxAlert('Please select an action.', 'danger');
                            return;
                        }

                        if (actionValue === 'whatsapp' && !templateValue) {
                            showAjaxAlert('Please select a WhatsApp template.', 'danger');
                            return;
                        }

                        // TODO: hook this up to your bulk-assign AJAX call.
                        // Example payload you'd send to backend:
                        // { action: actionValue, template: templateValue || null, ids: [...visible row ids...] }
                    });
                });

                // ================= ASSIGN BUTTON =================
                const allocateAssignBtn = document.getElementById('allocateAssignBtn');
                if (allocateAssignBtn) {
                    allocateAssignBtn.addEventListener('click', function () {
                        const selectedTypeEl = document.querySelector('.allocate-type-radio:checked');
                        const actionValue = document.getElementById('allocateAction').value;
                        const frequencyValue = document.getElementById('allocateFrequency').value;
                        const templateValue = document.getElementById('allocateTemplate').value;

                        if (!selectedLedgerId) {
                            showAjaxAlert('Please select a ledger.', 'danger');
                            return;
                        }
                        if (!selectedTypeEl) {
                            showAjaxAlert('Please select a Follow Up type.', 'danger');
                            return;
                        }
                        if (!actionValue || !frequencyValue) {
                            showAjaxAlert('Please select both Action and Frequency.', 'danger');
                            return;
                        }
                        // template required if action is whatsapp
                        if (actionValue === 'whatsapp' && !templateValue) {
                            showAjaxAlert('Please select a WhatsApp template.', 'danger');
                            return;
                        }

                        const originalText = this.textContent;
                        this.disabled = true;
                        this.textContent = 'Assigning...';
                        const self = this;

                        fetch("{{ url('owner/followup/update-action') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ids: [selectedLedgerId],
                                type: selectedTypeEl.value,
                                action: actionValue,
                                frequency: frequencyValue,
                                template: templateValue || null
                            })
                        })
                        .then(function (response) {
                            if (!response.ok) throw new Error('Request failed');
                            return response.json().catch(() => ({}));
                        })
                        .then(function (data) {
                            showAjaxAlert(data.message ?? 'Follow up allocated successfully!', 'success');
                            allocateModal.hide();
                            selectedLedgerId = null;
                            selectedLedgerRow = null;
                        })
                        .catch(function () {
                            showAjaxAlert('Something went wrong while allocating. Please try again.', 'danger');
                        })
                        .finally(function () {
                            self.disabled = false;
                            self.textContent = originalText;
                        });
                    });
                }

                // ================= VIEW LOG MODAL LOGIC (explicit "View Log" button, e.g. in Quick/Smart tabs) =================
                document.addEventListener('click', function (e) {
                    const btn = e.target.closest('.view-log');
                    if (!btn) return;

                    const rowData = JSON.parse(btn.getAttribute('data-row') || '{}');

                    document.getElementById('logModalName').textContent = rowData.name ?? '-';
                    document.getElementById('logModalMobile').textContent = rowData.mobile ?? '-';

                    let logs = rowData.logs;
                    if (!logs) logs = [rowData];

                    renderLogTable(logs);

                    const logModal = new bootstrap.Modal(document.getElementById('followupLogModal'));
                    logModal.show();
                });

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
                        tbody.innerHTML = `<tr><td colspan="11" class="text-center">No log entries found</td></tr>`;
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
                            <td>${log.freq ?? log.frequency ?? '-'}</td>
                            <td>${log.response ?? '-'}</td>
                            <td>${log.solution ?? '-'}</td>
                            <td>${log.admin_solution ?? '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }

                // ================= MONTH / DAYS FILTER REDRAW =================
                ['monthFilter', 'daysFilter', 'agewiseDaysFilter'].forEach(function (id) {
                    const el = document.getElementById(id);
                    if (el && followupTable) {
                        el.addEventListener('change', function () { followupTable.draw(); });
                    }
                });

            });
        </script>
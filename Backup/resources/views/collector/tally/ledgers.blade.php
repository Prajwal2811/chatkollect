@include('collector.components.header')
<div id="main-wrapper">
    <div class="nav-header">
        <a href="#" class="brand-logo">
            <svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
                <!-- RMS Text -->
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

@include('collector.components.navbar')
@include('collector.components.sidebar')

    <div class="content-body default-height">
        <style>
            /* ===== FIX: force every pie chart canvas to the SAME fixed size ===== */
            .chart-box {
                position: relative;
                height: 260px;
                width: 260px;
                margin: 0 auto;
            }
            .chart-box canvas {
                width: 100% !important;
                height: 100% !important;
            }
        </style>

        <div class="container-fluid">
            @php
                $ledgers = [
                    [
                        'name'  => 'ABC Traders',
                        'under' => 'Sundry Debtors',
                        'rows'  => [
                            'Balance'                  => 500000,
                            'Due'                      => 350000,
                            'Target'                   => 300000,
                            'Sale'                     => 200000,
                            'Other Debits'             => 100000,
                            'Receipts'                 => 1500000,
                            'Not Due'                  => 150000,
                            'Interest Cost'            => 82000,
                            'Interest Received'        => 50000,
                            'Interest Due'             => 17000,
                            'Interest Waived'          => 15000,
                            'Bad Debts / Family A/c'   => 100000,
                            'Total Debtors'            => 600000,
                            'March Closing Pending'    => 200000,
                        ],
                    ],

                    [
                        'name'  => 'XYZ Suppliers',
                        'under' => 'Sundry Creditors',
                        'rows'  => [
                            'Balance'                  => 500000,
                            'Due'                      => 350000,
                            'Target'                   => 300000,
                            'Purchase'                 => 200000,
                            'Other Credits'            => 100000,
                            'Payments'                 => 1500000,
                            'Not Due'                  => 150000,
                            'Interest Cost'            => 82000,
                            'Interest Paid'            => 50000,
                            'Interest Due'             => 17000,
                            'Interest Waived'          => 15000,
                            'Bad Debts / Family A/c'   => 100000,
                            'Total Creditors'          => 600000,
                            'March Closing Pending'    => 200000,
                        ],
                    ],
                ];

                $mergeMap = [
                    'Sale'               => 'Sale / Purchase',
                    'Purchase'           => 'Sale / Purchase',
                    'Other Debits'       => 'Other Debits / Credits',
                    'Other Credits'      => 'Other Debits / Credits',
                    'Receipts'           => 'Receipts / Payments',
                    'Payments'           => 'Receipts / Payments',
                    'Interest Received'  => 'Interest Received / Paid',
                    'Interest Paid'      => 'Interest Received / Paid',
                    'Total Debtors'      => 'Total Debtors / Creditors',
                    'Total Creditors'    => 'Total Debtors / Creditors',
                ];

                $totals = [];
                foreach ($ledgers as $l) {
                    foreach ($l['rows'] as $label => $value) {
                        $key = $mergeMap[$label] ?? $label;
                        $totals[$key] = ($totals[$key] ?? 0) + $value;
                    }
                }

                $breakdown = [];
                foreach ($ledgers as $l) {
                    foreach ($l['rows'] as $label => $value) {
                        $key = $mergeMap[$label] ?? $label;
                        $breakdown[$key][] = [
                            'ledger' => $l['name'],
                            'under'  => $l['under'],
                            'label'  => $label,
                            'value'  => $value,
                        ];
                    }
                }

                $cardStyles = [
                    'Balance'                    => ['bg' => 'primary',  'icon' => '₹'],
                    'Due'                         => ['bg' => 'danger',   'icon' => '₹'],
                    'Target'                      => ['bg' => 'success',  'icon' => '₹'],
                    'Sale / Purchase'             => ['bg' => 'warning',  'icon' => '₹'],
                    'Other Debits / Credits'      => ['bg' => 'info',     'icon' => '₹'],
                    'Receipts / Payments'         => ['bg' => 'secondary','icon' => '₹'],
                    'Not Due'                     => ['bg' => 'success',  'icon' => '₹'],
                    'Interest Cost'               => ['bg' => 'dark',     'icon' => '₹'],
                    'Interest Received / Paid'    => ['bg' => 'info',     'icon' => '₹'],
                    'Interest Due'                => ['bg' => 'danger',   'icon' => '₹'],
                    'Interest Waived'             => ['bg' => 'warning',  'icon' => '₹'],
                    'Bad Debts / Family A/c'      => ['bg' => 'secondary','icon' => '₹'],
                    'Total Debtors / Creditors'   => ['bg' => 'primary',  'icon' => '₹'],
                    'March Closing Pending'       => ['bg' => 'dark',     'icon' => '₹'],
                ];

                $fmt = fn ($n) => number_format($n, 2);

                // Separate debtor/creditor lists for looping with unique IDs
                $debtorLedgers   = collect($ledgers)->where('under', 'Sundry Debtors')->values();
                $creditorLedgers = collect($ledgers)->where('under', 'Sundry Creditors')->values();

                // ===== CHART DATA =====

                // 1. Sale Balance — Sales Collected vs Sales Not Collected
                $saleCollected    = ($totals['Receipts / Payments'] ?? 0);
                $saleNotCollected = ($totals['Due'] ?? 0);

                // 2. Balance - Due - Not Due (directly from totals)
                $balanceDueData = [
                    'Balance' => $totals['Balance'] ?? 0,
                    'Due'     => $totals['Due'] ?? 0,
                    'Not Due' => $totals['Not Due'] ?? 0,
                ];

                // 3. Aging buckets — SAMPLE DATA, replace with real voucher-ageing data
                $agingData = [
                    '15+ Days' => 120000,
                    '45+ Days' => 90000,
                    '90+ Days' => 140000,
                ];

                // 4. Due - Target - After Target
                $dueTargetData = [
                    'Due'          => $totals['Due'] ?? 0,
                    'Target'       => $totals['Target'] ?? 0,
                    'After Target' => max(($totals['Due'] ?? 0) - ($totals['Target'] ?? 0), 0),
                ];

                // 5. Monthly Due - Collection — SAMPLE DATA, replace with real month-wise data
                $monthlyDueData = [
                    'Aug-24' => 140000,
                    'Sep-24' => 79000,
                    'Oct-24' => 83000,
                    'Nov-24' => 134000,
                    'Dec-24' => 225000,
                    'Jan-25' => 430000,
                ];

                // ===== SINGLE SOURCE OF TRUTH FOR CHART COLORS =====
                // These SAME maps drive both the Chart.js pie slices AND the legend
                // badges below each chart, so colors always match.

                $saleBalanceColors = [
                    'Sales Collected'     => '#2ECC71', // Green
                    'Sales Not Collected' => '#E74C3C', // Red
                ];

                $balanceDueColors = [
                    'Balance' => '#4E73DF', // Blue
                    'Due'     => '#F6C23E', // Gold
                    'Not Due' => '#858796', // Gray
                ];

                $agingColors = [
                    '15+ Days' => '#36B9CC', // Cyan
                    '45+ Days' => '#F6C23E', // Yellow
                    '90+ Days' => '#E74A3B', // Red
                ];

                $dueTargetColors = [
                    'Due'          => '#E74A3B', // Red
                    'Target'       => '#1CC88A', // Green
                    'After Target' => '#4E73DF', // Blue
                ];

                $monthlyColors = [
                    '#4E73DF', // Blue
                    '#1CC88A', // Green
                    '#36B9CC', // Cyan
                    '#F6C23E', // Gold
                    '#E74A3B', // Red
                    '#6F42C1', // Purple
                ];
            @endphp

            <div class="row g-3 mb-4">
                @foreach ($totals as $label => $value)
                    @php
                        $style = $cardStyles[$label] ?? ['bg' => 'primary', 'icon' => '₹'];
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100"
                             style="cursor:pointer"
                             data-bs-toggle="modal"
                             data-bs-target="#ledgerBreakdownModal"
                             onclick="showLedgerBreakdown('{{ $label }}')">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">{{ $label }}</h6>
                                        <h4 class="mb-0 fw-bold">
                                            ₹ {{ $fmt($value) }}
                                        </h4>
                                    </div>
                                    <div class="rounded-circle bg-{{ $style['bg'] }}" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:bold;">
                                        {{ $style['icon'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Ledger breakdown modal -->
            <div class="modal fade" id="ledgerBreakdownModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ledgerBreakdownTitle">-</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Ledger</th>
                                            <th>Field</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ledgerBreakdownBody"></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th class="text-end" id="ledgerBreakdownTotal">-</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- row -->
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
                        </div>
                        <script>
                            setTimeout(function () {
                                let alert = document.getElementById('successAlert');
                                if (alert) {
                                    let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                    bsAlert.close();
                                }
                            }, 3000);
                        </script>
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="card-title">Ledger List</h4>
                            <span class="badge bg-primary">2</span>
                        </div>
                        <div class="card-body">
                        <input type="text" value="{{ $company }}" hidden>
                            <ul class="nav nav-pills mb-4" id="ledgerTabs" role="tablist">
                                <li class="nav-item me-2">
                                    <button class="nav-link active"
                                        data-bs-toggle="pill"
                                        data-bs-target="#debtors-tab">
                                        Sundry Debtors ({{ $debtorLedgers->count() }})
                                    </button>
                                </li>

                                <li class="nav-item me-2">
                                    <button class="nav-link"
                                        data-bs-toggle="pill"
                                        data-bs-target="#creditors-tab">
                                        Sundry Creditors ({{ $creditorLedgers->count() }})
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Debtors -->
                                <div class="tab-pane fade show active" id="debtors-tab">
                                    <div class="accordion" id="debtorAccordion">
                                        @foreach ($debtorLedgers as $index => $l)
                                            @php
                                                $uid = 'debtor-' . $index; // UNIQUE ID per ledger — accordion fix
                                                $rowMeta = [
                                                    'Balance'                 => ['class' => 'table-warning', 'value' => $l['rows']['Balance'], 'pct' => '83%', 'pctClass' => 'bg-success'],
                                                    'Due'                     => ['class' => 'table-warning', 'value' => $l['rows']['Due'], 'pct' => '70%', 'pctClass' => 'bg-warning text-dark'],
                                                    'Target'                  => ['class' => 'table-success', 'value' => $l['rows']['Target'], 'pct' => '86%', 'pctClass' => 'bg-success'],
                                                    'Sale'                    => ['class' => 'table-success', 'value' => $l['rows']['Sale'], 'pct' => '100%', 'pctClass' => 'bg-primary'],
                                                    'Other Debits'            => ['class' => '', 'value' => $l['rows']['Other Debits'], 'pct' => '5%', 'pctClass' => ''],
                                                    'Receipts'                => ['class' => '', 'value' => $l['rows']['Receipts'], 'pct' => '75%', 'pctClass' => ''],
                                                    'Not Due'                 => ['class' => '', 'value' => $l['rows']['Not Due'], 'pct' => '30%', 'pctClass' => ''],
                                                    'Interest Cost'           => ['class' => '', 'value' => $l['rows']['Interest Cost'], 'pct' => '4.1%', 'pctClass' => ''],
                                                    'Interest Received'       => ['class' => '', 'value' => $l['rows']['Interest Received'], 'pct' => '61%', 'pctClass' => ''],
                                                    'Interest Due'            => ['class' => 'table-danger', 'value' => $l['rows']['Interest Due'], 'pct' => '3.4%', 'pctClass' => ''],
                                                    'Interest Waived'         => ['class' => 'table-warning', 'value' => $l['rows']['Interest Waived'], 'pct' => '3%', 'pctClass' => ''],
                                                    'Bad Debts / Family A/c'  => ['class' => '', 'value' => $l['rows']['Bad Debts / Family A/c'], 'pct' => '16.67%', 'pctClass' => ''],
                                                    'Total Debtors'           => ['class' => 'table-primary', 'value' => $l['rows']['Total Debtors'], 'pct' => '100%', 'pctClass' => ''],
                                                    'March Closing Pending'   => ['class' => 'table-secondary', 'value' => $l['rows']['March Closing Pending'], 'pct' => '33%', 'pctClass' => ''],
                                                ];
                                            @endphp
                                            <div class="accordion-item mb-3 border rounded shadow-sm">
                                                <h2 class="accordion-header" id="heading-{{ $uid }}">
                                                    <button class="accordion-button collapsed fw-bold"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapse-{{ $uid }}"
                                                        aria-expanded="false"
                                                        aria-controls="collapse-{{ $uid }}">
                                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                            <div>
                                                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                                {{ $l['name'] }}
                                                            </div>
                                                            <span class="badge bg-success">Sundry Debtors</span>
                                                        </div>
                                                    </button>
                                                </h2>

                                                <div id="collapse-{{ $uid }}"
                                                     class="accordion-collapse collapse"
                                                     aria-labelledby="heading-{{ $uid }}"
                                                     data-bs-parent="#debtorAccordion">
                                                    <div class="accordion-body">
                                                        <div class="mx-auto col-lg-10 col-12">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered text-center align-middle table-hover">
                                                                    <tbody>
                                                                        @foreach ($rowMeta as $label => $meta)
                                                                            <tr class="{{ $meta['class'] }} clickable-row"
                                                                                style="cursor:pointer"
                                                                                onclick="window.location='{{ route('collector.tally.ledger.detail') }}'">
                                                                                <th width="40%">{{ $label }}</th>
                                                                                <td><strong>{{ $fmt($meta['value']) }}</strong></td>
                                                                                <td>
                                                                                    @if ($meta['pct'])
                                                                                        <span class="badge {{ $meta['pctClass'] ?: 'bg-secondary' }}">{{ $meta['pct'] }}</span>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="d-flex justify-content-end gap-2">
                                                                <a href="{{ route('collector.tally.ledger.track') }}" class="btn btn-primary">Track</a>
                                                                <a href="{{  route('collector.tally.ledger.collect') }}" target="_blank" class="btn btn-success">Collect</a>
                                                                <a href="{{  route('collector.tally.ledger.followup') }}" class="btn btn-warning">Follow Up</a>
                                                                <a href="{{ route('collector.tally.ledger.vouchers', ['company' => urlencode($company), 'ledger' => $l['name'], 'under' => 'Sundry Debtors']) }}"
                                                                   class="btn btn-dark">
                                                                    View Vouchers
                                                                </a>
                                                                <a href="#" class="btn btn-danger" target="_blank">
                                                                    <i class="fa fa-file-pdf me-1"></i> PDF
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Creditors -->
                                <div class="tab-pane fade" id="creditors-tab">
                                    <div class="accordion" id="creditorAccordion">
                                        @foreach ($creditorLedgers as $index => $l)
                                            @php
                                                $uid = 'creditor-' . $index; // UNIQUE ID per ledger
                                                $rowMeta = [
                                                    'Balance'                 => ['class' => 'table-warning', 'value' => $l['rows']['Balance'], 'pct' => '83%', 'pctClass' => 'bg-success'],
                                                    'Due'                     => ['class' => 'table-warning', 'value' => $l['rows']['Due'], 'pct' => '70%', 'pctClass' => 'bg-warning text-dark'],
                                                    'Target'                  => ['class' => 'table-success', 'value' => $l['rows']['Target'], 'pct' => '86%', 'pctClass' => 'bg-success'],
                                                    'Purchase'                => ['class' => 'table-success', 'value' => $l['rows']['Purchase'], 'pct' => '100%', 'pctClass' => 'bg-primary'],
                                                    'Other Credits'           => ['class' => '', 'value' => $l['rows']['Other Credits'], 'pct' => '5%', 'pctClass' => ''],
                                                    'Payments'                => ['class' => '', 'value' => $l['rows']['Payments'], 'pct' => '75%', 'pctClass' => ''],
                                                    'Not Due'                 => ['class' => '', 'value' => $l['rows']['Not Due'], 'pct' => '30%', 'pctClass' => ''],
                                                    'Interest Cost'           => ['class' => '', 'value' => $l['rows']['Interest Cost'], 'pct' => '4.1%', 'pctClass' => ''],
                                                    'Interest Paid'           => ['class' => '', 'value' => $l['rows']['Interest Paid'], 'pct' => '61%', 'pctClass' => ''],
                                                    'Interest Due'            => ['class' => 'table-danger', 'value' => $l['rows']['Interest Due'], 'pct' => '3.4%', 'pctClass' => ''],
                                                    'Interest Waived'         => ['class' => 'table-warning', 'value' => $l['rows']['Interest Waived'], 'pct' => '3%', 'pctClass' => ''],
                                                    'Bad Debts / Family A/c'  => ['class' => '', 'value' => $l['rows']['Bad Debts / Family A/c'], 'pct' => '16.67%', 'pctClass' => ''],
                                                    'Total Creditors'         => ['class' => 'table-primary', 'value' => $l['rows']['Total Creditors'], 'pct' => '100%', 'pctClass' => ''],
                                                    'March Closing Pending'   => ['class' => 'table-secondary', 'value' => $l['rows']['March Closing Pending'], 'pct' => '33%', 'pctClass' => ''],
                                                ];
                                            @endphp
                                            <div class="accordion-item mb-3 border rounded shadow-sm">
                                                <h2 class="accordion-header" id="heading-{{ $uid }}">
                                                    <button class="accordion-button collapsed fw-bold"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapse-{{ $uid }}"
                                                        aria-expanded="false"
                                                        aria-controls="collapse-{{ $uid }}">
                                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                            <div>
                                                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                                {{ $l['name'] }}
                                                            </div>
                                                            <span class="badge bg-danger">Sundry Creditors</span>
                                                        </div>
                                                    </button>
                                                </h2>

                                                <div id="collapse-{{ $uid }}"
                                                     class="accordion-collapse collapse"
                                                     aria-labelledby="heading-{{ $uid }}"
                                                     data-bs-parent="#creditorAccordion">
                                                    <div class="accordion-body">
                                                        <div class="mx-auto col-lg-10 col-12">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered text-center align-middle table-hover">
                                                                    <tbody>
                                                                        @foreach ($rowMeta as $label => $meta)
                                                                            <tr class="{{ $meta['class'] }} clickable-row"
                                                                                style="cursor:pointer"
                                                                                onclick="window.location='{{ route('collector.tally.ledger.detail') }}'">
                                                                                <th width="40%">{{ $label }}</th>
                                                                                <td><strong>{{ $fmt($meta['value']) }}</strong></td>
                                                                                <td>
                                                                                    @if ($meta['pct'])
                                                                                        <span class="badge {{ $meta['pctClass'] ?: 'bg-secondary' }}">{{ $meta['pct'] }}</span>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="d-flex justify-content-end gap-2">
                                                                <a href="{{ route('collector.tally.ledger.track') }}" class="btn btn-primary">Track</a>
                                                                <a href="{{  route('collector.tally.ledger.collect') }}" class="btn btn-success">Pay</a>
                                                                <button class="btn btn-warning">Follow Up</button>
                                                                <a href="{{ route('collector.tally.ledger.vouchers', ['company' => urlencode($company), 'ledger' => $l['name'], 'under' => 'Sundry Creditors']) }}"
                                                                   class="btn btn-dark">
                                                                    View Vouchers
                                                                </a>
                                                                <a href="{{ route('collector.tally.ledger.pdf', ['company' => urlencode($company), 'ledger' => $l['name'], 'under' => 'Sundry Creditors']) }}"
                                                                   class="btn btn-danger" target="_blank">
                                                                    <i class="fa fa-file-pdf me-1"></i> PDF
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts Row (matches reference Excel layout) -->
            <div class="row g-3 mt-2">

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><strong>Sale Balance</strong></div>
                        <div class="card-body">
                            <div class="chart-box">
                                <canvas id="chartSaleBalance"></canvas>
                            </div>
                            <div class="mt-3 border-top pt-2 d-flex justify-content-around flex-wrap">
                                <div class="text-center px-2">
                                    <span class="badge" style="background:{{ $saleBalanceColors['Sales Collected'] }};">&nbsp;</span>
                                    <div class="small text-muted">Sales Collected</div>
                                    <div class="fw-bold">₹ {{ $fmt($saleCollected) }}</div>
                                </div>
                                <div class="text-center px-2">
                                    <span class="badge" style="background:{{ $saleBalanceColors['Sales Not Collected'] }};">&nbsp;</span>
                                    <div class="small text-muted">Sales Not Collected</div>
                                    <div class="fw-bold">₹ {{ $fmt($saleNotCollected) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><strong>Balance - Due</strong></div>
                        <div class="card-body">
                            <div class="chart-box">
                                <canvas id="chartBalanceDue"></canvas>
                            </div>
                            <div class="mt-3 border-top pt-2 d-flex justify-content-around flex-wrap">
                                @foreach ($balanceDueData as $label => $value)
                                    <div class="text-center px-2">
                                        <span class="badge" style="background:{{ $balanceDueColors[$label] }};">&nbsp;</span>
                                        <div class="small text-muted">{{ $label }}</div>
                                        <div class="fw-bold">₹ {{ $fmt($value) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><strong>15+ Days, 45+ Days &amp; 90+ Days</strong></div>
                        <div class="card-body">
                            <div class="chart-box">
                                <canvas id="chartAging"></canvas>
                            </div>
                            <div class="mt-3 border-top pt-2 d-flex justify-content-around flex-wrap">
                                @foreach ($agingData as $label => $value)
                                    <div class="text-center px-2">
                                        <span class="badge" style="background:{{ $agingColors[$label] }};">&nbsp;</span>
                                        <div class="small text-muted">{{ $label }}</div>
                                        <div class="fw-bold">₹ {{ $fmt($value) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><strong>Due - Target</strong></div>
                        <div class="card-body">
                            <div class="chart-box">
                                <canvas id="chartDueTarget"></canvas>
                            </div>
                            <div class="mt-3 border-top pt-2 d-flex justify-content-around flex-wrap">
                                @foreach ($dueTargetData as $label => $value)
                                    <div class="text-center px-2">
                                        <span class="badge" style="background:{{ $dueTargetColors[$label] }};">&nbsp;</span>
                                        <div class="small text-muted">{{ $label }}</div>
                                        <div class="fw-bold">₹ {{ $fmt($value) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><strong>Monthly Due - Collection</strong></div>
                        <div class="card-body">
                            <div class="chart-box">
                                <canvas id="chartMonthly"></canvas>
                            </div>
                            <div class="mt-3 border-top pt-2 d-flex justify-content-around flex-wrap">
                                @php $i = 0; @endphp
                                @foreach ($monthlyDueData as $label => $value)
                                    <div class="text-center px-2 mb-1">
                                        <span class="badge" style="background:{{ $monthlyColors[$i % count($monthlyColors)] }};">&nbsp;</span>
                                        <div class="small text-muted">{{ $label }}</div>
                                        <div class="fw-bold">₹ {{ $fmt($value) }}</div>
                                    </div>
                                    @php $i++; @endphp
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /Analytics Charts Row -->

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        const ledgerBreakdownData = @json($breakdown);

        function showLedgerBreakdown(label) {
            document.getElementById('ledgerBreakdownTitle').innerText = label;

            const tbody = document.getElementById('ledgerBreakdownBody');
            tbody.innerHTML = '';

            let total = 0;
            const rows = ledgerBreakdownData[label] || [];

            rows.forEach(item => {
                total += Number(item.value);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <strong>${item.ledger}</strong>
                        <span class="badge bg-secondary ms-1">${item.under}</span>
                    </td>
                    <td class="text-muted">${item.label}</td>
                    <td class="text-end fw-bold">₹ ${Number(item.value).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('ledgerBreakdownTotal').innerText =
                '₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});
        }
    </script>

    <script>
        Chart.register(ChartDataLabels);

        // Chart data passed from PHP
        const saleBalance   = { collected: {{ $saleCollected }}, notCollected: {{ $saleNotCollected }} };
        const balanceDue    = @json($balanceDueData);
        const agingData     = @json($agingData);
        const dueTargetData = @json($dueTargetData);
        const monthlyData   = @json($monthlyDueData);

        // SAME color maps used by the PHP-rendered legend badges above —
        // this is what keeps chart slices and legend colors in sync.
        const saleBalanceColors = @json($saleBalanceColors);
        const balanceDueColors  = @json($balanceDueColors);
        const agingColors       = @json($agingColors);
        const dueTargetColors   = @json($dueTargetColors);
        const monthlyColors     = @json($monthlyColors);

        // Shared "Excel style" outside data label: Label, Value, and % all shown on the slice
        const dataLabelPlugin = {
            color: '#000',
            font: { size: 11, weight: 'bold' },
            formatter: function (value, ctx) {
                const label = ctx.chart.data.labels[ctx.dataIndex];
                const arr = ctx.chart.data.datasets[0].data;
                const total = arr.reduce((a, b) => a + b, 0);
                const pct = total ? ((value / total) * 100).toFixed(2) : 0;
                return label + '\n₹' + Number(value).toLocaleString('en-IN') + '\n' + pct + '%';
            },
            anchor: 'end',
            align: 'end',
            offset: 8,
            clamp: true,
        };

        // FIX: fixed aspectRatio + smaller padding so every pie renders
        // at the SAME physical size regardless of number of slices / label length
        function makePie(canvasId, labels, values, colors) {
            new Chart(document.getElementById(canvasId), {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1,
                    layout: { padding: 20 },
                    plugins: {
                        legend: { display: false },
                        datalabels: dataLabelPlugin,
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total ? ((ctx.parsed / total) * 100).toFixed(2) : 0;
                                    return `${ctx.label}: ₹${ctx.parsed.toLocaleString('en-IN')} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {

        // 1. Sale Balance — labels/colors pulled by key so order never desyncs
        makePie('chartSaleBalance',
            ['Sales Not Collected', 'Sales Collected'],
            [saleBalance.notCollected, saleBalance.collected],
            [saleBalanceColors['Sales Not Collected'], saleBalanceColors['Sales Collected']]
        );

        // 2. Balance Due
        makePie('chartBalanceDue',
            Object.keys(balanceDue),
            Object.values(balanceDue),
            Object.keys(balanceDue).map(k => balanceDueColors[k])
        );

        // 3. Aging
        makePie('chartAging',
            Object.keys(agingData),
            Object.values(agingData),
            Object.keys(agingData).map(k => agingColors[k])
        );

        // 4. Due / Target
        makePie('chartDueTarget',
            Object.keys(dueTargetData),
            Object.values(dueTargetData),
            Object.keys(dueTargetData).map(k => dueTargetColors[k])
        );

        // 5. Monthly — cycle through the shared palette by index
        makePie('chartMonthly',
            Object.keys(monthlyData),
            Object.values(monthlyData),
            Object.keys(monthlyData).map((_, i) => monthlyColors[i % monthlyColors.length])
        );

    });
    </script>
@include('collector.components.footer')
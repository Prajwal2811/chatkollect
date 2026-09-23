@include('owner.tally.components.header')
<div id="main-wrapper">
    <div class="nav-header">
        <a href="#" class="brand-logo">
            <svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
                <!-- RMS Text -->
                <text x="55" y="32" font-size="22" font-family="Arial, sans-serif" font-weight="bold" fill="#4E3F6B">
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
        <style>
            .accordion-button:not(.collapsed) {
                background-color: #f1f0f8;
                color: #4E3F6B;
            }

            .manual-input-form label {
                font-weight: 600;
                font-size: 13px;
                margin-bottom: 4px;
                color: #444;
            }

            table.example11 tbody tr {
                cursor: default;
            }

            .common-defaults-card {
                border: 1px solid #e6e6f0;
                border-radius: 12px;
                padding: 18px 20px;
                margin-bottom: 20px;
                background: linear-gradient(180deg, #fafaff 0%, #f5f4fb 100%);
                box-shadow: 0 2px 6px rgba(78, 63, 107, 0.06);
            }

            .common-defaults-card h6 {
                color: #4E3F6B;
                font-weight: 700;
            }

            .common-defaults-toggle-btn {
                border: 1px solid #4E3F6B;
                color: #4E3F6B;
                background: #fff;
                font-weight: 600;
                font-size: 13px;
                padding: 6px 16px;
                border-radius: 8px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all .2s ease;
            }

            .common-defaults-toggle-btn:hover {
                background: #4E3F6B;
                color: #fff;
            }

            .common-defaults-toggle-btn .toggle-icon {
                transition: transform .2s ease;
                font-size: 11px;
            }

            .common-defaults-toggle-btn[aria-expanded="true"] .toggle-icon {
                transform: rotate(180deg);
            }

            .common-defaults-warning {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                background: #fff8e6;
                border: 1px solid #ffe1a8;
                color: #7a5a00;
                font-size: 12.5px;
                font-weight: 500;
                padding: 10px 14px;
                border-radius: 8px;
                margin-bottom: 16px;
            }

            .common-defaults-warning i {
                margin-top: 2px;
                color: #d99b00;
            }

            /* Bill-by-bill indicator */
            .billwise-badge {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: 11px;
                font-weight: 600;
                padding: 3px 8px;
                border-radius: 20px;
                cursor: help;
            }

            .billwise-badge.is-yes {
                background: #fdeeee;
                color: #b02a2a;
                border: 1px solid #f3caca;
            }

            .billwise-badge.is-no {
                background: #eef7ee;
                color: #2e7d32;
                border: 1px solid #cfe9cf;
            }

            .billwise-note {
                display: block;
                margin-top: 3px;
                font-size: 11px;
                color: #b02a2a;
                font-weight: 500;
            }

            /* Value-source indicator (Tally vs Default) */
            .source-badge {
                display: inline-block;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .2px;
                padding: 2px 7px;
                border-radius: 10px;
                margin-left: 6px;
                vertical-align: middle;
                cursor: help;
            }

            .source-badge.source-tally {
                background: #eef2ff;
                color: #3730a3;
                border: 1px solid #c7d2fe;
            }

            .source-badge.source-default {
                background: #fff7ed;
                color: #9a3412;
                border: 1px solid #fed7aa;
            }

            /* Source filter bar */
            .source-filter-bar {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }

            .source-filter-bar label {
                font-size: 12.5px;
                font-weight: 600;
                color: #444;
                margin: 0 6px 0 0;
            }

            .js-source-filter {
                width: auto;
                min-width: 190px;
            }
        </style>

        <div class="container-fluid">
            @php
                $fmt = fn ($n) => number_format((float) $n, 2);

                // Helper: detect whether a ledger has "Maintain balances bill-by-bill" = Yes in Tally.
                // Accepts common truthy representations coming from the Tally XML/JSON import
                // (Yes / yes / 1 / true) so it works regardless of how the importer normalizes it.
                $isBillWise = function ($l) {
                    $val = $l['maintain_bill_by_bill'] ?? $l['maintain_balance_bill_by_bill'] ?? null;
                    return in_array(
                        strtolower(trim((string) $val)),
                        ['yes'],
                        true
                    );
                };

                // Helper: render a small "Tally" / "Default" badge next to a value, based on
                // credit_period_source / interest_rate_source coming from the backend.
                // Expected values: 'tally' or 'default' (case-insensitive). Anything else -> no badge.
                $sourceBadge = function ($source) {
                    $source = strtolower(trim((string) $source));

                    if ($source === 'tally') {
                        return '<span class="source-badge source-tally" title="This value is coming from Tally">Tally</span>';
                    }

                    if ($source === 'default') {
                        return '<span class="source-badge source-default" title="This value is the common default you set">Default</span>';
                    }

                    return '';
                };

                // Normalized (lowercase, trimmed) source value used for row data-attributes / filtering.
                $sourceKey = function ($source) {
                    $source = strtolower(trim((string) $source));
                    return in_array($source, ['tally', 'default'], true) ? $source : '';
                };

                $debtorLedgers   = collect($ledgers)->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Debtors')->values();
                $creditorLedgers = collect($ledgers)->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Creditors')->values();

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
                foreach ($debtorLedgers->concat($creditorLedgers) as $l) {
                    foreach (($l['rows'] ?? []) as $label => $value) {
                        $key = $mergeMap[$label] ?? $label;
                        $totals[$key] = ($totals[$key] ?? 0) + $value;
                    }
                }

                $breakdown = [];
                foreach ($debtorLedgers->concat($creditorLedgers) as $l) {
                    foreach (($l['rows'] ?? []) as $label => $value) {
                        $key = $mergeMap[$label] ?? $label;
                        $breakdown[$key][] = [
                            'ledger' => $l['name'],
                            'under'  => $l['under'] ?? '',
                            'label'  => $label,
                            'value'  => $value,
                        ];
                    }
                }
            @endphp

            <!-- Ledger Breakdown Modal -->
            <div class="modal fade" id="ledgerBreakdownModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ledgerBreakdownTitle">-</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                        <tr>
                                            <th>Ledger</th>
                                            <th>Field</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ledgerBreakdownBody"></tbody>
                                </table>
                            </div>
                            <table class="table table-bordered align-middle mb-0">
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

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="col-10 mx-auto mt-4" id="ajaxAlertWrapper">
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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Ledger List</h4>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary">
                                    {{ count($ledgers) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="text" value="{{ $company }}" hidden>
                            <ul class="nav nav-pills mb-4" id="ledgerTabs" role="tablist">
                                <li class="nav-item me-2">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#debtors-tab">
                                        Sundry Debtors ({{ $debtorLedgers->count() }})
                                    </button>
                                </li>
                                <li class="nav-item me-2">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#creditors-tab">
                                        Sundry Creditors ({{ $creditorLedgers->count() }})
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Debtors Tab -->
                                <div class="tab-pane fade show active" id="debtors-tab">

                                    <!-- Toggle button + Source filters (same row) -->
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div class="source-filter-bar mb-0">
                                            <div>
                                                <label for="debtorCreditSourceFilter">Credit Period Source</label>
                                                <select id="debtorCreditSourceFilter" class="form-control form-control-sm js-source-filter" data-table="#example11" data-column="credit">
                                                    <option value="">All</option>
                                                    <option value="tally">Tally</option>
                                                    <option value="default">Default</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="debtorInterestSourceFilter">Interest Rate Source</label>
                                                <select id="debtorInterestSourceFilter" class="form-control form-control-sm js-source-filter" data-table="#example11" data-column="interest">
                                                    <option value="">All</option>
                                                    <option value="tally">Tally</option>
                                                    <option value="default">Default</option>
                                                </select>
                                            </div>
                                        </div>

                                        <button type="button"
                                                class="common-defaults-toggle-btn js-defaults-toggle"
                                                data-target="#debtorDefaultsCollapse"
                                                aria-expanded="false">
                                            <i class="fa fa-cog"></i>
                                            Set Common Default Settings
                                            <span class="toggle-icon">▾</span>
                                        </button>
                                    </div>

                                    <!-- Collapsible common defaults form (hidden by default) -->
                                    <div class="collapse" id="debtorDefaultsCollapse">
                                        <div class="common-defaults-card">
                                            <h6 class="mb-3">Common Default Settings (Receivable)</h6>

                                            <div class="common-defaults-warning">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                <span>
                                                    <strong>Note:</strong> These default values will be applied
                                                    <u>only</u> to those ledgers whose Credit Period, Interest Rate
                                                    and Collector Name are <strong>not already present in Tally</strong>.
                                                    Ledgers that already have these values set in Tally will
                                                    <strong>not</strong> be overwritten or updated.
                                                </span>
                                            </div>

                                            <form action="{{ route('owner.tally.ledger.set-common-setting') }}" method="POST" class="manual-input-form">
                                                @csrf
                                                <input type="hidden" name="company" value="{{ $company }}">
                                                <input type="hidden" name="under" value="Sundry Debtors">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label>Default Credit Period - Receivable</label>
                                                        <input type="number" min="0" class="form-control form-control-sm" name="credit_period" placeholder="Enter credit period (days)">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Default Interest Rate - Receivable (Base Debtor Int Rate)</label>
                                                        <input type="number" min="0" step="0.01" class="form-control form-control-sm" name="interest_rate" placeholder="Enter interest rate (%)">
                                                    </div>
                                                    @php
                                                        $collectors = App\Models\Collector::where('owner_id', Auth::guard('owner')->id())->get();
                                                    @endphp
                                                    <div class="col-md-4">
                                                        <label>Default Collector Name - Receivable</label>
                                                        <select class="form-control form-control-sm" name="collector_id">
                                                            <option value="">Select Collector</option>

                                                            @foreach($collectors as $collector)
                                                                <option value="{{ $collector->id }}">
                                                                    {{ $collector->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end gap-2 mt-3">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        Save Details
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example11" class="display" style="min-width: 845px">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Ledger Name</th>
                                                    <th>Mobile</th>
                                                    <th>Credit Period - Receivable</th>
                                                    <th>Interest Rate - Receivable</th>
                                                    <th>Collector Name - Receivable</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($debtorLedgers as $index => $l)
                                                    @php
                                                        $billWise = $isBillWise($l);
                                                        $creditSrc = $sourceKey($l['credit_period_source'] ?? null);
                                                        $interestSrc = $sourceKey($l['interest_rate_source'] ?? null);
                                                    @endphp
                                                    <tr data-credit-source="{{ $creditSrc }}" data-interest-source="{{ $interestSrc }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $l['name'] }}</td>
                                                        <td>{{ $l['mobile'] ?? '-' }}</td>
                                                        <td>
                                                            {{ !empty($l['credit_period']) ? $l['credit_period'] : '-' }}
                                                            @if(!empty($l['credit_period']))
                                                                {!! $sourceBadge($creditSrc) !!}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ !empty($l['interest_rate']) ? $fmt($l['interest_rate']) . ' % p.a.' : '-' }}
                                                            @if(!empty($l['interest_rate']))
                                                                {!! $sourceBadge($interestSrc) !!}
                                                            @endif
                                                        </td>
                                                        @php
                                                            $collectorName = \App\Models\Collector::where('id', $l['assigned_collector'])->first();
                                                        @endphp
                                                        <td>{{ $collectorName->name ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">No Debtors Found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Creditors Tab -->
                                <div class="tab-pane fade" id="creditors-tab">

                                    <!-- Toggle button + Source filters (same row) -->
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div class="source-filter-bar mb-0">
                                            <div>
                                                <label for="creditorCreditSourceFilter">Credit Period Source</label>
                                                <select id="creditorCreditSourceFilter" class="form-control form-control-sm js-source-filter" data-table="#example" data-column="credit">
                                                    <option value="">All</option>
                                                    <option value="tally">Tally</option>
                                                    <option value="default">Default</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="creditorInterestSourceFilter">Interest Rate Source</label>
                                                <select id="creditorInterestSourceFilter" class="form-control form-control-sm js-source-filter" data-table="#example" data-column="interest">
                                                    <option value="">All</option>
                                                    <option value="tally">Tally</option>
                                                    <option value="default">Default</option>
                                                </select>
                                            </div>
                                        </div>

                                        <button type="button" class="common-defaults-toggle-btn js-defaults-toggle" data-target="#creditorDefaultsCollapse" aria-expanded="false">
                                            <i class="fa fa-cog"></i>
                                            Set Common Default Settings
                                            <span class="toggle-icon">▾</span>
                                        </button>
                                    </div>

                                    <!-- Collapsible common defaults form (hidden by default) -->
                                    <div class="collapse" id="creditorDefaultsCollapse">
                                        <div class="common-defaults-card">
                                            <h6 class="mb-3">Common Default Settings (Payable)</h6>

                                            <div class="common-defaults-warning">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                <span>
                                                    <strong>Note:</strong> These default values will be applied
                                                    <u>only</u> to those ledgers whose Credit Period, Interest Rate
                                                    and Collector Name are <strong>not already present in Tally</strong>.
                                                    Ledgers that already have these values set in Tally will
                                                    <strong>not</strong> be overwritten or updated.
                                                </span>
                                            </div>

                                            <form action="{{ route('owner.tally.ledger.set-common-setting') }}" method="POST" class="manual-input-form">
                                                @csrf
                                                <input type="hidden" name="company" value="{{ $company }}">
                                                <input type="hidden" name="under" value="Sundry Creditors">

                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label>Default Credit Period - Payable</label>
                                                        <input type="number" min="0" class="form-control form-control-sm" name="credit_period" placeholder="Enter credit period (days)">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Default Interest Rate - Payable (Base Creditor Int Rate)</label>
                                                        <input type="number" min="0" step="0.01" class="form-control form-control-sm" name="interest_rate" placeholder="Enter interest rate (%)">
                                                    </div>
                                                    @php
                                                        $collectors = App\Models\Collector::where('owner_id', Auth::guard('owner')->id())->get();
                                                    @endphp

                                                    <div class="col-md-4">
                                                        <label>Default Collector Name - Payable</label>

                                                        <select class="form-control form-control-sm" name="collector_id">
                                                            <option value="">Select Collector</option>

                                                            @foreach($collectors as $collector)
                                                                <option value="{{ $collector->id }}">
                                                                    {{ $collector->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="d-flex justify-content-end gap-2 mt-3">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        Save Details
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="display" style="min-width: 845px">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Ledger Name</th>
                                                    <th>Mobile</th>
                                                    <th>Credit Period - Payable</th>
                                                    <th>Interest Rate - Payable</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($creditorLedgers as $index => $l)
                                                    @php
                                                        $billWise = $isBillWise($l);
                                                        $creditSrc = $sourceKey($l['credit_period_source'] ?? null);
                                                        $interestSrc = $sourceKey($l['interest_rate_source'] ?? null);
                                                    @endphp
                                                    <tr data-credit-source="{{ $creditSrc }}" data-interest-source="{{ $interestSrc }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $l['name'] }}</td>
                                                        <td>{{ $l['mobile'] ?? '-' }}</td>
                                                        <td>
                                                            {{ $l['credit_period'] ?? '-' }}
                                                            @if(!empty($l['credit_period']))
                                                                {!! $sourceBadge($creditSrc) !!}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ !empty($l['interest_rate']) ? $fmt($l['interest_rate']) . ' % p.a.' : '-' }}
                                                            @if(!empty($l['interest_rate']))
                                                                {!! $sourceBadge($interestSrc) !!}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">No Creditors Found</td>
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
        </div>
    </div>

    @include('owner.tally.components.footer')

    <script>
    // Send CSRF token with every AJAX request automatically
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const breakdownData = @json($breakdown);

        function showLedgerBreakdown(label) {
            let items = breakdownData[label] || [];
            let html = '';
            let total = 0;

            items.forEach(function (item) {
                total += parseFloat(item.value);
                let formattedValue = new Intl.NumberFormat('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(item.value);

                html += `<tr>
                    <td>${item.ledger} <span class="badge bg-light text-dark ms-1">${item.under}</span></td>
                    <td>${item.label}</td>
                    <td class="text-end">₹ ${formattedValue}</td>
                </tr>`;
            });

            if (items.length === 0) {
                html = '<tr><td colspan="3" class="text-center text-muted">No data available</td></tr>';
            }

            let formattedTotal = new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(total);

            $('#ledgerBreakdownTitle').text(label + ' Breakdown');
            $('#ledgerBreakdownBody').html(html);
            $('#ledgerBreakdownTotal').text('₹ ' + formattedTotal);
        }

        // Small "Tally" / "Default" badge builder, mirrors the Blade $sourceBadge helper.
        // Expected values: 'tally' or 'default' (case-insensitive). Anything else -> ''.
        function sourceBadgeHtml(source) {
            const s = String(source || '').toLowerCase().trim();

            if (s === 'tally') {
                return '<span class="source-badge source-tally" title="This value is coming from Tally">Tally</span>';
            }
            if (s === 'default') {
                return '<span class="source-badge source-default" title="This value is the common default you set">Default</span>';
            }
            return '';
        }

        // Normalize a source value to 'tally' | 'default' | '' for data-attributes / filtering.
        function sourceKeyJs(source) {
            const s = String(source || '').toLowerCase().trim();
            return (s === 'tally' || s === 'default') ? s : '';
        }

        // ---------- Source (Tally / Default) filter for both tables ----------
        // Keeps the currently selected filter value per table/column, e.g.
        // sourceFilterState['#example11'].credit = 'tally'
        const sourceFilterState = {
            '#example11': { credit: '', interest: '' },
            '#example':   { credit: '', interest: '' },
        };

        if ($.fn.DataTable) {
            $.fn.dataTable.ext.search.push(function (settings, searchData, index, rowData, counter) {
                const tableSelector = '#' + settings.nTable.id;
                const state = sourceFilterState[tableSelector];
                if (!state) {
                    return true; // not one of our filtered tables, don't touch it
                }

                const $row = $(settings.oInstance.api().row(index).node());
                if (!$row.length) return true;

                if (state.credit && $row.data('credit-source') !== state.credit) {
                    return false;
                }
                if (state.interest && $row.data('interest-source') !== state.interest) {
                    return false;
                }
                return true;
            });
        }

        $(document).on('change', '.js-source-filter', function () {
            const $select = $(this);
            const tableSelector = $select.data('table');
            const column = $select.data('column'); // 'credit' or 'interest'
            const value = $select.val();

            if (!sourceFilterState[tableSelector]) {
                sourceFilterState[tableSelector] = { credit: '', interest: '' };
            }
            sourceFilterState[tableSelector][column] = value;

            if ($.fn.DataTable && $.fn.DataTable.isDataTable(tableSelector)) {
                $(tableSelector).DataTable().draw();
            }
        });

        $(function () {
            if ($.fn.DataTable) {
                $('.example11').each(function () {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({ paging: true, searching: true, info: true });
                    }
                });
            }

            $('[data-bs-toggle="tooltip"]').each(function () {
                bootstrap.Tooltip.getOrCreateInstance(this);
            });
        });

        $(document).on('click', '.js-defaults-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $target = $($(this).data('target'));
            if (!$target.length) return;

            bootstrap.Collapse.getOrCreateInstance($target[0], { toggle: false }).toggle();
        });

        $(document).on('shown.bs.collapse hidden.bs.collapse', '.collapse', function () {
            const isOpen = $(this).hasClass('show');
            $(`.js-defaults-toggle[data-target="#${this.id}"]`).attr('aria-expanded', isOpen);
        });

        // ---------- AJAX submit for the Common Default Settings forms ----------

        function showAjaxAlert(type, message) {
            const alertId = type === 'success' ? 'successAlert' : 'errorAlert';
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

            $('#successAlert, #errorAlert').remove();

            const html = `
                <div id="${alertId}" class="alert ${alertClass} alert-dismissible fade show text-center" role="alert">
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                    ${message}
                </div>`;

            $('#ajaxAlertWrapper').html(html);

            setTimeout(function () {
                let alert = document.getElementById(alertId);
                if (alert) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            }, 3000);
        }

        function buildDebtorRow(l, index) {
            const billWise = String(l.maintain_bill_by_bill || '').toLowerCase() === 'yes';
            const creditSrc = sourceKeyJs(l.credit_period_source);
            const interestSrc = sourceKeyJs(l.interest_rate_source);

            const creditPeriod = l.credit_period
                ? l.credit_period + sourceBadgeHtml(creditSrc)
                : '-';
            const interestRate = l.interest_rate
                ? parseFloat(l.interest_rate).toFixed(2) + ' % p.a.' + sourceBadgeHtml(interestSrc)
                : '-';

            return `<tr data-credit-source="${creditSrc}" data-interest-source="${interestSrc}">
                <td>${index + 1}</td>
                <td>${l.name}</td>
                <td>${l.mobile || '-'}</td>
                <td>${creditPeriod}</td>
                <td>${interestRate}</td>
                <td>${l.collector_name || '-'}</td>
            </tr>`;
        }

        function buildCreditorRow(l, index) {
            const billWise = String(l.maintain_bill_by_bill || '').toLowerCase() === 'yes';
            const creditSrc = sourceKeyJs(l.credit_period_source);
            const interestSrc = sourceKeyJs(l.interest_rate_source);

            const creditPeriod = l.credit_period
                ? l.credit_period + sourceBadgeHtml(creditSrc)
                : '-';
            const interestRate = l.interest_rate
                ? parseFloat(l.interest_rate).toFixed(2) + ' % p.a.' + sourceBadgeHtml(interestSrc)
                : '-';

            return `<tr data-credit-source="${creditSrc}" data-interest-source="${interestSrc}">
                <td>${index + 1}</td>
                <td>${l.name}</td>
                <td>${l.mobile || '-'}</td>
                <td>${creditPeriod}</td>
                <td>${interestRate}</td>
            </tr>`;
        }

        function renderTable(under, ledgers) {
            if (under === 'Sundry Debtors') {
                let rows = ledgers.length
                    ? ledgers.map((l, i) => buildDebtorRow(l, i)).join('')
                    : '<tr><td colspan="6" class="text-center text-muted">No Debtors Found</td></tr>';
                $('#example11 tbody').html(rows);

                $('#ledgerTabs .nav-link:contains("Sundry Debtors")')
                    .text(`Sundry Debtors (${ledgers.length})`);
            } else {
                let rows = ledgers.length
                    ? ledgers.map((l, i) => buildCreditorRow(l, i)).join('')
                    : '<tr><td colspan="6" class="text-center text-muted">No Creditors Found</td></tr>';
                $('#example tbody').html(rows);

                $('#ledgerTabs .nav-link:contains("Sundry Creditors")')
                    .text(`Sundry Creditors (${ledgers.length})`);
            }

            // Re-apply DataTable so the freshly rendered rows are picked up by the plugin/filter.
            if ($.fn.DataTable) {
                const tableSelector = under === 'Sundry Debtors' ? '#example11' : '#example';
                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    $(tableSelector).DataTable().rows().invalidate().draw();
                }
            }
        }

        $(document).on('submit', '.manual-input-form', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalBtnText = $submitBtn.text();

            $submitBtn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json',
            })
            .done(function (res) {
                console.log('AJAX success response:', res);
                showAjaxAlert(res.success ? 'success' : 'error', res.message);

                if (res.success && res.ledgers) {
                    renderTable(res.under, res.ledgers);

                    const $collapseParent = $form.closest('.collapse');
                    if ($collapseParent.length) {
                        bootstrap.Collapse.getOrCreateInstance($collapseParent[0]).hide();
                    }

                    $form[0].reset();
                }
            })
            .fail(function (xhr) {
                console.error('AJAX error:', xhr.status, xhr.responseText);
                const message = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                showAjaxAlert('error', message);
            })
            .always(function () {
                $submitBtn.prop('disabled', false).text(originalBtnText);
            });
        });
    </script>
</div>
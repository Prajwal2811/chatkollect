@include('owner.components.header')

@php
    // ---------------------------------------------------------
    // DUMMY DATA (for students / testing only)
    // Yeh data seedha view ke andar generate hota hai, controller
    // se kuch bhi pass karne ki zaroorat nahi.
    // ---------------------------------------------------------
    $company = 'Demo Company Pvt Ltd';

    $ledgers = [
        [
            'name'           => 'Rahul Traders',
            'under'          => 'Sundry Debtors',
            'mobile'         => '9876543210',
            'credit_period'  => 30,
            'interest_rate'  => 12,
            'collector_name' => 'Amit Sharma',
        ],
        [
            'name'           => 'Sharma Enterprises',
            'under'          => 'Sundry Debtors',
            'mobile'         => null, // incomplete ledger -> koi rows/summary nahi milegi
        ],
        [
            'name'           => 'Verma & Sons',
            'under'          => 'Sundry Debtors',
            'mobile'         => '9988776655',
            'credit_period'  => 15,
            'interest_rate'  => 9.5,
            'collector_name' => 'Priya Verma',
        ],
        [
            'name'           => 'Global Suppliers',
            'under'          => 'Sundry Creditors',
            'mobile'         => '9123456780',
            'credit_period'  => 45,
            'interest_rate'  => 10,
        ],
        [
            'name'           => 'City Hardware',
            'under'          => 'Sundry Creditors',
            'mobile'         => null, // incomplete ledger
        ],
        [
            'name'           => 'National Traders',
            'under'          => 'Sundry Creditors',
            'mobile'         => '9001122334',
            'credit_period'  => 60,
            'interest_rate'  => 11,
        ],
    ];
@endphp

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
    @include('owner.components.navbar')
    @include('owner.components.sidebar')

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
        </style>

        <div class="container-fluid">
            @php
                $sampleDebtorRows = [
                    'Balance'                => 500000,
                    'Due'                    => 350000,
                    'Target'                 => 300000,
                    'Sale'                   => 200000,
                    'Other Debits'           => 100000,
                    'Receipts'               => 1500000,
                    'Not Due'                => 150000,
                    'Interest Cost'          => 82000,
                    'Interest Received'      => 50000,
                    'Interest Due'           => 17000,
                    'Interest Waived'        => 15000,
                    'Bad Debts / Family A/c' => 100000,
                    'Total Debtors'          => 600000,
                    'March Closing Pending'  => 200000,
                ];

                $sampleCreditorRows = [
                    'Balance'                => 500000,
                    'Due'                    => 350000,
                    'Target'                 => 300000,
                    'Purchase'               => 200000,
                    'Other Credits'          => 100000,
                    'Payments'               => 1500000,
                    'Not Due'                => 150000,
                    'Interest Cost'          => 82000,
                    'Interest Paid'          => 50000,
                    'Interest Due'           => 17000,
                    'Interest Waived'        => 15000,
                    'Bad Debts / Family A/c' => 100000,
                    'Total Creditors'        => 600000,
                    'March Closing Pending'  => 200000,
                ];

                $fmt = fn ($n) => number_format((float) $n, 2);

                // A ledger is considered "incomplete" (data missing in Tally) when it
                // has no mobile number saved against it.
                $isComplete = fn ($l) => !empty($l['mobile']);

                $debtorLedgers   = collect($ledgers)->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Debtors')
                    ->map(function ($l) use ($sampleDebtorRows, $isComplete) {
                        return $l + ['rows' => $isComplete($l) ? $sampleDebtorRows : []];
                    })->values();

                $creditorLedgers = collect($ledgers)->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Creditors')
                    ->map(function ($l) use ($sampleCreditorRows, $isComplete) {
                        return $l + ['rows' => $isComplete($l) ? $sampleCreditorRows : []];
                    })->values();

                // Single combined list (Debtors + Creditors together, no type shown)
                $allLedgers = $debtorLedgers->concat($creditorLedgers)->values();

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

                // Only ledgers with complete Tally data (rows not empty) contribute
                // to the summary cards / breakdown modal.
                $totals = [];
                foreach ($allLedgers as $l) {
                    foreach ($l['rows'] as $label => $value) {
                        $key = $mergeMap[$label] ?? $label;
                        $totals[$key] = ($totals[$key] ?? 0) + $value;
                    }
                }

                $breakdown = [];
                foreach ($allLedgers as $l) {
                    foreach ($l['rows'] as $label => $value) {
                        $key = $mergeMap[$label] ?? $label;
                        $breakdown[$key][] = [
                            'ledger' => $l['name'],
                            'label'  => $label,
                            'value'  => $value,
                        ];
                    }
                }

                $cardStyles = [
                    'Balance'                   => ['bg' => 'primary',   'icon' => '₹'],
                    'Due'                       => ['bg' => 'danger',    'icon' => '₹'],
                    'Target'                    => ['bg' => 'success',   'icon' => '₹'],
                    'Sale / Purchase'           => ['bg' => 'warning',   'icon' => '₹'],
                    'Other Debits / Credits'    => ['bg' => 'info',      'icon' => '₹'],
                    'Receipts / Payments'       => ['bg' => 'secondary', 'icon' => '₹'],
                    'Not Due'                   => ['bg' => 'success',   'icon' => '₹'],
                    'Interest Cost'             => ['bg' => 'dark',      'icon' => '₹'],
                    'Interest Received / Paid'  => ['bg' => 'info',      'icon' => '₹'],
                    'Interest Due'              => ['bg' => 'danger',    'icon' => '₹'],
                    'Interest Waived'           => ['bg' => 'warning',   'icon' => '₹'],
                    'Bad Debts / Family A/c'    => ['bg' => 'secondary', 'icon' => '₹'],
                    'Total Debtors / Creditors' => ['bg' => 'primary',   'icon' => '₹'],
                    'March Closing Pending'     => ['bg' => 'dark',      'icon' => '₹'],
                ];
            @endphp

            <!-- Summary Cards (hidden by default) -->
            <div id="summaryCardsSection" style="display:none;">
                <div class="row g-3 mb-4">
                    @foreach ($totals as $label => $value)
                        @php $style = $cardStyles[$label] ?? ['bg' => 'dark', 'icon' => '₹']; @endphp
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <div class="card border-0 shadow-sm h-100"
                                 style="cursor:pointer"
                                 data-bs-toggle="modal"
                                 data-bs-target="#ledgerBreakdownModal"
                                 onclick="showLedgerBreakdown('{{ addslashes($label) }}')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">{{ $label }}</h6>
                                            <h4 class="mb-0 fw-bold">₹ {{ $fmt($value) }}</h4>
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
            </div>

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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Student List</h4>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary">
                                    {{ count($ledgers) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="text" value="{{ $company }}" hidden>

                            <!-- Common Defaults Toggle -->
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button"
                                        class="common-defaults-toggle-btn"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#defaultsCollapse"
                                        aria-expanded="false"
                                        aria-controls="defaultsCollapse">
                                    <i class="fa fa-cog"></i>
                                    Set Common Default Settings
                                    <span class="toggle-icon">▾</span>
                                </button>
                            </div>

                            <!-- Collapsible common defaults form -->
                            <div class="collapse" id="defaultsCollapse">
                                <div class="common-defaults-card">
                                    <h6 class="mb-3">Common Default Settings</h6>

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

                                    <form class="manual-input-form common-details-form" data-company="{{ $company }}">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label>Default Credit Period</label>
                                                <input type="number" min="0" class="form-control form-control-sm" name="credit_period" placeholder="Enter credit period (days)">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Default Interest Rate</label>
                                                <input type="number" min="0" step="0.01" class="form-control form-control-sm" name="interest_rate" placeholder="Enter interest rate (%)">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Default Collector Name</label>
                                                <input type="text" class="form-control form-control-sm" name="collector_name" placeholder="Enter collector name">
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

                            <!-- Single Combined Ledger List (no Type column) -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover example11" id="ledgerTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Ledger Name</th>
                                            <th>Mobile</th>
                                            <th>Credit Period</th>
                                            <th>Interest Rate</th>
                                            <th>Collector Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allLedgers as $index => $l)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $l['name'] }}</td>
                                                <td>{{ $l['mobile'] ?? '-' }}</td>
                                                <td>{{ $l['credit_period'] ?? '-' }}</td>
                                                <td>{{ $l['interest_rate'] ?? '-' }}</td>
                                                <td>{{ $l['collector_name'] ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No Ledgers Found</td>
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

    @include('owner.components.footer')

    <script>
        const breakdownData = @json($breakdown);

        function showLedgerBreakdown(label) {
            let items = breakdownData[label] || [];
            let html = '';
            let total = 0;

            items.forEach(function(item) {
                total += parseFloat(item.value);
                let formattedValue = new Intl.NumberFormat('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(item.value);

                html += `<tr>
                    <td>${item.ledger}</td>
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

        // Optional: initialize DataTables on the ledger table if the
        // DataTables plugin is loaded on this page.
        $(function () {
            if ($.fn.DataTable) {
                $('.example11').each(function () {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            paging: true,
                            searching: true,
                            info: true
                        });
                    }
                });
            }
        });

        // Save common default details (applies to debtor / creditor ledgers
        // that don't already have these values set in Tally — see backend note
        // below. This does not overwrite existing Tally data.)
        $(document).on('submit', '.common-details-form', function (e) {
            e.preventDefault();

            let $form   = $(this);
            let $btn    = $form.find('button[type="submit"]');
            let payload = {
                company: $form.data('company'),
                credit_period:  $form.find('[name="credit_period"]').val(),
                interest_rate:  $form.find('[name="interest_rate"]').val(),
                collector_name: $form.find('[name="collector_name"]').val() || null,
                _token: "{{ csrf_token() }}"
            };

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                // TODO: point this to your actual save route, e.g.
                // Server side: only apply credit_period / interest_rate / collector_name
                // to ledgers where these fields are currently empty/null in Tally.
                // Skip (do not overwrite) any ledger that already has these values.
                url: "{{ route('owner.tally.ledger.saveDetails') }}",
                method: 'POST',
                data: payload,
                success: function (res) {
                    $btn.text('Saved');
                    setTimeout(() => $btn.prop('disabled', false).text('Save Details'), 1500);
                },
                error: function (xhr) {
                    alert('Could not save details. Please try again.');
                    $btn.prop('disabled', false).text('Save Details');
                }
            });
        });
    </script>
</div>
@include('accountant.components.header')
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

@include('accountant.components.navbar')
@include('accountant.components.sidebar')

    <div class="content-body default-height">
        <style>
            .accordion-button:not(.collapsed) {
                background-color: #f1f0f8;
                color: #4E3F6B;
            }

            .ledger-check-wrap {
                display: inline-flex;
                align-items: center;
                margin-right: 10px;
            }

            .ledger-check-wrap input {
                width: 18px;
                height: 18px;
                cursor: pointer;
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

                $debtorLedgers   = collect($ledgers)->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Debtors')
                    ->map(fn ($l) => $l + ['rows' => $sampleDebtorRows])->values();

                $creditorLedgers = collect($ledgers)->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Creditors')
                    ->map(fn ($l) => $l + ['rows' => $sampleCreditorRows])->values();

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
                    foreach ($l['rows'] as $label => $value) {
                        $key = $mergeMap[$label] ?? $label;
                        $totals[$key] = ($totals[$key] ?? 0) + $value;
                    }
                }

                $breakdown = [];
                foreach ($debtorLedgers->concat($creditorLedgers) as $l) {
                    foreach ($l['rows'] as $label => $value) {
                        $key = $mergeMap[$label] ?? $label;
                        $breakdown[$key][] = [
                            'ledger' => $l['name'],
                            'under'  => $l['under'] ?? '',
                            'label'  => $label,
                            'value'  => $value,
                        ];
                    }
                }

                $rowClassMap = [
                    'Balance'               => 'table-warning',
                    'Due'                   => 'table-warning',
                    'Target'                => 'table-success',
                    'Sale'                  => 'table-success',
                    'Purchase'              => 'table-success',
                    'Interest Due'          => 'table-danger',
                    'Interest Waived'       => 'table-warning',
                    'Total Debtors'         => 'table-primary',
                    'Total Creditors'       => 'table-primary',
                    'March Closing Pending' => 'table-secondary',
                ];

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

            <!-- Summary Cards Toggle Button -->
            <div class="d-flex justify-content-end mb-2">
                <button class="btn btn-outline-primary btn-sm" type="button" id="summaryToggleBtn">
                    <i class="fa fa-chart-bar me-1"></i>
                    <span class="toggle-text">Show Summary</span>
                </button>
            </div>

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
                                    <div class="accordion" id="debtorAccordion">
                                        @forelse($debtorLedgers as $index => $l)
                                            @php
                                                $uid = 'debtor-' . $index;
                                                $rows = $l['rows'];
                                            @endphp
                                            <div class="accordion-item mb-3 border rounded shadow-sm">
                                                <h2 class="accordion-header" id="heading-{{ $uid }}">
                                                    <div class="d-flex align-items-center w-100">
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
                                                                    @if(!empty($l['mobile']))
                                                                        <span class="text-muted small ms-2">({{ $l['mobile'] }})</span>
                                                                    @endif
                                                                </div>
                                                                <span class="badge bg-success">Sundry Debtors</span>
                                                            </div>
                                                        </button>
                                                    </div>
                                                </h2>

                                                <div id="collapse-{{ $uid }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $uid }}" data-bs-parent="#debtorAccordion">
                                                    <div class="accordion-body">
                                                        <div class="mx-auto col-lg-10 col-12">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered text-center align-middle table-hover">
                                                                    <tbody>
                                                                        @foreach($rows as $label => $value)
                                                                            <tr class="{{ $rowClassMap[$label] ?? '' }}">
                                                                                <th width="40%">{{ $label }}</th>
                                                                                <td><strong>₹ {{ $fmt($value) }}</strong></td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="d-flex justify-content-end gap-2">
                                                                <a href="{{ route('accountant.tally.ledger.vouchers', [
                                                                    'company' => urlencode($company),
                                                                    'ledger'  => urlencode($l['name']),
                                                                    'under'   => urlencode($l['under'] ?? '')
                                                                ]) }}" class="btn btn-primary btn-sm">
                                                                    View Vouchers
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-center text-muted">No Debtors Found</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Creditors Tab -->
                                <div class="tab-pane fade" id="creditors-tab">
                                    <div class="accordion" id="creditorAccordion">
                                        @forelse($creditorLedgers as $index => $l)
                                            @php
                                                $uid = 'creditor-' . $index;
                                                $rows = $l['rows'];
                                            @endphp
                                            <div class="accordion-item mb-3 border rounded shadow-sm">
                                                <h2 class="accordion-header" id="heading-{{ $uid }}">
                                                    <div class="d-flex align-items-center w-100">
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
                                                                    @if(!empty($l['mobile']))
                                                                        <span class="text-muted small ms-2">({{ $l['mobile'] }})</span>
                                                                    @endif
                                                                </div>
                                                                <span class="badge bg-danger">Sundry Creditors</span>
                                                            </div>
                                                        </button>
                                                    </div>
                                                </h2>

                                                <div id="collapse-{{ $uid }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $uid }}" data-bs-parent="#creditorAccordion">
                                                    <div class="accordion-body">
                                                        <div class="mx-auto col-lg-10 col-12">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered text-center align-middle table-hover">
                                                                    <tbody>
                                                                        @foreach($rows as $label => $value)
                                                                            <tr class="{{ $rowClassMap[$label] ?? '' }}">
                                                                                <th width="40%">{{ $label }}</th>
                                                                                <td><strong>₹ {{ $fmt($value) }}</strong></td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="d-flex justify-content-end gap-2">
                                                                <a href="{{ route('accountant.tally.ledger.vouchers', [
                                                                    'company' => urlencode($company),
                                                                    'ledger'  => urlencode($l['name']),
                                                                    'under'   => urlencode($l['under'] ?? '')
                                                                ]) }}" class="btn btn-primary btn-sm">
                                                                    View Vouchers
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-center text-muted">No Creditors Found</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@include('accountant.components.footer')
    
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

    // Toggle Summary Cards section on button click
    $('#summaryToggleBtn').on('click', function () {
        let $section = $('#summaryCardsSection');
        let $btnText = $(this).find('.toggle-text');

        $section.slideToggle(200, function () {
            if ($section.is(':visible')) {
                $btnText.text('Hide Summary');
            } else {
                $btnText.text('Show Summary');
            }
        });
    });

    // Common Follow Up button (Ledger List header) opens the modal.
    // Ledger/Under are left blank since this is no longer per-ledger.
    $(document).on("click", ".openFollowupModal", function () {
        $("#followupCompany").val($(this).data("company"));
        $("#followupLedger").val('');
        $("#followupUnder").val('');
    });
</script>
</div>

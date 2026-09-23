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

                .assign-bad-debt-bar {
                    position: fixed;
                    right: 20px;
                    z-index: 1050;
                }

                #assignBadDebtBarDebtor {
                    bottom: 20px;
                }

                #assignBadDebtBarCreditor {
                    bottom: 80px;
                }

                .status-dot {
                    display: inline-block;
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    margin-left: 8px;
                    vertical-align: middle;
                    border: 1px solid rgba(0,0,0,0.15);
                }

                .rank-badge {
                    display: inline-block;
                    min-width: 20px;
                    padding: 2px 7px;
                    border-radius: 50px;
                    font-size: 11px;
                    font-weight: bold;
                    color: #fff;
                    margin-left: 8px;
                    vertical-align: middle;
                    text-align: center;
                }

                .ledger-mobile {
                    font-size: 12px;
                    color: #6c757d;
                    margin-left: 6px;
                }

                .ledger-filter-bar {
                    background: #f8f9fb;
                    border: 1px solid #eceaf5;
                    border-radius: 8px;
                    padding: 10px 12px;
                    flex-wrap: nowrap !important;
                    overflow-x: auto;
                    white-space: nowrap;
                }

                .ledger-filter-bar .form-select {
                    min-width: 140px;
                    width: auto;
                    flex: 0 0 auto;
                }

                .filter-legend-dot {
                    display: inline-block;
                    width: 9px;
                    height: 9px;
                    border-radius: 50%;
                    margin-right: 4px;
                    vertical-align: middle;
                }

                .ledger-filter-bar .filter-count-text {
                    white-space: nowrap;
                }

                .ledger-filter-bar::-webkit-scrollbar {
                    height: 4px;
                }

                .ledger-filter-bar .filter-search-wrap {
                    min-width: 200px;
                    max-width: 240px;
                    flex: 0 0 auto;
                }

                .ledger-filter-bar .filter-search-wrap .input-group-text {
                    background: #fff;
                }

                /* Summary accordion - kept visually identical to Ledger List accordion */
                .summary-value-badge {
                    font-size: 13px;
                    padding: 6px 12px;
                }

                /* Clickable Balance / Due / etc. cells inside ledger row tables */
                .ledger-field-value {
                    cursor: pointer;
                    color: inherit;
                    display: inline-block;
                }

                .ledger-field-value:hover strong {
                    text-decoration: underline;
                    color: #4E3F6B;
                }

                .ledger-field-value .fa-file-invoice {
                    font-size: 11px;
                    margin-left: 4px;
                    opacity: 0.6;
                }

                /* Balance breakdown status badges (inside ledgerVoucherModal) */
                .balance-status-badge {
                    font-size: 11px;
                    padding: 4px 8px;
                }

                /* Credit Period editable input inside Balance Due table */
                .credit-period-input {
                    width: 90px;
                    margin: 0 auto;
                }

                .ledger-field-row {
                    cursor: pointer;
                }

                .ledger-field-row:hover {
                    background-color: #f8f7fc;
                }

                .ledger-field-row:hover .ledger-field-value strong {
                    text-decoration: underline;
                    color: #4E3F6B;
                }
            </style>

            <div class="container-fluid">
                @php
                    $fmt = fn ($n) => number_format((float) $n, 2);

                    $statusColorMap = [
                        'success'   => '#28a745',
                        'danger'    => '#dc3545',
                        'secondary' => '#adb5bd',
                    ];
                    $statusLabelMap = [
                        'success'   => 'Marked - Good Standing',
                        'danger'    => 'Marked - Issue',
                        'secondary' => 'Unmarked',
                    ];

                    $overlimitColor = '#6f42c1';
                    $overlimitLabel = 'Overlimit';

                    $rankColorMap = [
                        'A' => '#198754',
                        'B' => '#0d6efd',
                        'C' => '#fd7e14',
                        'D' => '#dc3545',
                    ];
                    $rankLabelMap = [
                        'A' => 'Rank A - Excellent',
                        'B' => 'Rank B - Good',
                        'C' => 'Rank C - Average',
                        'D' => 'Rank D - Poor',
                    ];

                    $debtorLedgers = collect($ledgers)
                        ->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Debtors')
                        ->values()
                        ->map(function ($l) {
                            $rows = [
                                'Balance'                => $l['balance']           ?? 0,
                                'Balance Due'            => $l['due']                ?? 0,
                                'Target'                 => $l['target']             ?? 0,
                                'Sale'                   => $l['sale']                ?? 0,
                                'Other Debits'           => $l['other_debits']       ?? 0,
                                'Receipts'               => $l['receipts']           ?? 0,
                                'Not Due'                => $l['not_due']            ?? 0,
                                'Interest Cost'          => $l['interest_cost']      ?? 0,
                                'Interest Received'      => $l['interest_received']  ?? 0,
                                'Interest Due'           => $l['interest_due']       ?? 0,
                                'Interest Waived'        => $l['interest_waived']    ?? 0,
                                'Bad Debts / Family A/c' => $l['bad_debts']          ?? 0,
                                'Total Debtors'          => $l['total_debtors']      ?? 0,
                                'March Closing Pending'  => $l['march_closing_pending'] ?? 0,
                            ];

                            return $l + [
                                'rows'      => $rows,
                                'mobile'    => $l['mobile']    ?? '',
                                'status'    => $l['status']    ?? 'secondary',
                                'overlimit' => $l['overlimit'] ?? false,
                                'rank'      => $l['rank']      ?? null,
                            ];
                        });

                    $creditorLedgers = collect($ledgers)
                        ->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Creditors')
                        ->values()
                        ->map(function ($l) {
                            $rows = [
                                'Balance'                => $l['balance']           ?? 0,
                                'Balance Due'            => $l['due']                ?? 0,
                                'Target'                 => $l['target']             ?? 0,
                                'Purchase'               => $l['purchase']           ?? 0,
                                'Other Credits'          => $l['other_credits']      ?? 0,
                                'Payments'               => $l['payments']           ?? 0,
                                'Not Due'                => $l['not_due']            ?? 0,
                                'Interest Cost'          => $l['interest_cost']      ?? 0,
                                'Interest Paid'          => $l['interest_paid']      ?? 0,
                                'Interest Due'           => $l['interest_due']       ?? 0,
                                'Interest Waived'        => $l['interest_waived']    ?? 0,
                                'Bad Debts / Family A/c' => $l['bad_debts']          ?? 0,
                                'Total Creditors'        => $l['total_creditors']    ?? 0,
                                'March Closing Pending'  => $l['march_closing_pending'] ?? 0,
                            ];

                            return $l + [
                                'rows'      => $rows,
                                'mobile'    => $l['mobile']    ?? '',
                                'status'    => $l['status']    ?? 'secondary',
                                'overlimit' => $l['overlimit'] ?? false,
                                'rank'      => $l['rank']      ?? null,
                            ];
                        });

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


                    $computeTotals = function ($ledgerList) use ($mergeMap) {
                        $t = [];
                        foreach ($ledgerList as $l) {
                            foreach ($l['rows'] as $label => $value) {
                                $key = $mergeMap[$label] ?? $label;
                                $t[$key] = ($t[$key] ?? 0) + $value;
                            }
                        }
                        return $t;
                    };

                    $computeBreakdown = function ($ledgerList) use ($mergeMap) {
                        $b = [];
                        foreach ($ledgerList as $l) {
                            foreach ($l['rows'] as $label => $value) {
                                $key = $mergeMap[$label] ?? $label;
                                $b[$key][] = [
                                    'ledger' => $l['name'],
                                    'under'  => $l['under'] ?? '',
                                    'label'  => $label,
                                    'value'  => $value,
                                ];
                            }
                        }
                        return $b;
                    };

                    $totalsDebtor      = $computeTotals($debtorLedgers);
                    $totalsCreditor    = $computeTotals($creditorLedgers);
                    $breakdownDebtor   = $computeBreakdown($debtorLedgers);
                    $breakdownCreditor = $computeBreakdown($creditorLedgers);

                    $rowClassMap = [
                        'Balance'               => 'table-warning',
                        'Balance Due'           => 'table-warning',
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
                        'Balance Due'               => ['bg' => 'danger',    'icon' => '₹'],
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

                <!-- Sundry Debtors Summary (visible only when Debtors tab is active) -->
                <div id="summaryCardsSectionDebtor" class="summary-cards-section" data-scope="debtor" style="display:none;">
                    <div class="d-flex align-items-center mb-2">
                        <h6 class="text-uppercase text-success fw-bold mb-0">Sundry Debtors Summary</h6>
                    </div>
                    <div class="accordion mb-4" id="summaryAccordionDebtor">
                        @forelse ($totalsDebtor as $label => $value)
                            @php $style = $cardStyles[$label] ?? ['bg' => 'dark', 'icon' => '₹']; @endphp
                            <div class="accordion-item mb-3 border rounded shadow-sm">
                                <h2 class="accordion-header">
                                    <div class="d-flex align-items-center w-100">
                                        <button class="accordion-button collapsed fw-bold"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#ledgerBreakdownModal"
                                            onclick="showLedgerBreakdown('{{ addslashes($label) }}', 'debtor')">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">{{ $loop->iteration }}</span>
                                                    {{ $label }}
                                                </div>
                                                <span class="badge bg-{{ $style['bg'] }} summary-value-badge">₹ {{ $fmt($value) }}</span>
                                            </div>
                                        </button>
                                    </div>
                                </h2>
                            </div>
                        @empty
                            <p class="text-center text-muted">No Debtor data available</p>
                        @endforelse
                    </div>
                </div>

                <!-- Sundry Creditors Summary (visible only when Creditors tab is active) -->
                <div id="summaryCardsSectionCreditor" class="summary-cards-section" data-scope="creditor" style="display:none;">
                    <div class="d-flex align-items-center mb-2">
                        <h6 class="text-uppercase text-danger fw-bold mb-0">Sundry Creditors Summary</h6>
                    </div>
                    <div class="accordion mb-4" id="summaryAccordionCreditor">
                        @forelse ($totalsCreditor as $label => $value)
                            @php $style = $cardStyles[$label] ?? ['bg' => 'dark', 'icon' => '₹']; @endphp
                            <div class="accordion-item mb-3 border rounded shadow-sm">
                                <h2 class="accordion-header">
                                    <div class="d-flex align-items-center w-100">
                                        <button class="accordion-button collapsed fw-bold"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#ledgerBreakdownModal"
                                            onclick="showLedgerBreakdown('{{ addslashes($label) }}', 'creditor')">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">{{ $loop->iteration }}</span>
                                                    {{ $label }}
                                                </div>
                                                <span class="badge bg-{{ $style['bg'] }} summary-value-badge">₹ {{ $fmt($value) }}</span>
                                            </div>
                                        </button>
                                    </div>
                                </h2>
                            </div>
                        @empty
                            <p class="text-center text-muted">No Creditor data available</p>
                        @endforelse
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

                                <!-- 🔍 Search box -->
                                <div class="mb-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                        <input type="text" id="ledgerBreakdownSearch" class="form-control" placeholder="Search ledger, field...">
                                    </div>
                                </div>

                                <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
                                    <table class="table table-bordered text-center align-middle table-hover mb-0">
                                        <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                            <tr>
                                                <th>Ledger</th>
                                                <th>Field</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ledgerBreakdownBody"></tbody>
                                    </table>
                                </div>
                                <table class="table table-bordered text-center align-middle mb-0">
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th id="ledgerBreakdownTotal">-</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ledger Field Voucher Modal -->
                <div class="modal fade" id="ledgerVoucherModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ledgerVoucherModalTitle">-</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="ledgerVoucherLoading" class="text-center py-4 d-none">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 mb-0 text-muted">Loading vouchers...</p>
                                </div>
                                <div id="ledgerVoucherError" class="alert alert-danger d-none"></div>

                                <!-- 🔍 Search box -->
                                <div class="mb-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                        <input type="text" id="ledgerVoucherSearch" class="form-control" placeholder="Search particulars, voucher no, type...">
                                    </div>
                                </div>

                                <!-- ===== NORMAL table (every field EXCEPT Balance / Balance Due) ===== -->
                                <div class="table-responsive" id="ledgerVoucherNormalWrap" style="max-height: 55vh; overflow-y: auto;">
                                    <table class="table table-bordered table-hover align-middle mb-0" id="ledgerVoucherTable">
                                        <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                            <tr>
                                                <th class="col-date">Date</th>
                                                <th class="col-voucherno">Voucher No.</th>
                                                <th class="col-vouchertype">Voucher Type</th>
                                                <th class="col-particulars">Particulars</th>
                                                <th class="col-debit text-end">Debit</th>
                                                <th class="col-credit text-end">Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ledgerVoucherBody">
                                            <tr><td colspan="6" class="text-center text-muted">No vouchers loaded</td></tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- ===== BALANCE breakdown table (Original / Received-Paid / Pending) ===== -->
                                <div class="table-responsive d-none" id="ledgerVoucherBalanceWrap" style="max-height: 55vh; overflow-y: auto;">
                                    <table class="table table-bordered table-hover align-middle mb-0" id="ledgerBalanceTable">
                                        <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                            <tr>
                                                <th>Date</th>
                                                <th>Voucher No.</th>
                                                <th class="text-end">Original</th>
                                                <th class="text-end" id="ledgerBalanceClearedHeader">Received</th>
                                                <th class="text-end">Pending</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ledgerBalanceBody">
                                            <tr><td colspan="5" class="text-center text-muted">No vouchers loaded</td></tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- ===== BALANCE DUE table (Date / Due Date / Voucher No. / Credit Period / Days / Pending Amount) ===== -->
                                <div class="table-responsive d-none" id="ledgerVoucherDueWrap" style="max-height: 55vh; overflow-y: auto;">
                                    <!-- Save success/error feedback -->
                                    <div id="creditPeriodSaveAlert" class="alert alert-success alert-dismissible fade d-none py-2 px-3 mb-2" role="alert">
                                        <span id="creditPeriodSaveAlertText"></span>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <table class="table table-bordered table-hover align-middle mb-0" id="ledgerDueTable">
                                        <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                            <tr>
                                                <th>Date</th>
                                                <th>Due Date</th>
                                                <th>Voucher No.</th>
                                                <th class="text-center">Credit Period</th>
                                                <th class="text-center">Days</th>
                                                <th class="text-end" id="ledgerDueAmountHeader">Pending Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ledgerDueBody">
                                            <tr><td colspan="6" class="text-center text-muted">No vouchers loaded</td></tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="5" class="text-end">Total Due</th>
                                                <th class="text-end" id="ledgerDueTotal">-</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <a href="javascript:void(0);" id="ledgerVoucherViewAllLink" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-external-link-alt me-1"></i> View Full Ledger Vouchers
                                </a>
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Follow Up Modal (common, one per page) -->
                <div class="modal fade" id="followupTypeModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select Follow Up Type</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="followupCompany">
                                <input type="hidden" id="followupLedger" value="">
                                <input type="hidden" id="followupUnder" value="">
                                <div class="mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="followupType" id="type1" value="Follow Up-Balances/%Targets">
                                        <label class="form-check-label" for="type1">Follow Up-Balances/%Targets</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="followupType" id="type2" value="Follow Up-Balances/Targets(months)">
                                        <label class="form-check-label" for="type2">Follow Up-Balances/Targets(months)</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="followupType" id="type3" value="Follow Up-Balances/Days(agewise)">
                                        <label class="form-check-label" for="type3">Follow Up-Balances/Days(agewise)</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="followupType" id="type4" value="Follow Up-Balances/Days(10-20-30)">
                                        <label class="form-check-label" for="type4">Follow Up-Balances/Days(10-20-30)</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="followupType" id="type5" value="Follow Up-Due">
                                        <label class="form-check-label" for="type5">Follow Up-Due</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="followupType" id="type6" value="Follow Up-Not Due">
                                        <label class="form-check-label" for="type6">Follow Up-Not Due</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary btn-sm" id="followupGoBtn">Proceed</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bad Debt Assign Confirmation Modal -->
                <div class="modal fade" id="confirmBadDebtModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2">
                                    You are about to assign <strong><span id="confirmBadDebtCount">0</span> ledger(s)</strong>
                                    to <strong>Bad Debts / Family A/c</strong>.
                                </p>
                                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Ledger</th>
                                                <th>Under</th>
                                            </tr>
                                        </thead>
                                        <tbody id="confirmBadDebtList"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger btn-sm" id="confirmBadDebtProceedBtn">
                                    Yes, Assign
                                </button>
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
                                    {{-- <span class="badge bg-primary">
                                        {{ count($ledgers) }}
                                    </span> --}}
                                    <button type="button"
                                        class="btn btn-outline-danger btn-sm bad-debt-toggle-btn"
                                        data-type="debtor">
                                        <i class="fa fa-user-times me-1"></i>
                                        <span class="toggle-text">Mark Bad Debts (Debtors)</span>
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-danger btn-sm bad-debt-toggle-btn"
                                        data-type="creditor">
                                        <i class="fa fa-user-times me-1"></i>
                                        <span class="toggle-text">Mark Bad Debts (Creditors)</span>
                                    </button>
                                    <button type="button"
                                        class="btn btn-dark btn-sm openFollowupModal"
                                        data-bs-toggle="modal"
                                        data-bs-target="#followupTypeModal"
                                        data-company="{{ $company }}">
                                        Follow Up
                                    </button>
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

                                        <!-- Debtor Filters -->
                                        <div class="ledger-filter-bar d-flex flex-nowrap align-items-center gap-2 mb-3" data-scope="debtor">
                                            <strong class="me-1">Filter:</strong>

                                            <div class="input-group input-group-sm filter-search-wrap">
                                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                                <input type="text" class="form-control filter-search" placeholder="Search name / mobile...">
                                            </div>

                                            <select class="form-select form-select-sm filter-status">
                                                <option value="">All Status</option>
                                                <option value="success">🟢 Marked - Good Standing</option>
                                                <option value="danger">🔴 Marked - Issue</option>
                                                <option value="secondary">⚪ Unmarked</option>
                                            </select>

                                            <select class="form-select form-select-sm filter-rank">
                                                <option value="">All Ranks</option>
                                                <option value="A">Rank A - Excellent</option>
                                                <option value="B">Rank B - Good</option>
                                                <option value="C">Rank C - Average</option>
                                                <option value="D">Rank D - Poor</option>
                                            </select>

                                            <select class="form-select form-select-sm filter-overlimit">
                                                <option value="">All (Overlimit)</option>
                                                <option value="1">🟣 Overlimit Only</option>
                                            </select>

                                            <button type="button" class="btn btn-sm btn-outline-secondary filter-reset-btn">
                                                <i class="fa fa-times me-1"></i>Reset
                                            </button>

                                            <span class="text-muted small ms-auto filter-count-text"></span>
                                        </div>

                                        <div class="accordion" id="debtorAccordion">
                                            @forelse($debtorLedgers as $index => $l)
                                                @php
                                                    $uid = 'debtor-' . $index;
                                                    $rows = $l['rows'];
                                                    $dotColor = $statusColorMap[$l['status'] ?? 'secondary'] ?? '#adb5bd';
                                                    $dotLabel = $statusLabelMap[$l['status'] ?? 'secondary'] ?? 'Unmarked';
                                                    $rankColor = $rankColorMap[$l['rank'] ?? 'D'] ?? '#adb5bd';
                                                    $rankLabel = $rankLabelMap[$l['rank'] ?? 'D'] ?? 'Unranked';
                                                @endphp

                                                <div class="accordion-item mb-3 border rounded shadow-sm"
                                                    data-status="{{ $l['status'] ?? 'secondary' }}"
                                                    data-rank="{{ $l['rank'] ?? 'D' }}"
                                                    data-overlimit="{{ !empty($l['overlimit']) ? 1 : 0 }}"
                                                    data-name="{{ strtolower($l['name']) }}"
                                                    data-mobile="{{ $l['mobile'] ?? '' }}">
                                                    <h2 class="accordion-header" id="heading-{{ $uid }}">
                                                        <div class="d-flex align-items-center w-100">
                                                            <span class="ledger-check-wrap bad-debt-check-wrap d-none" data-type="debtor">
                                                                <input type="checkbox" class="bad-debt-checkbox" data-type="debtor"
                                                                    data-ledger="{{ $l['name'] }}"
                                                                    data-under="{{ $l['under'] ?? '' }}">
                                                            </span>
                                                            <button class="accordion-button collapsed fw-bold js-accordion-btn"
                                                                type="button"
                                                                data-bs-target="#collapse-{{ $uid }}"
                                                                aria-expanded="false"
                                                                aria-controls="collapse-{{ $uid }}">
                                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                                        <span class="text-muted small me-1">#{{ $l['unique_id'] ?? '-' }}</span>
                                                                        {{ $l['name'] }}
                                                                        @if(!empty($l['mobile']))
                                                                            <span class="ledger-mobile">({{ $l['mobile'] }})</span>
                                                                        @endif
                                                                        <span class="status-dot" style="background-color: {{ $dotColor }};" title="{{ $dotLabel }}"></span>
                                                                        @if(!empty($l['overlimit']))
                                                                            <span class="status-dot" style="background-color: {{ $overlimitColor }};" title="{{ $overlimitLabel }}"></span>
                                                                        @endif
                                                                        <span class="rank-badge" style="background-color: {{ $rankColor }};" title="{{ $rankLabel }}">
                                                                            {{ $l['rank'] ?? '-' }}
                                                                        </span>
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
                                                                                <tr class="ledger-field-row {{ $rowClassMap[$label] ?? '' }}"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#ledgerVoucherModal"
                                                                                    onclick="showLedgerVoucherDetail('{{ addslashes($l['name']) }}', '{{ addslashes($l['under'] ?? '') }}', '{{ addslashes($label) }}')">
                                                                                    <th width="40%">{{ $label }}</th>
                                                                                    <td>
                                                                                        <span class="ledger-field-value">
                                                                                            <strong>₹ {{ $fmt($value) }}</strong>
                                                                                            <i class="fa fa-file-invoice"></i>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <div class="d-flex justify-content-end gap-2">
                                                                    <a target="_blank" href="{{ route('owner.tally.ledger.vouchers', [
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

                                        <!-- Creditor Filters -->
                                        <div class="ledger-filter-bar d-flex flex-nowrap align-items-center gap-2 mb-3" data-scope="creditor">
                                            <strong class="me-1">Filter:</strong>

                                            <div class="input-group input-group-sm filter-search-wrap">
                                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                                <input type="text" class="form-control filter-search" placeholder="Search name / mobile...">
                                            </div>

                                            <select class="form-select form-select-sm filter-status">
                                                <option value="">All Status</option>
                                                <option value="success">🟢 Marked - Good Standing</option>
                                                <option value="danger">🔴 Marked - Issue</option>
                                                <option value="secondary">⚪ Unmarked</option>
                                            </select>

                                            <select class="form-select form-select-sm filter-rank">
                                                <option value="">All Ranks</option>
                                                <option value="A">Rank A - Excellent</option>
                                                <option value="B">Rank B - Good</option>
                                                <option value="C">Rank C - Average</option>
                                                <option value="D">Rank D - Poor</option>
                                            </select>

                                            <select class="form-select form-select-sm filter-overlimit">
                                                <option value="">All (Overlimit)</option>
                                                <option value="1">🟣 Overlimit Only</option>
                                            </select>

                                            <button type="button" class="btn btn-sm btn-outline-secondary filter-reset-btn">
                                                <i class="fa fa-times me-1"></i>Reset
                                            </button>

                                            <span class="text-muted small ms-auto filter-count-text"></span>
                                        </div>

                                        <div class="accordion" id="creditorAccordion">
                                            @forelse($creditorLedgers as $index => $l)
                                                @php
                                                    $uid = 'creditor-' . $index;
                                                    $rows = $l['rows'];
                                                    $dotColor = $statusColorMap[$l['status'] ?? 'secondary'] ?? '#adb5bd';
                                                    $dotLabel = $statusLabelMap[$l['status'] ?? 'secondary'] ?? 'Unmarked';
                                                    $rankColor = $rankColorMap[$l['rank'] ?? 'D'] ?? '#adb5bd';
                                                    $rankLabel = $rankLabelMap[$l['rank'] ?? 'D'] ?? 'Unranked';
                                                @endphp
                                                <div class="accordion-item mb-3 border rounded shadow-sm"
                                                    data-status="{{ $l['status'] ?? 'secondary' }}"
                                                    data-rank="{{ $l['rank'] ?? 'D' }}"
                                                    data-overlimit="{{ !empty($l['overlimit']) ? 1 : 0 }}"
                                                    data-name="{{ strtolower($l['name']) }}"
                                                    data-mobile="{{ $l['mobile'] ?? '' }}">
                                                    <h2 class="accordion-header" id="heading-{{ $uid }}">
                                                        <div class="d-flex align-items-center w-100">
                                                            <span class="ledger-check-wrap bad-debt-check-wrap d-none" data-type="creditor">
                                                                <input type="checkbox" class="bad-debt-checkbox" data-type="creditor"
                                                                    data-ledger="{{ $l['name'] }}"
                                                                    data-under="{{ $l['under'] ?? '' }}">
                                                            </span>
                                                            <button class="accordion-button collapsed fw-bold js-accordion-btn"
                                                                type="button"
                                                                data-bs-target="#collapse-{{ $uid }}"
                                                                aria-expanded="false"
                                                                aria-controls="collapse-{{ $uid }}">
                                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                                        <span class="text-muted small me-1">#{{ $l['unique_id'] ?? '-' }}</span>
                                                                        {{ $l['name'] }}
                                                                        @if(!empty($l['mobile']))
                                                                            <span class="ledger-mobile">({{ $l['mobile'] }})</span>
                                                                        @endif
                                                                        <span class="status-dot" style="background-color: {{ $dotColor }};" title="{{ $dotLabel }}"></span>
                                                                        @if(!empty($l['overlimit']))
                                                                            <span class="status-dot" style="background-color: {{ $overlimitColor }};" title="{{ $overlimitLabel }}"></span>
                                                                        @endif
                                                                        <span class="rank-badge" style="background-color: {{ $rankColor }};" title="{{ $rankLabel }}">
                                                                            {{ $l['rank'] ?? '-' }}
                                                                        </span>
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
                                                                                <tr class="ledger-field-row {{ $rowClassMap[$label] ?? '' }}"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#ledgerVoucherModal"
                                                                                    onclick="showLedgerVoucherDetail('{{ addslashes($l['name']) }}', '{{ addslashes($l['under'] ?? '') }}', '{{ addslashes($label) }}')">
                                                                                    <th width="40%">{{ $label }}</th>
                                                                                    <td>
                                                                                        <span class="ledger-field-value">
                                                                                            <strong>₹ {{ $fmt($value) }}</strong>
                                                                                            <i class="fa fa-file-invoice"></i>
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <div class="d-flex justify-content-end gap-2">
                                                                    <a href="{{ route('owner.tally.ledger.vouchers', [
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

        <!-- Floating Assign Bar - Debtors -->
        <div id="assignBadDebtBarDebtor" class="assign-bad-debt-bar d-none">
            <button type="button" class="btn btn-danger shadow assign-bad-debt-btn" data-type="debtor">
                <i class="fa fa-check me-1"></i>
                Assign Selected Debtors (<span class="bad-debt-selected-count">0</span>) to Bad Debts / Family A/c
            </button>
        </div>

        <!-- Floating Assign Bar - Creditors -->
        <div id="assignBadDebtBarCreditor" class="assign-bad-debt-bar d-none">
            <button type="button" class="btn btn-danger shadow assign-bad-debt-btn" data-type="creditor">
                <i class="fa fa-check me-1"></i>
                Assign Selected Creditors (<span class="bad-debt-selected-count">0</span>) to Bad Debts / Family A/c
            </button>
        </div>

        @include('owner.tally.components.footer')

        <script>
            const breakdownDataDebtor   = @json($breakdownDebtor);
            const breakdownDataCreditor = @json($breakdownCreditor);
            const currentCompanyName    = @json($company);

            function showLedgerBreakdown(label, type) {
                let dataset = (type === 'creditor') ? breakdownDataCreditor : breakdownDataDebtor;
                let items = dataset[label] || [];
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
                        <td><strong>₹ ${formattedValue}</strong></td>
                    </tr>`;
                });

                if (items.length === 0) {
                    html = '<tr><td colspan="3" class="text-center text-muted">No data available</td></tr>';
                }

                let formattedTotal = new Intl.NumberFormat('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(total);

                let typeLabel = (type === 'creditor') ? 'Sundry Creditors' : 'Sundry Debtors';
                $('#ledgerBreakdownTitle').text(label + ' Breakdown (' + typeLabel + ')');
                $('#ledgerBreakdownBody').html(html);
                $('#ledgerBreakdownTotal').text('₹ ' + formattedTotal);
            }

            // ===== Per-ledger, per-field (Balance / Balance Due / Sale / ...) voucher modal =====
            //
            // "Balance"     -> #ledgerBalanceTable (Original / Received-Paid / Pending)
            // "Balance Due" -> #ledgerDueTable     (Date / Due Date / Voucher No. / Credit Period / Days / Pending Amount)
            // baaki fields  -> #ledgerVoucherTable (normal debit/credit voucher list)
            function showLedgerVoucherDetail(ledger, under, field) {
                let isBalance = (field === 'Balance');
                let isDue     = (field === 'Balance Due');
                let isCreditor = (under === 'Sundry Creditors');

                $('#ledgerVoucherModalTitle').text(
                    ledger + ' — ' + field + (isBalance ? ' Breakdown' : (isDue ? ' (Overdue Vouchers)' : ' Vouchers'))
                );
                $('#ledgerVoucherError').addClass('d-none').text('');
                $('#ledgerVoucherLoading').removeClass('d-none');

                // Kaunsi table dikhani hai
                $('#ledgerVoucherNormalWrap').toggleClass('d-none', isBalance || isDue);
                $('#ledgerVoucherBalanceWrap').toggleClass('d-none', !isBalance);
                $('#ledgerVoucherDueWrap').toggleClass('d-none', !isDue);

                // Sab bodies reset
                $('#ledgerVoucherBody').html('<tr><td colspan="6" class="text-center text-muted">No vouchers loaded</td></tr>');
                $('#ledgerBalanceBody').html('<tr><td colspan="5" class="text-center text-muted">No vouchers loaded</td></tr>');
                $('#ledgerDueBody').html('<tr><td colspan="6" class="text-center text-muted">No vouchers loaded</td></tr>');
                $('#ledgerDueTotal').text('-');

                // Search box reset (purani search naye field pe carry na ho)
                $('#ledgerVoucherSearch').val('');

                // "Received" for Sundry Debtors, "Paid" for Sundry Creditors (Balance table header)
                $('#ledgerBalanceClearedHeader').text(isCreditor ? 'Paid' : 'Received');
                $('#ledgerDueAmountHeader').text('Pending Amount');

                // ===== Normal table ke column rules =====
                let hideVoucherNo = (field === 'Receipts');
                let hideDebit     = (field === 'Receipts');
                let hideCredit    = (field === 'Sale');

                $('#ledgerVoucherTable .col-voucherno').toggle(!hideVoucherNo);
                $('#ledgerVoucherTable .col-debit').toggle(!hideDebit);
                $('#ledgerVoucherTable .col-credit').toggle(!hideCredit);

                let baseUrl = "{{ route('owner.tally.ledger.vouchers', ['company' => ':company', 'ledger' => ':ledger', 'under' => ':under']) }}";
                let fullPageUrl = baseUrl
                    .replace(':company', encodeURIComponent(currentCompanyName))
                    .replace(':ledger', encodeURIComponent(ledger))
                    .replace(':under', encodeURIComponent(under));
                $('#ledgerVoucherViewAllLink').attr('href', fullPageUrl);

                let fieldAjaxBaseUrl = "{{ route('owner.tally.ledger.field-vouchers', ['company' => ':company', 'ledger' => ':ledger', 'under' => ':under']) }}";
                let fieldAjaxUrl = fieldAjaxBaseUrl
                    .replace(':company', encodeURIComponent(currentCompanyName))
                    .replace(':ledger', encodeURIComponent(ledger))
                    .replace(':under', encodeURIComponent(under));

                let dueAjaxUrl = "{{ route('owner.tally.ledger.due-vouchers') }}";

                let fmtAmt = function (n) {
                    return new Intl.NumberFormat('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(n || 0);
                };

                // date ko "16 March 2024" format me dikhane ke liye
                let fmtDate = function (d) {
                    if (!d) return '-';
                    let dateObj = new Date(d);
                    if (isNaN(dateObj.getTime())) return d;
                    return dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                };

                $.ajax({
                    url: isDue ? dueAjaxUrl : fieldAjaxUrl,
                    method: "GET",
                    data: isDue
                        ? { company: currentCompanyName, ledger: ledger, under: under }
                        : { field: field },
                    dataType: "json",
                    success: function (res) {
                        $('#ledgerVoucherLoading').addClass('d-none');

                        let vouchers = (res && res.vouchers) ? res.vouchers : (Array.isArray(res) ? res : []);

                        if (!vouchers.length) {
                            if (isDue) {
                                $('#ledgerDueBody').html('<tr><td colspan="6" class="text-center text-muted">No overdue vouchers found 🎉</td></tr>');
                                $('#ledgerDueTotal').text('₹ 0.00');
                            } else if (isBalance) {
                                $('#ledgerBalanceBody').html('<tr><td colspan="5" class="text-center text-muted">No pending invoices found — all clear! 🎉</td></tr>');
                            } else {
                                let colspan = 6 - (hideVoucherNo ? 1 : 0) - (hideDebit ? 1 : 0) - (hideCredit ? 1 : 0);
                                $('#ledgerVoucherBody').html('<tr><td colspan="' + colspan + '" class="text-center text-muted">No vouchers found for this field.</td></tr>');
                            }
                            return;
                        }

                        // ===== BALANCE DUE =====
                                                // ===== BALANCE DUE =====
                        if (isDue) {
                            let rows = '';
                            let total = 0;
                            let ledgerFallbackCP = res.ledger_credit_period ?? 0;
                            vouchers.forEach(function (v) {
                                total += parseFloat(v.amount) || 0;
                                let partial = (parseFloat(v.original) || 0) > (parseFloat(v.amount) || 0) + 0.009
                                    ? `<div class="text-muted small">of ₹ ${fmtAmt(v.original)}</div>`
                                    : '';
                                let cpValue = (v.credit_period !== null && v.credit_period !== undefined && v.credit_period !== '')
                                    ? v.credit_period
                                    : ledgerFallbackCP;

                                rows += `<tr data-voucher-id="${v.id ?? v.voucher_number ?? ''}">
                                    <td>${fmtDate(v.date)}</td>
                                    <td class="due-date-cell">${fmtDate(v.due_date)}</td>
                                    <td>${v.voucher_number ?? '-'}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <input type="number" min="0"
                                                class="form-control form-control-sm credit-period-input"
                                                value="${cpValue}"
                                                data-voucher-id="${v.id ?? ''}"
                                                data-voucher-number="${v.voucher_number ?? ''}"
                                                data-ledger="${ledger}"
                                                data-under="${under}">
                                            <span class="text-muted small">Days</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary credit-period-save-btn" title="Save">
                                                <i class="fa fa-save"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-danger days-badge">${v.days} days</span></td>
                                    <td class="text-end fw-bold">₹ ${fmtAmt(v.amount)}${partial}</td>
                                </tr>`;
                            });
                            $('#ledgerDueBody').html(rows);
                            $('#ledgerDueTotal').text('₹ ' + fmtAmt(res.total ?? total));
                            return;
                        }

                        // ===== BALANCE =====
                        if (isBalance) {
                            let clearedKey = isCreditor ? 'paid' : 'received';
                            let rows = '';
                            vouchers.forEach(function (v) {
                                rows += `<tr>
                                    <td>${fmtDate(v.date)}</td>
                                    <td>${v.voucher_number ?? '-'}</td>
                                    <td class="text-end">₹ ${fmtAmt(v.original)}</td>
                                    <td class="text-end">₹ ${fmtAmt(v[clearedKey])}</td>
                                    <td class="text-end fw-bold ${v.pending > 0 ? 'text-danger' : 'text-success'}">₹ ${fmtAmt(v.pending)}</td>
                                </tr>`;
                            });
                            $('#ledgerBalanceBody').html(rows);
                            return;
                        }

                        // ===== NORMAL fields =====
                        let rows = '';
                        vouchers.forEach(function (v) {
                            rows += `<tr>
                                <td class="col-date">${fmtDate(v.date)}</td>
                                <td class="col-voucherno" style="${hideVoucherNo ? 'display:none;' : ''}">${v.voucher_no ?? '-'}</td>
                                <td class="col-vouchertype">${v.voucher_type ?? '-'}</td>
                                <td class="col-particulars">${v.particulars ?? '-'}</td>
                                <td class="col-debit text-end" style="${hideDebit ? 'display:none;' : ''}">${v.debit ? '₹ ' + fmtAmt(v.debit) : ''}</td>
                                <td class="col-credit text-end" style="${hideCredit ? 'display:none;' : ''}">${v.credit ? '₹ ' + fmtAmt(v.credit) : ''}</td>
                            </tr>`;
                        });
                        $('#ledgerVoucherBody').html(rows);
                    },
                    error: function () {
                        $('#ledgerVoucherLoading').addClass('d-none');
                        $('#ledgerVoucherError')
                            .removeClass('d-none')
                            .text('Could not load vouchers for this field. Please try "View Full Ledger Vouchers" instead.');
                    }
                });
            }

            // ===== Balance Due modal: per-voucher editable Credit Period -> Save button se update =====
            $(document).on('click', '.credit-period-save-btn', function () {
                let $btn   = $(this);
                let $input = $btn.closest('td').find('.credit-period-input');
                let $row   = $input.closest('tr');
                let newValue = $input.val();

                if (newValue === '' || isNaN(newValue) || newValue < 0) {
                    alert('Please enter a valid credit period (in days).');
                    return;
                }

                $input.prop('disabled', true);
                $btn.prop('disabled', true);
                let originalIcon = $btn.html();
                $btn.html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: "{{ route('owner.tally.ledger.update-credit-period') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        company: currentCompanyName,
                        ledger: $input.data('ledger'),
                        under: $input.data('under'),
                        voucher_id: $input.data('voucher-id'),
                        voucher_number: $input.data('voucher-number'),
                        credit_period: newValue
                    },
                    dataType: "json",
                    success: function (res) {
                        $input.prop('disabled', false);
                        $btn.prop('disabled', false);
                        $btn.html(originalIcon);

                        if (res && res.days !== undefined) {
                            $row.find('.days-badge').text(res.days + ' days');
                        }
                        if (res && res.due_date) {
                            let dateObj = new Date(res.due_date);
                            let formatted = isNaN(dateObj.getTime())
                                ? res.due_date
                                : dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                            $row.find('.due-date-cell').text(formatted);
                        }

                        showCreditPeriodAlert('success', 'Credit period updated successfully.');

                        // Row ko bhi ek pal ke liye highlight kar do, visual confirmation ke liye
                        $row.addClass('table-success');
                        setTimeout(() => $row.removeClass('table-success'), 1200);
                    },
                    error: function () {
                        $input.prop('disabled', false);
                        $btn.prop('disabled', false);
                        $btn.html(originalIcon);
                        showCreditPeriodAlert('danger', 'Credit period update nahi ho paya. Dobara try karein.');
                    }
                });
            });

            // Reusable alert helper — modal ke andar hi auto show/hide hota hai
            let creditPeriodAlertTimer = null;
            function showCreditPeriodAlert(type, message) {
                let $alert = $('#creditPeriodSaveAlert');
                clearTimeout(creditPeriodAlertTimer);

                $alert
                    .removeClass('alert-success alert-danger d-none')
                    .addClass('alert-' + type + ' show')
                    .find('#creditPeriodSaveAlertText').text(message);

                $alert.removeClass('d-none');

                creditPeriodAlertTimer = setTimeout(function () {
                    $alert.removeClass('show').addClass('d-none');
                }, 2500);
            }

            // ===== Tab-wise Summary Cards =====
            let summaryVisible = false;

            function getActiveLedgerScope() {
                return $('#creditors-tab').hasClass('active') ? 'creditor' : 'debtor';
            }

            function showSummaryForActiveTab() {
                let scope = getActiveLedgerScope();
                $('.summary-cards-section').hide();
                if (scope === 'creditor') {
                    $('#summaryCardsSectionCreditor').show();
                } else {
                    $('#summaryCardsSectionDebtor').show();
                }
            }

            $('#summaryToggleBtn').on('click', function () {
                let $btnText = $(this).find('.toggle-text');

                summaryVisible = !summaryVisible;

                if (summaryVisible) {
                    showSummaryForActiveTab();
                    $btnText.text('Hide Summary');
                } else {
                    $('.summary-cards-section').hide();
                    $btnText.text('Show Summary');
                }
            });

            $('#ledgerTabs button[data-bs-toggle="pill"]').on('shown.bs.tab', function () {
                if (summaryVisible) {
                    showSummaryForActiveTab();
                }
            });

            $(document).on("click", ".openFollowupModal", function () {
                $("#followupCompany").val($(this).data("company"));
                $("#followupLedger").val('');
                $("#followupUnder").val('');
            });

            $("#followupGoBtn").click(function () {
                let rawCompany = $("#followupCompany").val();
                let ledger     = encodeURIComponent($("#followupLedger").val());
                let under      = encodeURIComponent($("#followupUnder").val());
                let type       = $('input[name="followupType"]:checked').val();

                if (!type) {
                    alert("Please select Follow Up Type");
                    return;
                }

                let baseUrl = "{{ route('owner.tally.followup.hub', ['company' => ':company']) }}";
                let url = baseUrl.replace(':company', encodeURIComponent(rawCompany));

                url += "?ledger=" + ledger
                    + "&under=" + under
                    + "&type=" + encodeURIComponent(type);

                window.open(url, "_blank");
            });

            // ===== Bad Debts / Family A/c assignment feature (separate for Debtors & Creditors) =====

            $('.bad-debt-toggle-btn').on('click', function () {
                let $btn = $(this);
                let type = $btn.data('type');
                let $wraps = $('.bad-debt-check-wrap[data-type="' + type + '"]');
                let willTurnOn = $wraps.first().hasClass('d-none');

                $wraps.toggleClass('d-none', !willTurnOn);

                let label = type === 'debtor' ? 'Mark Bad Debts (Debtors)' : 'Mark Bad Debts (Creditors)';
                $btn.find('.toggle-text').text(willTurnOn ? 'Cancel Selection' : label);
                $btn.toggleClass('btn-outline-danger', !willTurnOn).toggleClass('btn-danger', willTurnOn);

                let $bar = type === 'debtor' ? $('#assignBadDebtBarDebtor') : $('#assignBadDebtBarCreditor');
                $bar.toggleClass('d-none', !willTurnOn);

                if (!willTurnOn) {
                    $('.bad-debt-checkbox[data-type="' + type + '"]').prop('checked', false);
                    $bar.find('.bad-debt-selected-count').text(0);
                }
            });

            $(document).on('change', '.bad-debt-checkbox', function () {
                let type = $(this).data('type');
                let count = $('.bad-debt-checkbox[data-type="' + type + '"]:checked').length;
                let $bar = type === 'debtor' ? $('#assignBadDebtBarDebtor') : $('#assignBadDebtBarCreditor');
                $bar.find('.bad-debt-selected-count').text(count);
            });

            let pendingBadDebtSelection = [];
            let pendingBadDebtType = null;

            $('.assign-bad-debt-btn').on('click', function () {
                let type = $(this).data('type');
                let selected = [];
                $('.bad-debt-checkbox[data-type="' + type + '"]:checked').each(function () {
                    selected.push({
                        ledger: $(this).attr('data-ledger'),   // .attr() taaki naam string hi rahe (jQuery .data() type convert kar deta hai)
                        under: $(this).attr('data-under')
                    });
                });

                if (selected.length === 0) {
                    alert('Please select at least one ledger.');
                    return;
                }

                pendingBadDebtSelection = selected;
                pendingBadDebtType = type;

                $('#confirmBadDebtCount').text(selected.length);
                let rows = '';
                selected.forEach(function (item, i) {
                    rows += `<tr>
                        <td>${i + 1}</td>
                        <td>${$('<div>').text(item.ledger).html()}</td>
                        <td>${$('<div>').text(item.under).html()}</td>
                    </tr>`;
                });
                $('#confirmBadDebtList').html(rows);

                let confirmModal = new bootstrap.Modal(document.getElementById('confirmBadDebtModal'));
                confirmModal.show();
            });

            $('#confirmBadDebtProceedBtn').on('click', function () {
                if (pendingBadDebtSelection.length === 0) {
                    return;
                }

                let $btn = $(this);
                $btn.prop('disabled', true).text('Assigning...');

                $.ajax({
                    url: "{{ route('owner.tally.baddebts.assign', ['company' => $company]) }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        company: @json($company),
                        type: pendingBadDebtType,
                        ledgers: pendingBadDebtSelection
                    },
                    success: function (res) {
                        let confirmModalEl = document.getElementById('confirmBadDebtModal');
                        bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
                        alert('The selected ledger(s) have been successfully assigned to Bad Debts / Family A/c.');
                        location.reload();
                    },
                    error: function () {
                        alert('Something went wrong. Please try again.');
                        $btn.prop('disabled', false).text('Yes, Assign');
                    }
                });
            });

            // ===== Search + Status / Rank / Overlimit Filters (per tab, scoped independently) =====

            function applyLedgerFilters(scope) {
                let $bar = $('.ledger-filter-bar[data-scope="' + scope + '"]');
                let searchVal    = ($bar.find('.filter-search').val() || '').toLowerCase().trim();
                let statusVal    = $bar.find('.filter-status').val();
                let rankVal      = $bar.find('.filter-rank').val();
                let overlimitVal = $bar.find('.filter-overlimit').val();

                let $accordion = scope === 'debtor' ? $('#debtorAccordion') : $('#creditorAccordion');
                let $items = $accordion.find('.accordion-item');
                let total = $items.length;
                let visibleCount = 0;

                $items.each(function () {
                    let $item = $(this);
                    let matches = true;

                    if (searchVal) {
                        let name = String($item.data('name') || '').toLowerCase();
                        let mobile = String($item.data('mobile') || '').toLowerCase();
                        if (name.indexOf(searchVal) === -1 && mobile.indexOf(searchVal) === -1) {
                            matches = false;
                        }
                    }
                    if (matches && statusVal && String($item.data('status')) !== statusVal) {
                        matches = false;
                    }
                    if (matches && rankVal && String($item.data('rank')) !== rankVal) {
                        matches = false;
                    }
                    if (matches && overlimitVal && String($item.data('overlimit')) !== overlimitVal) {
                        matches = false;
                    }

                    $item.toggle(matches);
                    if (matches) visibleCount++;
                });

                $bar.find('.filter-count-text').text(
                    total > 0 ? ('Showing ' + visibleCount + ' of ' + total) : ''
                );

                let $emptyMsg = $accordion.find('.filter-empty-msg');
                if (total > 0 && visibleCount === 0) {
                    if ($emptyMsg.length === 0) {
                        $accordion.append('<p class="text-center text-muted filter-empty-msg">No ledgers match the selected filters.</p>');
                    }
                } else {
                    $emptyMsg.remove();
                }
            }

            $(document).on('change', '.ledger-filter-bar select', function () {
                let scope = $(this).closest('.ledger-filter-bar').data('scope');
                applyLedgerFilters(scope);
            });

            $(document).on('input', '.ledger-filter-bar .filter-search', function () {
                let scope = $(this).closest('.ledger-filter-bar').data('scope');
                applyLedgerFilters(scope);
            });

            $(document).on('click', '.filter-reset-btn', function () {
                let $bar = $(this).closest('.ledger-filter-bar');
                $bar.find('select').val('');
                $bar.find('.filter-search').val('');
                applyLedgerFilters($bar.data('scope'));
            });

            // ===== Manual, single-source accordion open/close =====
            $(document).off('click.ledgerAccordion').on('click.ledgerAccordion', '.js-accordion-btn', function (e) {
                e.preventDefault();
                e.stopPropagation();

                let $btn = $(this);
                let targetSel = $btn.attr('data-bs-target');
                if (!targetSel) return;

                let $target = $(targetSel);
                if ($target.length === 0) return;

                let $accordionParent = $target.closest('.accordion');
                let isCurrentlyOpen = $target.hasClass('show');

                $accordionParent.find('.accordion-collapse.show').not($target).each(function () {
                    let otherInstance = bootstrap.Collapse.getOrCreateInstance(this, { toggle: false });
                    otherInstance.hide();
                    $(this).closest('.accordion-item')
                        .find('.js-accordion-btn')
                        .addClass('collapsed')
                        .attr('aria-expanded', 'false');
                });

                let instance = bootstrap.Collapse.getOrCreateInstance($target[0], { toggle: false });

                if (isCurrentlyOpen) {
                    instance.hide();
                    $btn.addClass('collapsed').attr('aria-expanded', 'false');
                } else {
                    instance.show();
                    $btn.removeClass('collapsed').attr('aria-expanded', 'true');
                }
            });

            // ===== Search inside modals (Breakdown + Voucher) =====

            function filterModalTableRows(inputId, tbodyId) {
                let val = ($('#' + inputId).val() || '').toLowerCase().trim();
                let $rows = $('#' + tbodyId + ' tr');
                let visibleCount = 0;

                $rows.each(function () {
                    let $row = $(this);
                    if ($row.hasClass('no-match-row') || $row.find('td[colspan]').length > 0) {
                        return;
                    }
                    let text = $row.text().toLowerCase();
                    let matches = val === '' || text.indexOf(val) !== -1;
                    $row.toggle(matches);
                    if (matches) visibleCount++;
                });

                let $tbody = $('#' + tbodyId);
                let $noMatch = $tbody.find('.no-match-row');
                if (visibleCount === 0 && val !== '') {
                    if ($noMatch.length === 0) {
                        let colspan = $tbody.closest('table').find('thead th').length || 3;
                        $tbody.append('<tr class="no-match-row"><td colspan="' + colspan + '" class="text-center text-muted">No matching results.</td></tr>');
                    }
                } else {
                    $noMatch.remove();
                }
            }

            // Live search - Breakdown Modal
            $(document).on('input', '#ledgerBreakdownSearch', function () {
                filterModalTableRows('ledgerBreakdownSearch', 'ledgerBreakdownBody');
            });

            // Live search - Voucher Modal (Normal / Balance / Balance Due, jo table visible ho usi me)
            $(document).on('input', '#ledgerVoucherSearch', function () {
                let tbodyId = 'ledgerVoucherBody';
                if (!$('#ledgerVoucherBalanceWrap').hasClass('d-none')) {
                    tbodyId = 'ledgerBalanceBody';
                } else if (!$('#ledgerVoucherDueWrap').hasClass('d-none')) {
                    tbodyId = 'ledgerDueBody';
                }
                filterModalTableRows('ledgerVoucherSearch', tbodyId);
            });

            // Modal band hone par search box reset
            $('#ledgerBreakdownModal').on('hidden.bs.modal', function () {
                $('#ledgerBreakdownSearch').val('');
            });
            $('#ledgerVoucherModal').on('hidden.bs.modal', function () {
                $('#ledgerVoucherSearch').val('');
                $('#creditPeriodSaveAlert').addClass('d-none').removeClass('show alert-success alert-danger');
            });
        </script>
    </div>
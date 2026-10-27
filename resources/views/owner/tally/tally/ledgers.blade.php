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

                    /* Credit Period editable input inside Balance Overdue table */
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

                    /* ===== Debtor Ranking table ===== */
                    .ranking-table thead th {
                        position: sticky;
                        top: 0;
                        z-index: 1;
                        background: #fff;
                        font-weight: 700;
                        white-space: nowrap;
                    }

                    /* ===== Interest Cost modal ===== */
                    .int-grey { background: #e9ecef !important; }

                    .int-summary-table th,
                    .int-summary-table td { font-size: 13px; white-space: nowrap; padding: 6px 10px; }
                    .int-summary-table thead tr:first-child th { font-weight: 700; }
                    .int-summary-table .int-sum-val.int-highlight { outline: 2px solid #4E3F6B; outline-offset: -2px; }
                </style>

                <div class="container-fluid">
                    @php
                        // ✅ Bina round kiye, DB ke decimals jaise ke taise (sirf Indian comma grouping)
                        $fmt = function ($n) {
                            $s   = (string) $n;
                            $neg = str_starts_with($s, '-');
                            $s   = ltrim($s, '+-');
                            [$i, $d] = array_pad(explode('.', $s, 2), 2, '');
                            if (strlen($i) > 3) {
                                $i = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($i, 0, -3)) . ',' . substr($i, -3);
                            }
                            return ($neg ? '-' : '') . $i . ($d !== '' ? '.' . $d : '');
                        };

                        // ✅ String hi rakho, float cast mat karo (value DB jaisi rahegi)
                        $num = function ($v) {
                            $s = str_replace([',', '₹', ' '], '', (string) ($v ?? 0));
                            return is_numeric($s) ? $s : '0';
                        };

                        // trailing zeros hatane ke liye (totals ke liye)
                        $trimZeros = fn ($s) => str_contains($s, '.') ? rtrim(rtrim($s, '0'), '.') : $s;

                        // target ki value alag-alag key naam se aa sakti hai, jo mile wahi le lo
                        $getTarget = function ($l) use ($num) {
                            foreach (['target', 'target_balance', 'targetBal', 'balance_target'] as $k) {
                                if (isset($l[$k]) && $l[$k] !== '') {
                                    return $num($l[$k]);
                                }
                            }
                            return '0';
                        };

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
                            'A' => 'Rank A',
                            'B' => 'Rank B',
                            'C' => 'Rank C',
                            'D' => 'Rank D',
                        ];

                        $debtorLedgers = collect($ledgers)
                            ->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Debtors')
                            ->values()
                            ->map(function ($l) use ($num, $getTarget) {
                                $rows = [
                                    'Balance'                => $num($l['balance']               ?? 0),
                                    'Balance Overdue'        => $num($l['due']                   ?? 0),
                                    'Balance Target'         => $getTarget($l),
                                    'Sale'                   => $num($l['sale']                  ?? 0),
                                    'Other Debits'           => $num($l['other_debits']          ?? 0),
                                    'Receipts'               => $num($l['receipts']              ?? 0),
                                    'Not Due'                => $num($l['not_due']               ?? 0),
                                    'Interest Cost'          => $num($l['interest_cost']         ?? 0),
                                    'Interest Received'      => $num($l['interest_received']     ?? 0),
                                    'Interest Due'           => $num($l['interest_due']          ?? 0),
                                    'Interest Waived'        => $num($l['interest_waived']       ?? 0),
                                    'Bad Debts / Family A/c' => $num($l['bad_debts']             ?? 0),
                                    'Total Debtors'          => $num($l['total_debtors']         ?? 0),
                                    'March Closing Pending'  => $num($l['march_closing_pending'] ?? 0),
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
                            ->map(function ($l) use ($num, $getTarget) {
                                $rows = [
                                    'Balance'                => $num($l['balance']               ?? 0),
                                    'Balance Overdue'        => $num($l['due']                   ?? 0),
                                    'Balance Target'         => $getTarget($l),
                                    'Purchase'               => $num($l['purchase']              ?? 0),
                                    'Other Credits'          => $num($l['other_credits']         ?? 0),
                                    'Payments'               => $num($l['payments']              ?? 0),
                                    'Not Due'                => $num($l['not_due']               ?? 0),
                                    'Interest Cost'          => $num($l['interest_cost']         ?? 0),
                                    'Interest Paid'          => $num($l['interest_paid']         ?? 0),
                                    'Interest Due'           => $num($l['interest_due']          ?? 0),
                                    'Interest Waived'        => $num($l['interest_waived']       ?? 0),
                                    'Bad Debts / Family A/c' => $num($l['bad_debts']             ?? 0),
                                    'Total Creditors'        => $num($l['total_creditors']       ?? 0),
                                    'March Closing Pending'  => $num($l['march_closing_pending'] ?? 0),
                                ];

                                return $l + [
                                    'rows'      => $rows,
                                    'mobile'    => $l['mobile']    ?? '',
                                    'status'    => $l['status']    ?? 'secondary',
                                    'overlimit' => $l['overlimit'] ?? false,
                                    'rank'      => $l['rank']      ?? null,
                                ];
                            });

                        // ===== Excel wale formulas ke hisaab se Rank / Class (Debtors & Creditors dono ke liye alag) =====
                        // H            = % Target  (Target / (Overdue + Not Due) * 100)
                        // Priority Rank = RANK(H, H-range, 0)  -> descending
                        // Debtor Rank   = RANK(H, H-range, 1)  -> ascending   (column J)
                        // Class         = Debtor Rank / COUNTA(J) * 100  ->  <=15 A, <=50 B, <=85 C, baaki D
                        $applyRanks = function ($list) use ($num) {
                            $list = $list->map(function ($l) use ($num) {
                                $overdue    = $l['rows']['Balance Overdue'];
                                $notDue     = $l['rows']['Not Due'];
                                $collection = $num($l['target_collection'] ?? 0);
                                $total      = bcadd($overdue, $notDue, 2);

                                $l['has_due']    = bccomp($total, '0', 2) > 0;
                                $l['pct_target'] = $l['has_due'] ? round($collection / $total * 100) : null;
                                return $l;
                            });

                            // sirf active debtors ka pool
                            $pcts  = $list->filter(fn ($l) => $l['has_due'])->pluck('pct_target');
                            $count = $pcts->count();

                            return $list->map(function ($l) use ($pcts, $count) {
                                if (!$l['has_due']) {
                                    $l['priority_rank'] = null;
                                    $l['debtor_rank']   = null;
                                    $l['rank']          = null;
                                    return $l;
                                }

                                $p = $l['pct_target'];
                                $l['priority_rank'] = $pcts->filter(fn ($x) => $x > $p)->count() + 1;
                                $l['debtor_rank']   = $pcts->filter(fn ($x) => $x < $p)->count() + 1;

                                $share = $count > 0 ? ($l['debtor_rank'] / $count) * 100 : 0;
                                $l['rank'] = $share <= 15 ? 'A' : ($share <= 50 ? 'B' : ($share <= 85 ? 'C' : 'D'));

                                return $l;
                            })->values();
                        };

                        $debtorLedgers   = $applyRanks($debtorLedgers);
                        $creditorLedgers = $applyRanks($creditorLedgers);

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

                        // ✅ float + ki jagah bcadd (exact sum)
                        $computeTotals = function ($ledgerList) use ($mergeMap, $num, $trimZeros) {
                            $t = [];
                            foreach ($ledgerList as $l) {
                                foreach ($l['rows'] as $label => $value) {
                                    $key = $mergeMap[$label] ?? $label;
                                    $t[$key] = bcadd($t[$key] ?? '0', $num($value), 10);
                                }
                            }
                            return array_map($trimZeros, $t);
                        };

                        $computeBreakdown = function ($ledgerList) use ($mergeMap, $num) {
                            $b = [];
                            foreach ($ledgerList as $l) {
                                foreach ($l['rows'] as $label => $value) {
                                    $key = $mergeMap[$label] ?? $label;
                                    $b[$key][] = [
                                        'ledger' => $l['name'],
                                        'under'  => $l['under'] ?? '',
                                        'label'  => $label,
                                        'value'  => $num($value),
                                    ];
                                }
                            }
                            return $b;
                        };

                        $totalsDebtor      = $computeTotals($debtorLedgers);
                        $totalsCreditor    = $computeTotals($creditorLedgers);
                        $breakdownDebtor   = $computeBreakdown($debtorLedgers);
                        $breakdownCreditor = $computeBreakdown($creditorLedgers);

                        // ===== Debtor Ranking table =====
                        $rankingRows = $debtorLedgers->map(function ($l) use ($num) {
                            $overdue = $l['rows']['Balance Overdue'];
                            $notDue  = $l['rows']['Not Due'];
                            $target  = $l['rows']['Balance Target'];
                            $targetCollection = $num($l['target_collection'] ?? 0);
                            $total    = bcadd($overdue, $notDue, 2);
                            $hasTotal = bccomp($total, '0', 2) !== 0;

                            return [
                                'name'        => $l['name'],
                                'overdue'     => $overdue,
                                'not_due'     => $notDue,
                                'total_due'   => $total,
                                'target'      => $target,
                                'target_collection' => $targetCollection, 
                                'pct_overdue' => $hasTotal ? round($overdue / $total * 100, 2) : 0,
                                'pct_not_due' => $hasTotal ? round($notDue  / $total * 100, 2) : 0,
                                'pct_target'    => $l['pct_target'],
                                'class'         => $l['rank'],
                                'debtor_rank'   => $l['debtor_rank'],
                                'priority_rank' => $l['priority_rank'],
                            ];
                        })->values();

                        $rowClassMap = [
                            'Balance'               => 'table-warning',
                            'Balance Overdue'       => 'table-warning',
                            'Balance Target'        => 'table-success',
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
                            'Balance Overdue'           => ['bg' => 'danger',    'icon' => '₹'],
                            'Balance Target'            => ['bg' => 'success',   'icon' => '₹'],
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
                        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
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

                                    <!-- NORMAL breakdown (Interest Cost ko chhodkar sab labels) -->
                                    <div id="ledgerBreakdownNormalWrap">
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

                                    <!-- INTEREST COST breakdown (sirf Interest Cost label ke liye) -->
                                    <div id="ledgerBreakdownInterestWrap" class="d-none">
                                        <div class="small text-muted mb-2" id="bdIntProgress"></div>
                                        <!-- Har ledger ki row -->
                                        <div class="table-responsive" style="max-height: 45vh; overflow: auto;">
                                            <table class="table table-bordered table-hover text-center align-middle mb-0 int-summary-table">
                                                <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                                    <tr>
                                                        <th class="text-start">Ledger</th>
                                                        <th>Interest <span class="bd-int-cleared-label">Received</span></th>
                                                        <th>Interest Due (Party)</th>
                                                        <th class="int-grey">Interest Due (Base)</th>
                                                        <th>Interest Waived (Party)</th>
                                                        <th class="int-grey">Interest Waived (Base)</th>
                                                        <th>Interest Cost (Party Rate)</th>
                                                        <th class="int-grey">Interest Cost (Base Rate)</th>
                                                        <th class="int-grey">Base Int Premium / Concession</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="bdIntBody"></tbody>
                                                <!-- ✅ FIX: Total row (JS .bd-sum-val ko yahin update karta hai) -->
                                                <tfoot style="position: sticky; bottom: 0; background: #fff; z-index: 1;">
                                                    <tr class="fw-bold">
                                                        <th class="text-start">Total</th>
                                                        <th class="bd-sum-val" data-k="received">-</th>
                                                        <th class="bd-sum-val" data-k="dueParty">-</th>
                                                        <th class="bd-sum-val int-grey" data-k="dueBase">-</th>
                                                        <th class="bd-sum-val" data-k="waivedParty">-</th>
                                                        <th class="bd-sum-val int-grey" data-k="waivedBase">-</th>
                                                        <th class="bd-sum-val" data-k="costParty">-</th>
                                                        <th class="bd-sum-val int-grey" data-k="costBase">-</th>
                                                        <th class="bd-sum-val int-grey" data-k="premium">-</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

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

                                    <!-- ✅ FIX: Credit period save alert ab wraps ke bahar hai (Sale table me bhi dikhega) -->
                                    <div id="creditPeriodSaveAlert" class="alert alert-dismissible d-none py-2 px-3 mb-2" role="alert">
                                        <span id="creditPeriodSaveAlertText"></span>
                                        <button type="button" class="btn-close" id="creditPeriodAlertClose" aria-label="Close"></button>
                                    </div>

                                    <!-- 🔍 Search box -->
                                    <div class="mb-2">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                                            <input type="text" id="ledgerVoucherSearch" class="form-control" placeholder="Search particulars, voucher no, type...">
                                        </div>
                                    </div>

                                    <!-- ===== NORMAL table (every field EXCEPT Balance / Balance Overdue / Target) ===== -->
                                    <div class="table-responsive" id="ledgerVoucherNormalWrap" style="max-height: 55vh; overflow-y: auto;">
                                        <table class="table table-bordered table-hover align-middle mb-0" id="ledgerVoucherTable">
                                            <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                                <tr>
                                                    <th class="col-date">Date</th>
                                                    <th class="col-voucherno">Voucher No.</th>
                                                    <th class="col-vouchertype">Voucher Type</th>
                                                    <th class="col-particulars">Particulars</th>
                                                    <th class="col-duedate d-none">Due Date</th>
                                                    <th class="col-creditperiod d-none text-center">Credit Period</th>
                                                    <th class="col-days d-none text-center">Days</th>
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

                                    <!-- ===== BALANCE DUE table (Date / Voucher No. / Days / Pending Amount) ===== -->
                                    <div class="table-responsive d-none" id="ledgerVoucherDueWrap" style="max-height: 55vh; overflow-y: auto;">
                                        <table class="table table-bordered table-hover align-middle mb-0" id="ledgerDueTable">
                                            <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                                <tr id="ledgerDueHeadRow">
                                                    <th>Date</th>
                                                    <th>Voucher No.</th>
                                                    <th class="text-center">Days</th>
                                                    <th class="text-end" id="ledgerDueAmountHeader">Pending Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ledgerDueBody">
                                                <tr><td colspan="4" class="text-center text-muted">No vouchers loaded</td></tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3" class="text-end" id="ledgerDueTotalLabel">Total Due</th>
                                                    <th class="text-end" id="ledgerDueTotal">-</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- ===== TARGET table (Month-wise Overdue / Not Due / Balance / Targets) ===== -->
                                    <div class="table-responsive d-none" id="ledgerVoucherTargetWrap" style="max-height: 55vh; overflow: auto;">
                                        <table class="table table-bordered table-hover align-middle mb-0 text-end" id="ledgerTargetTable">
                                            <thead style="position: sticky; top: 0; background: #fff; z-index: 1;">
                                                <tr>
                                                    <th class="text-start">Month</th>
                                                    <th>Overdue <br> Balance Till</th>
                                                    <th>Not Due  <br> Balance Till</th>
                                                    <th>Balance Today</th>
                                                    <th>% Overdue</th>
                                                    <th>Overdue target</th>
                                                    <th>Target Collection</th>
                                                    <th>% Not Due</th>
                                                    <th>Target Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ledgerTargetBody">
                                                <tr><td colspan="9" class="text-center text-muted">No data loaded</td></tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- ===== INTEREST COST table ===== -->
                                    <div class="table-responsive d-none" id="ledgerVoucherInterestWrap" style="max-height: 55vh; overflow: auto;">

                                        <div class="d-flex gap-4 small mb-2">
                                            <span>Rate Of Interest: <strong id="intPartyRate">-</strong></span>
                                            <span>Base Rate Of Interest: <strong id="intBaseRate">-</strong></span>
                                        </div>
                                        <!-- Summary (single table) -->
                                        <table class="table table-bordered text-center align-middle mb-3 int-summary-table">
                                            <thead>
                                                <tr>
                                                    <th>Interest <span class="int-cleared-label">Received</span></th>
                                                    <th>Interest Due (Party)</th>
                                                    <th class="int-grey">Interest Due (Base)</th>
                                                    <th>Interest Waived (Party)</th>
                                                    <th class="int-grey">Interest Waived (Base)</th>
                                                    <th>Interest Cost (Party Rate)</th>
                                                    <th class="int-grey">Interest Cost (Base Rate)</th>
                                                    <th class="int-grey">Base Int Premium / Concession</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="int-sum-val" data-k="received">-</td>
                                                    <td class="int-sum-val" data-k="dueParty">-</td>
                                                    <td class="int-sum-val int-grey" data-k="dueBase">-</td>
                                                    <td class="int-sum-val" data-k="waivedParty">-</td>
                                                    <td class="int-sum-val int-grey" data-k="waivedBase">-</td>
                                                    <td class="int-sum-val" data-k="costParty">-</td>
                                                    <td class="int-sum-val int-grey" data-k="costBase">-</td>
                                                    <td class="int-sum-val int-grey" data-k="premium">-</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <!-- Tabs -->
                                        <ul class="nav nav-pills mb-3" id="interestTabs" role="tablist">
                                            <li class="nav-item me-2" role="presentation">
                                                <button class="nav-link active" id="intTabReceivedBtn" type="button" role="tab"
                                                        data-bs-toggle="pill" data-bs-target="#intTabReceived"
                                                        aria-controls="intTabReceived" aria-selected="true">
                                                    Interest Due Invoices <span id="intReceivedLabel">Received</span>
                                                    <span class="badge bg-primary ms-1" id="intReceivedCount">0</span>
                                                </button>
                                            </li>
                                            <li class="nav-item me-2" role="presentation" id="intTabPendingLi">
                                                    <button class="nav-link" id="intTabPendingBtn" type="button" role="tab"
                                                        data-bs-toggle="pill" data-bs-target="#intTabPending"
                                                        aria-controls="intTabPending" aria-selected="false">
                                                    Interest Due Invoices Pending
                                                    <span class="badge bg-primary ms-1" id="intPendingCount">0</span>
                                                </button>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <!-- Tab 1: Invoices Received / Paid -->
                                            <div class="tab-pane fade show active" id="intTabReceived" role="tabpanel" aria-labelledby="intTabReceivedBtn">
                                                <table class="table table-bordered table-hover align-middle mb-0 text-end">
                                                    <thead id="intReceivedHead" style="position: sticky; top: 0; background: #fff; z-index: 1;"></thead>
                                                    <tbody id="intReceivedBody">
                                                        <tr><td colspan="10" class="text-center text-muted">No data loaded</td></tr>
                                                    </tbody>
                                                    <tfoot id="intReceivedFoot"></tfoot>
                                                </table>
                                            </div>

                                            <!-- Tab 2: Invoices Pending -->
                                            <div class="tab-pane fade" id="intTabPending" role="tabpanel" aria-labelledby="intTabPendingBtn">
                                                <table class="table table-bordered table-hover align-middle mb-0 text-end">
                                                    <thead id="intPendingHead" style="position: sticky; top: 0; background: #fff; z-index: 1;"></thead>
                                                    <tbody id="intPendingBody">
                                                        <tr><td colspan="9" class="text-center text-muted">No data loaded</td></tr>
                                                    </tbody>
                                                    <tfoot id="intPendingFoot"></tfoot>
                                                </table>
                                            </div>
                                        </div>
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

                    <!-- Debtor Ranking Modal -->
                    <div class="modal fade" id="debtorRankingModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down" style="max-width: 1300px;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Debtor Ranking</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <div class="input-group input-group-sm" style="max-width:100%;">
                                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                                            <input type="text" id="debtorRankingSearch" class="form-control" placeholder="Search debtor...">
                                        </div>
                                    </div>

                                    <div class="table-responsive" style="max-height: 65vh; overflow: auto;">
                                        <table class="table table-bordered table-hover align-middle mb-0 ranking-table" id="debtorRankingTable">
                                            <thead>
                                                <tr>
                                                    <th>Debtor</th>
                                                    <th class="text-end">Balance Overdue</th>
                                                    <th class="text-end">Not Due</th>
                                                    <th class="text-end">Total Due</th>
                                                    <th class="text-end">Target Balance</th>
                                                    <th class="text-end">Target Collection</th>
                                                    <th class="text-end">%Overdue</th>
                                                    <th class="text-end">% Not Due</th>
                                                    <th class="text-end">% Target</th>
                                                    <th class="text-end">Collection<br>Priority Rank</th>
                                                    <th class="text-end">Debtor Rank</th>
                                                    <th class="text-end">Class</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($rankingRows as $r)
                                                    <tr>
                                                        <td class="fw-bold">{{ $r['name'] }}</td>
                                                        <td class="text-end">{{ $fmt($r['overdue']) }}</td>
                                                        <td class="text-end">{{ $fmt($r['not_due']) }}</td>
                                                        <td class="text-end">{{ $fmt($r['total_due']) }}</td>
                                                        <td class="text-end">{{ $fmt($r['target']) }}</td>
                                                        <td class="text-end">{{ $fmt($r['target_collection']) }}</td> 
                                                        <td class="text-end">{{ $r['pct_overdue'] }}%</td>
                                                        <td class="text-end">{{ $r['pct_not_due'] }}%</td>
                                                        <td class="text-end">{{ isset($r['pct_target']) ? $r['pct_target'] . '%' : '-' }}</td>
                                                        <td class="text-end">{{ $r['priority_rank'] ?? '-' }}</td>
                                                        <td class="text-end">{{ $r['debtor_rank'] ?? '-' }}</td>
                                                        <td class="text-end">
                                                            @if($r['class'])
                                                                <span class="badge" style="background-color: {{ $rankColorMap[$r['class']] }};">{{ $r['class'] }}</span>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="12" class="text-center text-muted">No Debtors Found</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
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
                                            class="btn btn-outline-success btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#debtorRankingModal">
                                            <i class="fa fa-list-ol me-1"></i> Debtor Ranking
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
                                                    <input type="text" class="form-control filter-search" placeholder="Search name / mobile / #id...">
                                                </div>

                                                <select class="form-select form-select-sm filter-status">
                                                    <option value="">All Status</option>
                                                    <option value="success">🟢 Marked - Good Standing</option>
                                                    <option value="danger">🔴 Marked - Issue</option>
                                                    <option value="secondary">⚪ Unmarked</option>
                                                </select>

                                                <select class="form-select form-select-sm filter-rank">
                                                    <option value="">All Ranks</option>
                                                    <option value="A">Rank A</option>
                                                    <option value="B">Rank B</option>
                                                    <option value="C">Rank C</option>
                                                    <option value="D">Rank D</option>
                                                    <option value="none">Unranked</option>
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
                                                        $rk = preg_match('/^\s*(?:rank\s*)?([A-D])\s*$/i', (string) ($l['rank'] ?? ''), $rm) ? strtoupper($rm[1]) : '';
                                                        $rankColor = $rankColorMap[$rk] ?? '#adb5bd';
                                                        $rankLabel = $rankLabelMap[$rk] ?? 'Unranked';
                                                    @endphp

                                                    <div class="accordion-item mb-3 border rounded shadow-sm"
                                                        data-ledger="{{ $l['name'] }}"
                                                        data-under="{{ $l['under'] ?? '' }}"
                                                        data-status="{{ $l['status'] ?? 'secondary' }}"
                                                        data-rank="{{ $rk }}"
                                                        data-overlimit="{{ !empty($l['overlimit']) ? 1 : 0 }}"
                                                        data-name="{{ mb_strtolower($l['name']) }}"
                                                        data-uid="{{ strtolower((string) ($l['unique_id'] ?? '')) }}"
                                                        data-mobile="{{ preg_replace('/\D/', '', $l['mobile'] ?? '') }}">
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
                                                                                {{ $rk ?: '-' }}
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
                                                                                                @php
                                                                                                    $intClass = [
                                                                                                        'Interest Cost'     => 'js-int-cost',
                                                                                                        'Interest Received' => 'js-int-received',
                                                                                                        'Interest Paid'     => 'js-int-received',
                                                                                                        'Interest Due'      => 'js-int-due',
                                                                                                        'Interest Waived'   => 'js-int-waived',
                                                                                                    ][$label] ?? null;
                                                                                                @endphp
                                                                                                <strong @if($intClass) class="{{ $intClass }}" @endif>₹ {{ $fmt($value) }}</strong>
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
                                                    <input type="text" class="form-control filter-search" placeholder="Search name / mobile / #id...">
                                                </div>

                                                <select class="form-select form-select-sm filter-status">
                                                    <option value="">All Status</option>
                                                    <option value="success">🟢 Marked - Good Standing</option>
                                                    <option value="danger">🔴 Marked - Issue</option>
                                                    <option value="secondary">⚪ Unmarked</option>
                                                </select>

                                                <select class="form-select form-select-sm filter-rank">
                                                    <option value="">All Ranks</option>
                                                    <option value="A">Rank A</option>
                                                    <option value="B">Rank B</option>
                                                    <option value="C">Rank C</option>
                                                    <option value="D">Rank D</option>
                                                    <option value="none">Unranked</option>
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
                                                        $rk = preg_match('/^\s*(?:rank\s*)?([A-D])\s*$/i', (string) ($l['rank'] ?? ''), $rm) ? strtoupper($rm[1]) : '';
                                                        $rankColor = $rankColorMap[$rk] ?? '#adb5bd';
                                                        $rankLabel = $rankLabelMap[$rk] ?? 'Unranked';
                                                    @endphp
                                                    <div class="accordion-item mb-3 border rounded shadow-sm"
                                                        data-ledger="{{ $l['name'] }}"
                                                        data-under="{{ $l['under'] ?? '' }}"
                                                        data-status="{{ $l['status'] ?? 'secondary' }}"
                                                        data-rank="{{ $rk }}"
                                                        data-overlimit="{{ !empty($l['overlimit']) ? 1 : 0 }}"
                                                        data-name="{{ mb_strtolower($l['name']) }}"
                                                        data-uid="{{ strtolower((string) ($l['unique_id'] ?? '')) }}"
                                                        data-mobile="{{ preg_replace('/\D/', '', $l['mobile'] ?? '') }}">
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
                                                                                {{ $rk ?: '-' }}
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
                                                                                                @php
                                                                                                    $intClass = [
                                                                                                        'Interest Cost'     => 'js-int-cost',
                                                                                                        'Interest Received' => 'js-int-received',
                                                                                                        'Interest Paid'     => 'js-int-received',
                                                                                                        'Interest Due'      => 'js-int-due',
                                                                                                        'Interest Waived'   => 'js-int-waived',
                                                                                                    ][$label] ?? null;
                                                                                                @endphp
                                                                                                <strong @if($intClass) class="{{ $intClass }}" @endif>₹ {{ $fmt($value) }}</strong>
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
                // ✅ HTML escape helper (XSS + broken attribute se bachne ke liye)
                function escHtml(s) {
                    return String(s ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                // ✅ Bina round kiye amount dikhata hai (Indian grouping). DB ki value jaisi hai waisi hi.
                function fmtExact(n) {
                    if (n === null || n === undefined || n === '') return '0';
                    let s = String(n).replace(/,/g, '');
                    let neg = s[0] === '-';
                    s = s.replace(/^[-+]/, '');
                    let parts = s.split('.');
                    let i = parts[0], d = parts[1];
                    if (i.length > 3) {
                        i = i.slice(0, -3).replace(/\B(?=(\d{2})+(?!\d))/g, ',') + ',' + i.slice(-3);
                    }
                    return (neg ? '-' : '') + i + (d ? '.' + d : '');
                }

                function calcDueDateFromDays(days) {
                    let d = new Date();
                    d.setDate(d.getDate() - (parseInt(days, 10) || 0));
                    return d;
                }

                function fmtDateShared(dateObj) {
                    if (!(dateObj instanceof Date) || isNaN(dateObj.getTime())) return '-';
                    return dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                }

                const breakdownDataDebtor   = @json($breakdownDebtor);
                const breakdownDataCreditor = @json($breakdownCreditor);
                const totalsDataDebtor      = @json($totalsDebtor);
                const totalsDataCreditor    = @json($totalsCreditor);
                const currentCompanyName    = @json($company);

                // ✅ Voucher modal ke ajax race condition se bachne ke liye token
                let voucherReqToken = 0;

                // ===== Interest Cost Breakdown (sab ledgers ka total + har ledger ki row) =====
                const BD_INT_KEYS = ['received','dueParty','dueBase','waivedParty','waivedBase','costParty','costBase','premium'];
                const BD_INT_BASE_KEYS = ['dueBase','waivedBase','costBase','premium'];
                let bdIntToken = 0;   // modal dobara khule to purane ajax ignore karne ke liye

                function bdInterestVals(res) {
                    const n = v => parseFloat(String(v ?? 0).replace(/,/g, '')) || 0;

                    const received    = n(res.settlement_received_total) + n(res.pending_settlement_received_total);
                    const waivedParty = n(res.interest_waived_total)     + n(res.pending_interest_waived_total);
                    const waivedBase  = n(res.interest_waived_total_b)   + n(res.pending_interest_waived_total_b);
                    const costParty   = n(res.total);
                    const costBase    = n(res.total_b);

                    return {
                        received:    received,
                        dueParty:    Math.max(costParty - received - waivedParty, 0),
                        dueBase:     Math.max(costBase  - received - waivedBase,  0),
                        waivedParty: waivedParty,
                        waivedBase:  waivedBase,
                        costParty:   costParty,
                        costBase:    costBase,
                        premium:     costParty - costBase
                    };
                }

                function loadBreakdownInterest(items, type) {
                    const token      = ++bdIntToken;
                    const isCreditor = (type === 'creditor');
                    const rs         = v => '₹ ' + fmtExact(v.toFixed(2));
                    const esc        = escHtml;

                    $('.bd-int-cleared-label').text(isCreditor ? 'Paid' : 'Received');

                    // Pehle saari rows "..." ke saath banao
                    let html = '';
                    items.forEach(function (item, i) {
                        html += `<tr data-i="${i}">
                            <td class="text-start fw-bold">${esc(item.ledger)}</td>
                            ${BD_INT_KEYS.map(k => `<td data-k="${k}" class="text-muted ${BD_INT_BASE_KEYS.includes(k) ? 'int-grey' : ''}">...</td>`).join('')}
                        </tr>`;
                    });
                    $('#bdIntBody').html(html || '<tr><td colspan="9" class="text-center text-muted">No data available</td></tr>');
                    $('.bd-sum-val').text('-');

                    if (!items.length) {
                        $('#bdIntProgress').text('');
                        return;
                    }

                    const totals = {};
                    BD_INT_KEYS.forEach(k => totals[k] = 0);

                    let done = 0, next = 0, failed = 0;
                    const total = items.length;

                    const updateProgress = () => {
                        $('#bdIntProgress').text(
                            done < total
                                ? 'Loading interest data... ' + done + ' / ' + total
                                : (failed ? 'Loaded with ' + failed + ' error(s).' : '')
                        );
                    };
                    updateProgress();

                    const finishOne = () => {
                        done++;
                        BD_INT_KEYS.forEach(k => $('.bd-sum-val[data-k="' + k + '"]').text(rs(totals[k])));
                        updateProgress();
                        pump();
                    };

                    function pump() {
                        if (token !== bdIntToken || next >= total) return;
                        const i    = next++;
                        const item = items[i];
                        const $row = $('#bdIntBody tr[data-i="' + i + '"]');

                        $.ajax({
                            url: "{{ route('owner.tally.ledger.interest-data') }}",
                            method: "GET",
                            dataType: "json",
                            data: { company: currentCompanyName, ledger: item.ledger, under: item.under },
                            success: function (res) {
                                if (token !== bdIntToken) return;
                                const vals = bdInterestVals(res);
                                BD_INT_KEYS.forEach(k => {
                                    totals[k] += vals[k];
                                    $row.find('td[data-k="' + k + '"]').removeClass('text-muted').text(rs(vals[k]));
                                });
                                finishOne();
                            },
                            error: function () {
                                if (token !== bdIntToken) return;
                                failed++;
                                $row.find('td[data-k]').text('Error').addClass('text-danger');
                                finishOne();
                            }
                        });
                    }

                    // 5 parallel requests
                    for (let c = 0; c < Math.min(5, total); c++) pump();
                }

                function showLedgerBreakdown(label, type) {
                    let dataset = (type === 'creditor') ? breakdownDataCreditor : breakdownDataDebtor;
                    let totals  = (type === 'creditor') ? totalsDataCreditor : totalsDataDebtor;
                    let items = dataset[label] || [];
                    let typeLabel = (type === 'creditor') ? 'Sundry Creditors' : 'Sundry Debtors';

                    $('#ledgerBreakdownTitle').text(label + ' Breakdown (' + typeLabel + ')');
                    $('#ledgerBreakdownSearch').val('');

                    // ===== Interest Cost: naya 8-column UI =====
                    if (label === 'Interest Cost') {
                        $('#ledgerBreakdownNormalWrap').addClass('d-none');
                        $('#ledgerBreakdownInterestWrap').removeClass('d-none');
                        loadBreakdownInterest(items, type);
                        return;
                    }

                    // ===== Baaki sab labels: pehle jaisa =====
                    bdIntToken++;   // chal rahe interest ajax band
                    $('#ledgerBreakdownInterestWrap').addClass('d-none');
                    $('#ledgerBreakdownNormalWrap').removeClass('d-none');

                    let html = '';
                    items.forEach(function (item) {
                        html += `<tr>
                            <td>${escHtml(item.ledger)} <span class="badge bg-light text-dark ms-1">${escHtml(item.under)}</span></td>
                            <td>${escHtml(item.label)}</td>
                            <td><strong>₹ ${fmtExact(item.value)}</strong></td>
                        </tr>`;
                    });

                    if (items.length === 0) {
                        html = '<tr><td colspan="3" class="text-center text-muted">No data available</td></tr>';
                    }

                    $('#ledgerBreakdownBody').html(html);
                    // Total PHP se aaya hua exact value (JS me float sum nahi)
                    $('#ledgerBreakdownTotal').text('₹ ' + fmtExact(totals[label] ?? 0));
                }

                // ===== TARGET modal: DB se data =====
                function renderTargetData(ledger, under, token) {
                    const amt = n => (n === null || n === undefined) ? '' : fmtExact(n);
                    const pct = n => (n === null || n === undefined) ? '' : fmtExact(n) + '%';

                    $('#ledgerVoucherLoading').removeClass('d-none');

                    $.ajax({
                        url: "{{ route('owner.tally.ledger.target-data') }}",
                        method: "GET",
                        dataType: "json",
                        data: { company: currentCompanyName, ledger: ledger, under: under },
                        success: function (res) {
                            if (token !== voucherReqToken) return;
                            $('#ledgerVoucherLoading').addClass('d-none');
                            let html = '';
                            (res.rows || []).forEach(function (r) {
                                html += `<tr>
                                    <td class="month-cell text-start">${escHtml(r.month)}</td>
                                    <td>${amt(r.overdue)}</td>
                                    <td>${amt(r.notDue)}</td>
                                    <td>${amt(r.balance)}</td>
                                    <td>${pct(r.pctOverdue)}</td>
                                    <td>${pct(r.target)}</td>
                                    <td>${amt(r.targetCollection)}</td>
                                    <td>${pct(r.pctNotDue)}</td>
                                    <td>${amt(r.targetBal)}</td>
                                </tr>`;
                            });
                            $('#ledgerTargetBody').html(html || '<tr><td colspan="9" class="text-center text-muted">No data found</td></tr>');
                        },
                        error: function () {
                            if (token !== voucherReqToken) return;
                            $('#ledgerVoucherLoading').addClass('d-none');
                            $('#ledgerVoucherError').removeClass('d-none').text('Could not load target data.');
                        }
                    });
                }


                // ===== Interest summary (single table) =====
                function fillInterestSummary(res, isCreditor) {
                    const rs   = v => '₹ ' + fmtExact(v.toFixed(2));
                    const vals = bdInterestVals(res);

                    Object.keys(vals).forEach(k => $('.int-sum-val[data-k="' + k + '"]').text(rs(vals[k])));
                    $('.int-cleared-label').text(isCreditor ? 'Paid' : 'Received');
                }

                // ===== INTEREST modal: field ke hisaab se columns (Party + Base[grey]) =====
                function renderInterestData(ledger, under, field = 'Interest Cost', token) {
                    const amt = n => (n === null || n === undefined || n === '') ? '-' : fmtExact(n);
                    const rs  = n => '₹ ' + amt(n);
                    const num = v => parseFloat(String(v ?? 0).replace(/,/g, '')) || 0;
                    const fmtD = d => {
                        if (!d) return '-';
                        const o = new Date(d);
                        return isNaN(o.getTime()) ? escHtml(d) : fmtDateShared(o);
                    };
                    const isCreditor = (under === 'Sundry Creditors');
                    const clr        = isCreditor ? 'Paid' : 'Recd';
                    const settleWord = isCreditor ? 'Interest Paid' : 'Interest Received';

                    const rowFilters = {
                        'Interest Cost':     r => true,
                        'Interest Received': r => num(r.settlement_amount) > 0 || num(r.settlement_amount_b) > 0,
                        'Interest Paid':     r => num(r.settlement_amount) > 0 || num(r.settlement_amount_b) > 0,
                        'Interest Due':      r => (num(r.interest_due) - num(r.settlement_amount) - num(r.interest_waived)) > 0.009
                                            || (num(r.interest_due_b) - num(r.settlement_amount_b) - num(r.interest_waived_b)) > 0.009,
                        'Interest Waived':   r => num(r.interest_waived) > 0 || num(r.interest_waived_b) > 0,
                    };
                    const keep = rowFilters[field] || (() => true);

                    // [party, base] totals — received tab & pending tab
                    const footerTotals = (res) => {
                        switch (field) {
                            case 'Interest Received':
                            case 'Interest Paid':
                                return {
                                    rec:  [res.settlement_received_total,   res.settlement_received_total_b],
                                    pend: [res.pending_settlement_received_total, res.pending_settlement_received_total_b]
                                };
                            case 'Interest Waived':
                                return {
                                    rec:  [res.interest_waived_total,   res.interest_waived_total_b],
                                    pend: [res.pending_interest_waived_total, res.pending_interest_waived_total_b]
                                };
                            default: // Cost, Due
                                return {
                                    rec:  [res.received_total, res.received_total_b],
                                    pend: [res.pending_total,  res.pending_total_b]
                                };
                        }
                    };

                    const badge = r => r.settlement_status
                        ? `<span class="badge ${r.settlement_status === 'settled' ? 'bg-success' : 'bg-warning text-dark'}">
                                ${escHtml(r.settlement_status.charAt(0).toUpperCase() + r.settlement_status.slice(1))}
                           </span>`
                        : '-';
                    const remaining  = r => num(r.interest_due)   - num(r.settlement_amount)   - num(r.interest_waived);
                    const remainingB = r => num(r.interest_due_b) - num(r.settlement_amount_b) - num(r.interest_waived_b);

                    // grey:true => base rate column
                    const colDefs = {
                        inv:        { h: 'INV No',              cls: 'text-start', v: r => escHtml(r.inv_no ?? '-') },
                        recdDate:   { h: `Inv ${clr} Date`,     v: r => fmtD(r.recd_date) },
                        date:       { h: 'Date',                cls: 'text-start', v: r => fmtD(r.date) },
                        dueDate:    { h: 'Due Date',            v: r => fmtD(r.due_date) },
                        intPending: { h: 'Int Pending',         v: r => rs(r.int_pending) },
                        amount:     { h: 'Amount',              v: r => rs(r.amount) },
                        days:       { h: 'Days',                cls: 'text-center', v: r => `<span class="badge bg-danger">${escHtml(r.days ?? '-')} days</span>` },
                        intOnInt:   { h: 'Int on Int',          v: r => rs(r.int_on_int) },
                        due:        { h: 'Interest Due',        cls: 'fw-bold', v: r => rs(r.interest_due) },
                        status:     { h: 'Settlement Status',   cls: 'text-center', v: r => badge(r) },
                        settle:     { h: `${settleWord} Amount`, v: r => rs(r.settlement_amount) },
                        settleShort:{ h: `Interest ${clr}`,      v: r => rs(r.settlement_amount) },
                        waived:     { h: 'Interest Waived',     cls: 'fw-bold', v: r => rs(r.interest_waived) },
                        remaining:  { h: 'Remaining Due',       cls: 'fw-bold text-danger', v: r => rs(Math.max(remaining(r), 0).toFixed(2)) },

                        // ---- BASE RATE (grey) ----
                        intPendingB:  { grey: true, h: 'Int Pending (Base)',  v: r => rs(r.int_pending_b) },
                        intOnIntB:    { grey: true, h: 'Int on Int (Base)',   v: r => rs(r.int_on_int_b) },
                        dueB:         { grey: true, h: 'Interest Due (Base)', cls: 'fw-bold', v: r => rs(r.interest_due_b) },
                        settleB:      { grey: true, h: `${settleWord} Amt (Base)`, v: r => rs(r.settlement_amount_b) },
                        settleShortB: { grey: true, h: `Interest ${clr} (Base)`,   v: r => rs(r.settlement_amount_b) },
                        waivedB:      { grey: true, h: 'Interest Waived (Base)', cls: 'fw-bold', v: r => rs(r.interest_waived_b) },
                        remainingB:   { grey: true, h: 'Remaining Due (Base)', cls: 'fw-bold text-danger', v: r => rs(Math.max(remainingB(r), 0).toFixed(2)) },
                    };

                    const layouts = {
                        'Interest Cost': {
                            received: ['inv','recdDate','intPending','days','intOnInt','due','status','settle','waived',
                                       'intPendingB','intOnIntB','dueB','settleB','waivedB'],
                            pending:  ['date','dueDate','inv','days','amount','due','status','settle','waived',
                                       'dueB','settleB','waivedB'],
                            label: 'Interest Due',
                            rTotal: 'due', rTotalB: 'dueB', pTotal: 'due', pTotalB: 'dueB'
                        },
                        'Interest Received': {
                            received: ['inv','recdDate','intPending','days','intOnInt','due','settleShort','waived',
                                       'intPendingB','intOnIntB','dueB','settleShortB','waivedB'],
                            pending:  ['date','inv','days','due','status','settle','dueB','settleB'],
                            label: settleWord,
                            rTotal: 'settleShort', rTotalB: 'settleShortB', pTotal: 'settle', pTotalB: 'settleB'
                        },
                        'Interest Due': {
                            received: ['inv','recdDate','intPending','days','intOnInt','due','remaining',
                                       'intPendingB','intOnIntB','dueB','remainingB'],
                            pending:  ['date','dueDate','inv','days','amount','due','remaining','dueB','remainingB'],
                            label: 'Interest Due',
                            rTotal: 'due', rTotalB: 'dueB', pTotal: 'due', pTotalB: 'dueB'
                        },
                        'Interest Waived': {
                            received: ['inv','recdDate','intPending','days','intOnInt','due','settleShort','waived',
                                       'intPendingB','intOnIntB','dueB','settleShortB','waivedB'],
                            pending:  ['date','inv','days','due','status','waived','dueB','waivedB'],
                            label: 'Interest Waived',
                            rTotal: 'waived', rTotalB: 'waivedB', pTotal: 'waived', pTotalB: 'waivedB'
                        },
                    };
                    layouts['Interest Paid'] = layouts['Interest Received'];
                    const layout = layouts[field] || layouts['Interest Cost'];

                    const alignCls = c => (c.cls && c.cls.includes('text-start')) ? 'text-start'
                                        : (c.cls && c.cls.includes('text-center')) ? 'text-center' : '';

                    const buildHead = keys => '<tr>' + keys.map(k => {
                        const c = colDefs[k];
                        return `<th class="${alignCls(c)} ${c.grey ? 'int-grey' : ''}">${c.h}</th>`;
                    }).join('') + '</tr>';

                    const buildRows = (keys, list) => list.map(r =>
                        '<tr>' + keys.map(k => {
                            const c = colDefs[k];
                            return `<td class="${c.cls ?? ''} ${c.grey ? 'int-grey' : ''}">${c.v(r)}</td>`;
                        }).join('') + '</tr>'
                    ).join('');

                    // Footer: label ek total column tak, phir party total + base total apni-apni column ke neeche
                    const buildFoot = (keys, label, partyKey, partyId, baseKey, baseId) => {
                        const first = Math.min(keys.indexOf(partyKey), keys.indexOf(baseKey));
                        let html = `<tr><th colspan="${first}" class="text-end">${label}</th>`;
                        for (let i = first; i < keys.length; i++) {
                            const k = keys[i];
                            if (k === partyKey)      html += `<th id="${partyId}">-</th>`;
                            else if (k === baseKey)  html += `<th id="${baseId}" class="int-grey">-</th>`;
                            else                     html += `<th class="${colDefs[k].grey ? 'int-grey' : ''}"></th>`;
                        }
                        return html + '</tr>';
                    };

                    const rKeys = layout.received;
                    const pKeys = layout.pending;
                    const cleared = isCreditor ? 'Paid' : 'Received';
                    const isCostOrDue = (field === 'Interest Cost' || field === 'Interest Due');

                    $('#intReceivedHead').html(buildHead(rKeys));
                    $('#intPendingHead').html(buildHead(pKeys));
                    $('#intReceivedFoot').html(buildFoot(rKeys,
                        isCostOrDue ? `Interest Due Invoices ${cleared}` : `${layout.label} (Invoices ${cleared})`,
                        layout.rTotal, 'intReceivedTotal', layout.rTotalB, 'intReceivedTotalB'));
                    $('#intPendingFoot').html(buildFoot(pKeys,
                        isCostOrDue ? 'Interest Due Invoices Pending' : `${layout.label} (Invoices Pending)`,
                        layout.pTotal, 'intPendingTotal', layout.pTotalB, 'intPendingTotalB'));

                    // Summary table me kaunsi value highlight karni hai
                    const highlightKeys = {
                        'Interest Cost':     ['costParty', 'costBase'],
                        'Interest Due':      ['dueParty', 'dueBase'],
                        'Interest Received': ['received'],
                        'Interest Paid':     ['received'],
                        'Interest Waived':   ['waivedParty', 'waivedBase'],
                    }[field];
                    $('#ledgerVoucherInterestWrap .int-highlight').removeClass('int-highlight');
                    if (highlightKeys) {
                        $(highlightKeys.map(k => 'tbody .int-sum-val[data-k="' + k + '"]').join(',')).addClass('int-highlight');
                    }

                    // Reset
                    $('.int-cleared-label').text(cleared);
                    $('#intReceivedLabel').text(cleared);
                    const isSettleField = (field === 'Interest Received' || field === 'Interest Paid');
                    $('#intTabPendingLi').toggleClass('d-none', isSettleField);
                    $('#intPartyRate, #intBaseRate, .int-sum-val').text('-');

                    const emptyRow = n => `<tr><td colspan="${n}" class="text-center text-muted">No data loaded</td></tr>`;
                    $('#intReceivedBody').html(emptyRow(rKeys.length));
                    $('#intPendingBody').html(emptyRow(pKeys.length));
                    $('#intReceivedCount, #intPendingCount').text('0');
                    bootstrap.Tab.getOrCreateInstance(document.getElementById('intTabReceivedBtn')).show();

                    $('#ledgerVoucherLoading').removeClass('d-none');

                    $.ajax({
                        url: "{{ route('owner.tally.ledger.interest-data') }}",
                        method: "GET",
                        dataType: "json",
                        data: { company: currentCompanyName, ledger: ledger, under: under },
                        success: function (res) {
                            if (token !== voucherReqToken) return;
                            $('#ledgerVoucherLoading').addClass('d-none');

                            const ft = footerTotals(res);

                            $('#intPartyRate').text(res.party_rate != null ? res.party_rate + '%' : '-');
                            $('#intBaseRate').text(res.base_rate  != null ? res.base_rate  + '%' : '-');

                            // Top summary table
                            fillInterestSummary(res, isCreditor);

                            // Tab footers
                            $('#intReceivedTotal').text(rs(ft.rec[0] ?? 0));
                            $('#intReceivedTotalB').text(rs(ft.rec[1] ?? 0));
                            $('#intPendingTotal').text(rs(ft.pend[0] ?? 0));
                            $('#intPendingTotalB').text(rs(ft.pend[1] ?? 0));

                            const received = (res.received || []).filter(keep);
                            const pending  = (res.pending  || []).filter(keep);

                            $('#intReceivedBody').html(
                                received.length ? buildRows(rKeys, received)
                                                : `<tr><td colspan="${rKeys.length}" class="text-center text-muted">No data found</td></tr>`
                            );
                            $('#intReceivedCount').text(received.length);

                            $('#intPendingBody').html(
                                pending.length ? buildRows(pKeys, pending)
                                               : `<tr><td colspan="${pKeys.length}" class="text-center text-muted">No data found</td></tr>`
                            );
                            $('#intPendingCount').text(pending.length);
                        },
                        error: function () {
                            if (token !== voucherReqToken) return;
                            $('#ledgerVoucherLoading').addClass('d-none');
                            $('#ledgerVoucherError').removeClass('d-none').text('Could not load interest data.');
                        }
                    });
                }

                // ===== Per-ledger, per-field (Balance / Balance Overdue / Target / Sale / ...) voucher modal =====
                function showLedgerVoucherDetail(ledger, under, field) {
                    const token = ++voucherReqToken;

                    let isBalance  = (field === 'Balance');
                    let isNotDue   = (field === 'Not Due');
                    let isDue      = (field === 'Balance Overdue') || isNotDue;   // Not Due bhi Due wali table use karta hai
                    let isTarget   = (field === 'Balance Target');
                    let isSale     = (field === 'Sale');
                    let isCreditor = (under === 'Sundry Creditors');
                    const interestFields = ['Interest Cost', 'Interest Received', 'Interest Paid', 'Interest Due', 'Interest Waived'];
                    let isInterest = interestFields.includes(field);

                    $('#ledgerVoucherModalTitle').text(
                        ledger + ' — ' + field + (isBalance ? ' Breakdown' : (isDue ? (isNotDue ? ' (Not Due Vouchers)' : ' (Overdue Vouchers)') : (isTarget ? ' (Month-wise)' : (isInterest ? ' (' + field + ' Breakdown)' : ' Vouchers'))))
                    );
                    $('#ledgerVoucherError').addClass('d-none').text('');
                    $('#creditPeriodSaveAlert').addClass('d-none');
                    $('#ledgerVoucherLoading').removeClass('d-none');
                    $('#ledgerVoucherNormalWrap').toggleClass('d-none', isBalance || isDue || isTarget || isInterest);
                    $('#ledgerVoucherBalanceWrap').toggleClass('d-none', !isBalance);
                    $('#ledgerVoucherDueWrap').toggleClass('d-none', !isDue);
                    $('#ledgerVoucherTargetWrap').toggleClass('d-none', !isTarget);
                    $('#ledgerVoucherInterestWrap').toggleClass('d-none', !isInterest);

                    $('#ledgerVoucherBody').html('<tr><td colspan="6" class="text-center text-muted">No vouchers loaded</td></tr>');
                    $('#ledgerBalanceBody').html('<tr><td colspan="5" class="text-center text-muted">No vouchers loaded</td></tr>');
                    $('#ledgerDueBody').html('<tr><td colspan="4" class="text-center text-muted">No vouchers loaded</td></tr>');
                    $('#ledgerTargetBody').html('<tr><td colspan="9" class="text-center text-muted">No data loaded</td></tr>');
                    $('#ledgerDueTotal').text('-');

                    $('#ledgerVoucherSearch').val('');

                    $('#ledgerBalanceClearedHeader').text(isCreditor ? 'Paid' : 'Received');
                    $('#ledgerDueHeadRow').html(isNotDue
                        ? '<th>Date</th><th>Due Date</th><th>Voucher No.</th><th class="text-end" id="ledgerDueAmountHeader">Amount</th>'
                        : '<th>Date</th><th>Voucher No.</th><th class="text-center">Days</th><th class="text-end" id="ledgerDueAmountHeader">Pending Amount</th>');
                    $('#ledgerDueTotalLabel').text(isNotDue ? 'Total Not Due' : 'Total Due');

                    let hideVoucherNo = (field === 'Receipts');
                    let hideDebit     = (field === 'Receipts');
                    let hideCredit    = (field === 'Sale' || field === 'Other Debits');

                    $('#ledgerVoucherTable .col-voucherno').toggle(!hideVoucherNo);
                    $('#ledgerVoucherTable .col-debit').toggle(!hideDebit);
                    $('#ledgerVoucherTable .col-credit').toggle(!hideCredit);

                    // Sale ke liye Credit Period columns dikhao
                    $('#ledgerVoucherTable .col-duedate').toggleClass('d-none', !isSale);
                    $('#ledgerVoucherTable .col-creditperiod').toggleClass('d-none', !isSale);
                    $('#ledgerVoucherTable .col-days').toggleClass('d-none', !isSale);

                    let baseUrl = "{{ route('owner.tally.ledger.vouchers', ['company' => ':company', 'ledger' => ':ledger', 'under' => ':under']) }}";
                    let fullPageUrl = baseUrl
                        .replace(':company', encodeURIComponent(currentCompanyName))
                        .replace(':ledger', encodeURIComponent(ledger))
                        .replace(':under', encodeURIComponent(under));
                    $('#ledgerVoucherViewAllLink').attr('href', fullPageUrl);

                    // ===== TARGET: DB se data =====
                    if (isTarget) {
                        renderTargetData(ledger, under, token);
                        return;
                    }

                    if (isInterest) {
                        renderInterestData(ledger, under, field, token);
                        return;
                    }

                    let fieldAjaxBaseUrl = "{{ route('owner.tally.ledger.field-vouchers', ['company' => ':company', 'ledger' => ':ledger', 'under' => ':under']) }}";
                    let fieldAjaxUrl = fieldAjaxBaseUrl
                        .replace(':company', encodeURIComponent(currentCompanyName))
                        .replace(':ledger', encodeURIComponent(ledger))
                        .replace(':under', encodeURIComponent(under));

                    let dueAjaxUrl = "{{ route('owner.tally.ledger.due-vouchers') }}";

                    // ✅ Round nahi, DB jaisa exact amount
                    let fmtAmt = function (n) {
                        return fmtExact(n);
                    };

                    let fmtDate = function (d) {
                        if (!d) return '-';
                        let dateObj = new Date(d);
                        if (isNaN(dateObj.getTime())) return escHtml(d);
                        return dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                    };

                    $.ajax({
                        url: isDue ? dueAjaxUrl : fieldAjaxUrl,
                        method: "GET",
                        data: isDue
                            ? { company: currentCompanyName, ledger: ledger, under: under, type: isNotDue ? 'not_due' : 'due' }
                            : { field: field },
                        dataType: "json",
                        success: function (res) {
                            if (token !== voucherReqToken) return;
                            $('#ledgerVoucherLoading').addClass('d-none');

                            let vouchers = (res && res.vouchers) ? res.vouchers : (Array.isArray(res) ? res : []);

                            if (!vouchers.length) {
                                if (isDue) {
                                    $('#ledgerDueBody').html('<tr><td colspan="4" class="text-center text-muted">' + (isNotDue ? 'No not due vouchers found' : 'No overdue vouchers found 🎉') + '</td></tr>');
                                    $('#ledgerDueTotal').text('₹ 0');
                                } else if (isBalance) {
                                    $('#ledgerBalanceBody').html('<tr><td colspan="5" class="text-center text-muted">No pending invoices found — all clear! 🎉</td></tr>');
                                } else {
                                    let colspan = 6 - (hideVoucherNo ? 1 : 0) - (hideDebit ? 1 : 0) - (hideCredit ? 1 : 0) + (isSale ? 3 : 0);
                                    $('#ledgerVoucherBody').html('<tr><td colspan="' + colspan + '" class="text-center text-muted">No vouchers found for this field.</td></tr>');
                                }
                                return;
                            }

                            // ===== BALANCE DUE (sirf Days + Amount) =====
                            if (isDue) {
                                let rows = '';
                                vouchers.forEach(function (v) {
                                    let partial = (parseFloat(v.original) || 0) > (parseFloat(v.amount) || 0) + 0.009
                                        ? `<div class="text-muted small">of ₹ ${fmtAmt(v.original)}</div>`
                                        : '';

                                    if (isNotDue) {
                                        rows += `<tr>
                                            <td>${fmtDate(v.date)}</td>
                                            <td>${fmtDate(v.due_date)}</td>
                                            <td>${escHtml(v.voucher_number ?? '-')}</td>
                                            <td class="text-end fw-bold">₹ ${fmtAmt(v.amount)}</td>
                                        </tr>`;
                                        return;
                                    }

                                    rows += `<tr data-voucher-id="${escHtml(v.id ?? v.voucher_number ?? '')}">
                                        <td>${fmtDate(v.date)}</td>
                                        <td>${escHtml(v.voucher_number ?? '-')}</td>
                                        <td class="text-center"><span class="badge bg-danger days-badge">${escHtml(v.days)} days</span></td>
                                        <td class="text-end fw-bold">₹ ${fmtAmt(v.amount)}${partial}</td>
                                    </tr>`;
                                });
                                $('#ledgerDueBody').html(rows);
                                // Total controller se aata hai (exact). JS me float sum nahi karte.
                                $('#ledgerDueTotal').text('₹ ' + fmtAmt(res.total ?? 0));
                                return;
                            }

                            // ===== BALANCE =====
                            if (isBalance) {
                                let clearedKey = isCreditor ? 'paid' : 'received';
                                let rows = '';
                                vouchers.forEach(function (v) {
                                    rows += `<tr>
                                        <td>${fmtDate(v.date)}</td>
                                        <td>${escHtml(v.voucher_number ?? '-')}</td>
                                        <td class="text-end">₹ ${fmtAmt(v.original)}</td>
                                        <td class="text-end">₹ ${fmtAmt(v[clearedKey])}</td>
                                        <td class="text-end fw-bold ${parseFloat(v.pending) > 0 ? 'text-danger' : 'text-success'}">₹ ${fmtAmt(v.pending)}</td>
                                    </tr>`;
                                });
                                $('#ledgerBalanceBody').html(rows);
                                return;
                            }

                            // ===== NORMAL fields (Sale yahin, Due Date + Credit Period yahin editable) =====
                            let rows = '';
                            vouchers.forEach(function (v) {
                                let dueDateCell = '';
                                let creditPeriodCell = '';
                                let daysCell = '';

                                if (isSale) {
                                    dueDateCell = `<td class="col-duedate due-date-cell">${fmtDateShared(calcDueDateFromDays(v.days))}</td>`;

                                    creditPeriodCell = `<td class="col-creditperiod text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <input type="number" min="0"
                                                class="form-control form-control-sm credit-period-input"
                                                value="${escHtml(v.credit_period ?? 0)}"
                                                data-voucher-id="${escHtml(v.id ?? '')}"
                                                data-voucher-number="${escHtml(v.voucher_no ?? '')}"
                                                data-ledger="${escHtml(ledger)}"
                                                data-under="${escHtml(under)}">
                                            <button type="button" class="btn btn-sm btn-outline-primary credit-period-save-btn" title="Save">
                                                <i class="fa fa-save"></i>
                                            </button>
                                        </div>
                                    </td>`;

                                    daysCell = `<td class="col-days text-center"><span class="badge bg-danger days-badge">${escHtml(v.days ?? 0)} days</span></td>`;
                                }

                                rows += `<tr>
                                    <td class="col-date">${fmtDate(v.date)}</td>
                                    <td class="col-voucherno" style="${hideVoucherNo ? 'display:none;' : ''}">${escHtml(v.voucher_no ?? '-')}</td>
                                    <td class="col-vouchertype">${escHtml(v.voucher_type ?? '-')}</td>
                                    <td class="col-particulars">${escHtml(v.particulars ?? '-')}</td>
                                    ${dueDateCell}
                                    ${creditPeriodCell}
                                    ${daysCell}
                                    <td class="col-debit text-end" style="${hideDebit ? 'display:none;' : ''}">${parseFloat(v.debit) > 0 ? '₹ ' + fmtAmt(v.debit) : ''}</td>
                                    <td class="col-credit text-end" style="${hideCredit ? 'display:none;' : ''}">${parseFloat(v.credit) > 0 ? '₹ ' + fmtAmt(v.credit) : ''}</td>
                                </tr>`;
                            });
                            $('#ledgerVoucherBody').html(rows);
                        },
                        error: function () {
                            if (token !== voucherReqToken) return;
                            $('#ledgerVoucherLoading').addClass('d-none');
                            $('#ledgerVoucherError')
                                .removeClass('d-none')
                                .text('Could not load vouchers for this field. Please try "View Full Ledger Vouchers" instead.');
                        }
                    });
                }

                // ===== Credit Period alert helper (manual dismiss, DOM se delete nahi hota) =====
                let creditPeriodAlertTimer = null;
                function showCreditPeriodAlert(type, message) {
                    const $alert = $('#creditPeriodSaveAlert');
                    clearTimeout(creditPeriodAlertTimer);

                    $alert.removeClass('alert-success alert-danger d-none').addClass('alert-' + type);
                    $('#creditPeriodSaveAlertText').text(message);

                    creditPeriodAlertTimer = setTimeout(function () {
                        $alert.addClass('d-none');
                    }, 2500);
                }

                $(document).on('click', '#creditPeriodAlertClose', function () {
                    clearTimeout(creditPeriodAlertTimer);
                    $('#creditPeriodSaveAlert').addClass('d-none');
                });

                // ===== Per-voucher editable Credit Period -> Save button se update =====
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
                            // .attr() taaki ledger naam / id string hi rahe (.data() type convert kar deta hai)
                            ledger: $input.attr('data-ledger'),
                            under: $input.attr('data-under'),
                            voucher_id: $input.attr('data-voucher-id'),
                            voucher_number: $input.attr('data-voucher-number'),
                            credit_period: newValue
                        },
                        dataType: "json",
                        success: function (res) {
                            $input.prop('disabled', false);
                            $btn.prop('disabled', false);
                            $btn.html(originalIcon);

                            if (res && res.days !== undefined) {
                                $row.find('.days-badge').text(res.days + ' days');
                                $row.find('.due-date-cell').text(fmtDateShared(calcDueDateFromDays(res.days)));
                            }

                            showCreditPeriodAlert('success', 'Credit period updated successfully.');

                            // Row ko ek pal ke liye highlight, visual confirmation ke liye
                            $row.addClass('table-success');
                            setTimeout(() => $row.removeClass('table-success'), 1200);
                        },
                        error: function () {
                            $input.prop('disabled', false);
                            $btn.prop('disabled', false);
                            $btn.html(originalIcon);
                            showCreditPeriodAlert('danger', 'Could not update credit period. Please try again.');
                        }
                    });
                });

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
                            <td>${escHtml(item.ledger)}</td>
                            <td>${escHtml(item.under)}</td>
                        </tr>`;
                    });
                    $('#confirmBadDebtList').html(rows);

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmBadDebtModal')).show();
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

                // ===== Search + Status / Rank / Overlimit Filters (per tab) =====
                function applyLedgerFilters(scope) {
                    const $bar       = $('.ledger-filter-bar[data-scope="' + scope + '"]');
                    const q          = ($bar.find('.filter-search').val() || '').toLowerCase().trim();
                    const qDigits    = q.replace(/\D/g, '');
                    const qId        = q.replace(/^#/, '');
                    const statusVal  = $bar.find('.filter-status').val();
                    const rankVal    = $bar.find('.filter-rank').val();
                    const overlimitV = $bar.find('.filter-overlimit').val();

                    const $accordion = scope === 'debtor' ? $('#debtorAccordion') : $('#creditorAccordion');
                    const $items     = $accordion.find('.accordion-item');
                    const total      = $items.length;
                    let visibleCount = 0;

                    $items.each(function () {
                        const $item  = $(this);
                        const name   = ($item.attr('data-name')   || '').toLowerCase();
                        const uid    = ($item.attr('data-uid')    || '').toLowerCase();
                        const mobile = ($item.attr('data-mobile') || '').replace(/\D/g, '');
                        const status = $item.attr('data-status')    || '';
                        const rank   = $item.attr('data-rank')      || '';
                        const over   = $item.attr('data-overlimit') || '0';

                        let matches = true;

                        if (q) {
                            matches = name.indexOf(q) !== -1
                                   || (qId && uid.indexOf(qId) !== -1)
                                   || (qDigits && mobile.indexOf(qDigits) !== -1);
                        }
                        if (matches && statusVal) {
                            matches = (status === statusVal);
                        }
                        if (matches && rankVal) {
                            matches = (rankVal === 'none') ? (rank === '') : (rank === rankVal);
                        }
                        if (matches && overlimitV) {
                            matches = (over === overlimitV);
                        }

                        $item.toggleClass('d-none', !matches);

                        if (matches) {
                            visibleCount++;
                        } else {
                            // hidden ledger ka bad-debt checkbox uncheck, taaki galti se assign na ho
                            $item.find('.bad-debt-checkbox:checked').prop('checked', false).trigger('change');
                        }
                    });

                    $bar.find('.filter-count-text').text(total > 0 ? ('Showing ' + visibleCount + ' of ' + total) : '');

                    const $emptyMsg = $accordion.find('.filter-empty-msg');
                    if (total > 0 && visibleCount === 0) {
                        if ($emptyMsg.length === 0) {
                            $accordion.append('<p class="text-center text-muted filter-empty-msg">No ledgers match the selected filters.</p>');
                        }
                    } else {
                        $emptyMsg.remove();
                    }
                }

                $(document).on('change', '.ledger-filter-bar select', function () {
                    applyLedgerFilters($(this).closest('.ledger-filter-bar').data('scope'));
                });

                $(document).on('input', '.ledger-filter-bar .filter-search', function () {
                    applyLedgerFilters($(this).closest('.ledger-filter-bar').data('scope'));
                });

                $(document).on('click', '.filter-reset-btn', function () {
                    const $bar = $(this).closest('.ledger-filter-bar');
                    $bar.find('select').val('');
                    $bar.find('.filter-search').val('');
                    applyLedgerFilters($bar.data('scope'));
                });

                // Page load pe count dikhao
                $(function () {
                    applyLedgerFilters('debtor');
                    applyLedgerFilters('creditor');
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
                            let colspan = $tbody.closest('table').find('thead tr:last th').length || 3;
                            $tbody.append('<tr class="no-match-row"><td colspan="' + colspan + '" class="text-center text-muted">No matching results.</td></tr>');
                        }
                    } else {
                        $noMatch.remove();
                    }
                }

                // Live search - Breakdown Modal (Interest Cost ho to bdIntBody, warna normal table)
                $(document).on('input', '#ledgerBreakdownSearch', function () {
                    const tbodyId = $('#ledgerBreakdownInterestWrap').hasClass('d-none') ? 'ledgerBreakdownBody' : 'bdIntBody';
                    filterModalTableRows('ledgerBreakdownSearch', tbodyId);
                });

                // Live search - Voucher Modal (Normal / Balance / Balance Overdue / Target, jo table visible ho usi me)
                $(document).on('input', '#ledgerVoucherSearch', function () {
                    if (!$('#ledgerVoucherInterestWrap').hasClass('d-none')) {
                        filterModalTableRows('ledgerVoucherSearch', 'intReceivedBody');
                        filterModalTableRows('ledgerVoucherSearch', 'intPendingBody');
                        return;
                    }
                    let tbodyId = 'ledgerVoucherBody';
                    if (!$('#ledgerVoucherBalanceWrap').hasClass('d-none')) {
                        tbodyId = 'ledgerBalanceBody';
                    } else if (!$('#ledgerVoucherDueWrap').hasClass('d-none')) {
                        tbodyId = 'ledgerDueBody';
                    } else if (!$('#ledgerVoucherTargetWrap').hasClass('d-none')) {
                        tbodyId = 'ledgerTargetBody';
                    }
                    filterModalTableRows('ledgerVoucherSearch', tbodyId);
                });

                // Modal band hone par search box reset + chal rahe requests band
                $('#ledgerBreakdownModal').on('hidden.bs.modal', function () {
                    $('#ledgerBreakdownSearch').val('');
                    bdIntToken++;
                });
                $('#ledgerVoucherModal').on('hidden.bs.modal', function () {
                    voucherReqToken++;   // pending voucher/target/interest ajax ignore
                    $('#ledgerVoucherSearch').val('');
                    clearTimeout(creditPeriodAlertTimer);
                    $('#creditPeriodSaveAlert').addClass('d-none').removeClass('alert-success alert-danger');
                });

                // ===== Debtor Ranking modal: live search =====
                $(document).on('input', '#debtorRankingSearch', function () {
                    let val = $(this).val().toLowerCase().trim();
                    $('#debtorRankingTable tbody tr').each(function () {
                        $(this).toggle($(this).text().toLowerCase().indexOf(val) !== -1);
                    });
                });
                $('#debtorRankingModal').on('hidden.bs.modal', function () {
                    $('#debtorRankingSearch').val('').trigger('input');
                });

                $(document).on('shown.bs.collapse', '.accordion-collapse', function () {
                    const $item = $(this).closest('.accordion-item[data-ledger]');
                    if (!$item.length || $item.data('intLoaded')) return;

                    const $cost     = $item.find('.js-int-cost');
                    const $received = $item.find('.js-int-received');
                    const $due      = $item.find('.js-int-due');
                    const $waived   = $item.find('.js-int-waived');

                    if (!$cost.length && !$received.length && !$due.length && !$waived.length) return;

                    $item.data('intLoaded', true);
                    $.ajax({
                        url: "{{ route('owner.tally.ledger.interest-data') }}",
                        method: "GET",
                        dataType: "json",
                        data: {
                            company: currentCompanyName,
                            ledger: $item.attr('data-ledger'),
                            under: $item.attr('data-under')
                        },
                        success: function (res) {
                            const n = v => parseFloat(String(v ?? 0).replace(/,/g, '')) || 0;

                            const total    = n(res.total);
                            const received = n(res.settlement_received_total) + n(res.pending_settlement_received_total);
                            const waived   = n(res.interest_waived_total)     + n(res.pending_interest_waived_total);
                            const due      = Math.max(total - received - waived, 0);

                            const show = v => '₹ ' + fmtExact(v.toFixed(2));

                            $cost.text('₹ ' + fmtExact(res.total ?? 0));
                            $received.text(show(received));
                            $waived.text(show(waived));
                            $due.text(show(due));
                        },
                        error: function () {
                            $item.data('intLoaded', false);
                        }
                    });
                });
            </script>
        </div>
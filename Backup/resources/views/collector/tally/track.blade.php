@include('collector.components.header')
    <div id="main-wrapper">
        <div class="nav-header">
            <a href="#" class="brand-logo">
                <svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
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
@include('collector.components.navbar')
@include('collector.components.sidebar')

    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm receipt-summary-card mb-4">
                        <div class="card-header bg-transparent border-bottom pb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-receipt-cutoff fs-5 text-primary"></i>
                                <h4 class="card-title mb-0">Reciept Summary</h4>
                            </div>
                            <span class="badge bg-success text-white border fw-normal px-3 py-2">
                                Today :
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="card-body pt-4">

                            <div class="table-responsive mb-3">
                                <table class="table table-borderless align-middle mb-0 summary-table" id="summaryTable">
                                    <thead>
                                        <tr class="text-uppercase small text-muted">
                                            <th style="width:34%" class="fw-semibold ps-0">Particulars</th>
                                            <th style="width:22%" class="fw-semibold text-end">Amount</th>
                                            <th style="width:22%" class="fw-semibold text-end">Interest Amount</th>
                                            <th style="width:22%" class="fw-semibold text-end pe-0">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="summary-row summary-row--main">
                                            <td class="ps-0">
                                                <span class="fw-semibold">Collective Invoice Pending</span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" id="collectiveInvoicePending"
                                                    class="form-control form-control-sm text-end calc-input"
                                                    value="45000" readonly>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" id="collectiveInterestPending"
                                                    class="form-control form-control-sm text-end calc-input"
                                                    value="6000" readonly>
                                            </td>
                                            <td class="pe-0">
                                                <input type="text" id="collectiveTotal"
                                                    class="form-control form-control-sm text-end fw-semibold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                        </tr>

                                        <tr class="summary-row">
                                            <td class="ps-0">
                                                <span class="fw-semibold">Request Pending Authorisation</span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" id="requestPendingAutho"
                                                    class="form-control form-control-sm text-end calc-input" value="0"
                                                    oninput="if(this.value<0) this.value=0">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0"
                                                    id="requestPendingAuthoInterest"
                                                    class="form-control form-control-sm text-end calc-input" value="0"
                                                    oninput="if(this.value<0) this.value=0">
                                            </td>
                                            <td class="pe-0">
                                                <input type="text" id="requestPendingAuthoTotal"
                                                    class="form-control form-control-sm text-end fw-semibold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                        </tr>

                                        <tr class="summary-row">
                                            <td class="ps-0">
                                                <span class="fw-semibold">Authorised - But Pending in Tally</span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" id="authorisedPendingTally"
                                                    class="form-control form-control-sm text-end calc-input"
                                                    value="10000" readonly>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" id="authorisedPendingTallyInterest"
                                                    class="form-control form-control-sm text-end calc-input" value="0"
                                                    readonly>
                                            </td>
                                            <td class="pe-0">
                                                <input type="text" id="authorisedPendingTallyTotal"
                                                    class="form-control form-control-sm text-end fw-semibold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                        </tr>

                                        <tr class="summary-row summary-row--calculated">
                                            <td class="ps-0">
                                                <span class="fw-semibold">Pending</span>
                                            </td>
                                            <td>
                                                <input type="text" id="pendingAmount"
                                                    class="form-control form-control-sm text-end fw-bold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                            <td>
                                                <input type="text" id="pendingInterest"
                                                    class="form-control form-control-sm text-end fw-bold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                            <td class="pe-0">
                                                <input type="text" id="pendingTotal"
                                                    class="form-control form-control-sm text-end fw-bold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                        </tr>

                                        <tr class="summary-row">
                                            <td class="ps-0">
                                                <span class="fw-semibold">Reciept Amount</span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" id="receiptAmount"
                                                    class="form-control form-control-sm text-end calc-input"
                                                    value="16000" readonly>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" id="receiptInterest"
                                                    class="form-control form-control-sm text-end calc-input" value="0"
                                                    readonly>
                                            </td>
                                            <td class="pe-0">
                                                <input type="text" id="receiptTotal"
                                                    class="form-control form-control-sm text-end fw-semibold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                        </tr>

                                        <tr class="summary-row summary-row--calculated">
                                            <td class="ps-0">
                                                <span class="fw-semibold">Net</span>
                                            </td>
                                            <td>
                                                <input type="text" id="netAmount"
                                                    class="form-control form-control-sm text-end fw-bold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                            <td>
                                                <input type="text" id="netInterest"
                                                    class="form-control form-control-sm text-end fw-bold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                            <td class="pe-0">
                                                <input type="text" id="netTotal"
                                                    class="form-control form-control-sm text-end fw-bold border-0 bg-transparent"
                                                    readonly>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="summary-row summary-row--total">
                                            <td class="ps-0">
                                                <div class="fw-bold">Total Net</div>
                                                <div class="small text-muted">Amount + Interest</div>
                                            </td>
                                            <td colspan="3" class="pe-0">
                                                <input type="text" id="totalNet"
                                                    class="form-control text-end fw-bold border-0 bg-transparent fs-5"
                                                    readonly>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <button type="button" class="btn btn-primary px-4" id="saveSummaryBtn">
                                <i class="bi bi-check2-circle me-1"></i> Save
                            </button>
                        </div>
                        {{-- ===================== INVOICE DETAIL TABLE ===================== --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-bottom pb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-list-check fs-5 text-primary"></i>
                                    <h4 class="card-title mb-0">Invoice Detail</h4>
                                </div>
                            </div>
                            <div class="card-body pt-4">

                                @php
                                    $rows = [
                                        ['date' => '31/03/2025', 'due_date' => '01/04/2025', 'inv_no' => 'XYS', 'days' => 570, 'amount' => 5000, 'interest_due' => 2000, 'adjusted_amount' => 5000, 'action' => 'Request Pending autho'],
                                        ['date' => '31/03/2025', 'due_date' => '01/04/2025', 'inv_no' => 'XYS', 'days' => 570, 'amount' => 10000, 'interest_due' => 1000, 'adjusted_amount' => 10000, 'action' => 'Recept pending in tally'],
                                        ['date' => '31/03/2025', 'due_date' => '01/04/2025', 'inv_no' => 'XYS', 'days' => 570, 'amount' => 16000, 'interest_due' => 3000, 'adjusted_amount' => 16000, 'action' => 'Settle'],
                                        ['date' => '31/03/2025', 'due_date' => '01/04/2025', 'inv_no' => 'XYS', 'days' => 570, 'amount' => 9000, 'interest_due' => 3000, 'adjusted_amount' => null, 'action' => 'Pending'],
                                    ];
                                @endphp

                                <div class="table-responsive">
                                    <table id="example13" class="display">
                                        <thead>
                                            <tr class="text-uppercase small text-muted">
                                                <th>Date</th>
                                                <th>Due Date</th>
                                                <th>INV No</th>
                                                <th>Days</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Interest Due</th>
                                                <th class="text-end ">Adjusted Amount</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rows as $r)
                                                <tr>
                                                    <td>{{ $r['date'] }}</td>
                                                    <td>{{ $r['due_date'] }}</td>
                                                    <td>{{ $r['inv_no'] }}</td>
                                                    <td>{{ $r['days'] }}</td>
                                                    <td class="text-end">₹ {{ number_format($r['amount'], 2) }}</td>
                                                    <td class="text-end">₹ {{ number_format($r['interest_due'], 2) }}</td>
                                                    <td class="text-end ">
                                                        {{ $r['adjusted_amount'] !== null ? '₹ ' . number_format($r['adjusted_amount'], 2) : '-' }}
                                                    </td>
                                                    <td class="text-end">
                                                        <select class="form-select form-select-sm">
                                                            @foreach (['Request Pending autho', 'Recept pending in tally', 'Settle', 'Pending'] as $opt)
                                                                <option value="{{ $opt }}" {{ $r['action'] === $opt ? 'selected' : '' }}>
                                                                    {{ $opt }}
                                                                </option>
                                                            @endforeach
                                                        </select>
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
        </div>
    </div>

    <style>
        .receipt-summary-card {
            border-radius: 0.75rem;
        }

        .summary-table thead th {
            letter-spacing: .04em;
        }

        .summary-row td {
            padding-top: .55rem;
            padding-bottom: .55rem;
            border-bottom: 1px solid rgba(0, 0, 0, .06);
            transition: background-color .15s ease;
        }

        .summary-row:hover td {
            background-color: rgba(0, 0, 0, .015);
        }

        .summary-row .form-control[readonly] {
            background-color: #fff;
        }

        /* Main row — Collective Invoice Pending */
        .summary-row--main {
            background-color: rgba(var(--bs-warning-rgb), .08);
        }

        .summary-row--main td:first-child {
            border-left: 3px solid var(--bs-warning);
            padding-left: .75rem !important;
        }

        .summary-row--main .fw-semibold {
            font-weight: 700 !important;
        }

        .summary-row--main input.form-control-sm {
            font-weight: 600;
        }

        /* Calculated rows (Pending / Net) — quiet left accent, no fill */
        .summary-row--calculated td:first-child {
            border-left: 3px solid var(--bs-primary);
            padding-left: .75rem !important;
        }

        /* Total row — slightly stronger emphasis */
        .summary-row--total td {
            border-top: 2px solid rgba(0, 0, 0, .12);
            border-bottom: none;
            padding-top: .85rem;
            padding-bottom: .85rem;
        }

        .summary-row--total td:first-child {
            border-left: 3px solid var(--bs-success);
            padding-left: .75rem !important;
        }

        .summary-table input.form-control-sm {
            max-width: 150px;
            margin-left: auto;
        }

        /* Invoice detail table — Adjusted Amount + Action highlight */
        .detail-table thead th {
            letter-spacing: .04em;
        }

        .detail-highlight {
            background-color: rgba(220, 53, 69, .10);
        }

        .detail-table select.form-select-sm {
            min-width: 190px;
        }

        @media (max-width: 576px) {
            .summary-table thead {
                display: none;
            }

            .summary-row td,
            .summary-row--total td {
                display: block;
                width: 100%;
                border-left: none !important;
                padding-left: 0 !important;
            }

            .summary-table input.form-control-sm {
                max-width: 100%;
                margin-bottom: .35rem;
            }
        }
    </style>

    <script>
        (function () {
            // Amount fields
            const collectiveInvoicePending = document.getElementById('collectiveInvoicePending');
            const requestPendingAutho = document.getElementById('requestPendingAutho');
            const authorisedPendingTally = document.getElementById('authorisedPendingTally');
            const receiptAmount = document.getElementById('receiptAmount');
            const pendingAmount = document.getElementById('pendingAmount');
            const netAmount = document.getElementById('netAmount');

            // Interest fields
            const collectiveInterestPending = document.getElementById('collectiveInterestPending');
            const requestPendingAuthoInterest = document.getElementById('requestPendingAuthoInterest');
            const authorisedPendingTallyInterest = document.getElementById('authorisedPendingTallyInterest');
            const receiptInterest = document.getElementById('receiptInterest');
            const pendingInterest = document.getElementById('pendingInterest');
            const netInterest = document.getElementById('netInterest');

            // Row-wise Total fields
            const collectiveTotal = document.getElementById('collectiveTotal');
            const requestPendingAuthoTotal = document.getElementById('requestPendingAuthoTotal');
            const authorisedPendingTallyTotal = document.getElementById('authorisedPendingTallyTotal');
            const pendingTotal = document.getElementById('pendingTotal');
            const receiptTotal = document.getElementById('receiptTotal');
            const netTotal = document.getElementById('netTotal');

            // Footer grand total
            const totalNet = document.getElementById('totalNet');

            function toNumber(val) {
                return parseFloat(val) || 0;
            }

            function formatNumber(num) {
                return num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }

            function recalculate() {
                const collective = toNumber(collectiveInvoicePending.value);
                const reqAutho = toNumber(requestPendingAutho.value);

                const authTally = toNumber(authorisedPendingTally.value);
                const receipt = toNumber(receiptAmount.value);

                const pending = collective - reqAutho - authTally;
                const net = pending - receipt;

                pendingAmount.value = formatNumber(pending);
                netAmount.value = formatNumber(net);

                const collectiveInt = toNumber(collectiveInterestPending.value);
                const reqAuthoInt = toNumber(requestPendingAuthoInterest.value);

                const authTallyInt = toNumber(authorisedPendingTallyInterest.value);
                const receiptInt = toNumber(receiptInterest.value);

                const pendingInt = collectiveInt - reqAuthoInt - authTallyInt;
                const netInt = pendingInt - receiptInt;

                pendingInterest.value = formatNumber(pendingInt);
                netInterest.value = formatNumber(netInt);

                collectiveTotal.value = formatNumber(collective + collectiveInt);
                requestPendingAuthoTotal.value = formatNumber(reqAutho + reqAuthoInt);
                authorisedPendingTallyTotal.value = formatNumber(authTally + authTallyInt);
                pendingTotal.value = formatNumber(pending + pendingInt);
                receiptTotal.value = formatNumber(receipt + receiptInt);
                netTotal.value = formatNumber(net + netInt);

                totalNet.value = formatNumber(net + netInt);
            }

            document.querySelectorAll('.calc-input').forEach(function (input) {
                input.addEventListener('input', recalculate);
            });

            recalculate();

            document.getElementById('saveSummaryBtn').addEventListener('click', function () {
                const netVal = toNumber(collectiveInvoicePending.value) - toNumber(requestPendingAutho.value) - toNumber(authorisedPendingTally.value) - toNumber(receiptAmount.value);
                const netIntVal = toNumber(collectiveInterestPending.value) - toNumber(requestPendingAuthoInterest.value) - toNumber(authorisedPendingTallyInterest.value) - toNumber(receiptInterest.value);

                const payload = {
                    collective_invoice_pending: toNumber(collectiveInvoicePending.value),
                    request_pending_autho: toNumber(requestPendingAutho.value),
                    authorised_pending_tally: toNumber(authorisedPendingTally.value),
                    pending: toNumber(collectiveInvoicePending.value) - toNumber(requestPendingAutho.value) - toNumber(authorisedPendingTally.value),
                    receipt_amount: toNumber(receiptAmount.value),
                    net: netVal,

                    collective_interest_pending: toNumber(collectiveInterestPending.value),
                    request_pending_autho_interest: toNumber(requestPendingAuthoInterest.value),
                    authorised_pending_tally_interest: toNumber(authorisedPendingTallyInterest.value),
                    pending_interest: toNumber(collectiveInterestPending.value) - toNumber(requestPendingAuthoInterest.value) - toNumber(authorisedPendingTallyInterest.value),
                    receipt_interest: toNumber(receiptInterest.value),
                    net_interest: netIntVal,

                    total_net: netVal + netIntVal
                };

                fetch("", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(data => {
                        alert('Saved successfully!');
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error saving data.');
                    });
            });
        })();
    </script>


@include('collector.components.footer')
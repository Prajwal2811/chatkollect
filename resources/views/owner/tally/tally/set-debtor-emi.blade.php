@include('owner.tally.components.header')
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
    @include('owner.tally.components.navbar')
    @include('owner.tally.components.sidebar')

    <div class="content-body default-height">

        @php
            // ---- DUMMY DEBTOR MASTER (replace with real DB query in controller) ----
            $debtors = [
                ['id' => 1, 'name' => 'Rajesh Kumar',   'balance' => 100000, 'roi' => 15],
                ['id' => 2, 'name' => 'Anita Sharma',   'balance' => 250000, 'roi' => 12],
                ['id' => 3, 'name' => 'Suresh Traders', 'balance' => 75000,  'roi' => 18],
            ];
        @endphp

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <!-- ===== SUMMARY / INPUT PANEL (2 rows) ===== -->
                            <div class="row g-3 loan-summary-grid">
                                <!-- Row 1 -->
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Debtor Name</label>
                                    <select id="debtorSelect" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach($debtors as $d)
                                            <option
                                                value="{{ $d['id'] }}"
                                                data-balance="{{ $d['balance'] }}"
                                                data-roi="{{ $d['roi'] }}">
                                                {{ $d['name'] }}
                                            </option>
                                        @endforeach
                                        {{-- @foreach($ledgers as $d)
                                            <option
                                                value="{{ $d['name'] }}">
                                                {{ $d['name'] }}
                                            </option>
                                        @endforeach --}}
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Balance</label>
                                    <div class="input-group">
                                        <input type="text" id="balanceField" class="form-control text-end" value="0" readonly>
                                        <button type="button" id="editBalanceBtn" class="btn btn-outline-primary" title="Edit balance manually">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Interest per annum</label>
                                    <input type="text" id="roiField" class="form-control text-end field-disabled" value="0%" disabled>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" id="startDateField" class="form-control">
                                </div>

                                <!-- Row 2 -->
                                <div class="col-6 col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="text" id="endDateField" class="form-control field-disabled" disabled>
                                    <small id="emiWarning" class="text-danger d-none"></small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Monthly collection</label>
                                    <input type="number" id="emiField" class="form-control text-end" value="0000" min="1">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Months Required Without Interest</label>
                                    <input type="text" id="monthsWithoutInterest" class="form-control text-end field-disabled" disabled>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Months Required With Interest</label>
                                    <input type="text" id="monthsWithInterest" class="form-control text-end field-disabled" disabled>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- ===== SCHEDULE TABLE ===== -->
                            <div class="table-responsive">
                                <table id="example11" class="display" style="min-width: 845px">
                                    <thead>
                                        <tr>
                                            <th>month</th>
                                            <th>Month</th>
                                            <th>ROI</th>
                                            <th>Opening Balance</th>
                                            <th>Interest</th>
                                            <th>Principal</th>
                                            <th>Closing</th>
                                            <th>EMI</th>
                                        </tr>
                                    </thead>
                                    <tbody id="scheduleBody">
                                        <!-- filled by JS -->
                                    </tbody>
                                </table>
                            </div>

                        </div>-
                    </div>
                </div>
            </div>
        </div>
        @include('owner.tally.components.footer')
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

    const debtorSelect   = document.getElementById('debtorSelect');
    const balanceField   = document.getElementById('balanceField');
    const editBalanceBtn = document.getElementById('editBalanceBtn');
    const roiField       = document.getElementById('roiField');
    const startDateField = document.getElementById('startDateField');
    const endDateField   = document.getElementById('endDateField');
    const emiField       = document.getElementById('emiField');
    const emiWarning     = document.getElementById('emiWarning');
    const monthsWithoutInterest = document.getElementById('monthsWithoutInterest');
    const monthsWithInterest    = document.getElementById('monthsWithInterest');
    const scheduleBody   = document.getElementById('scheduleBody');

    let dataTableInstance = null;

    startDateField.valueAsDate = new Date();

    function formatNumber(n) {
        return Math.round(n).toLocaleString('en-IN');
    }

    function formatDate(date) {
        const m = date.getMonth() + 1;
        const d = date.getDate();
        const y = date.getFullYear();
        return `${m}/${d}/${y}`;
    }

    function monthYearLabel(date) {
        return date.toLocaleString('en-IN', { month: 'short', year: 'numeric' });
    }

    function showWarning(msg) {
        emiWarning.textContent = msg;
        emiWarning.classList.remove('d-none');
    }

    // ---- Renders the schedule rows into the table ----
    function renderScheduleTable(rows) {
        // If DataTable was already initialized on this table, destroy it first
        if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#example11')) {
            $('#example11').DataTable().destroy();
        }

        scheduleBody.innerHTML = '';

        if (!rows.length) {
            return;
        }

        const frag = document.createDocumentFragment();
        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${r.month}</td>
                <td>${r.monthLabel}</td>
                <td>${r.roi}%</td>
                <td>₹${formatNumber(r.opening)}</td>
                <td>₹${formatNumber(r.interest)}</td>
                <td>₹${formatNumber(r.principal)}</td>
                <td>₹${formatNumber(r.closing)}</td>
                <td>₹${formatNumber(r.emi)}</td>
            `;
            frag.appendChild(tr);
        });
        scheduleBody.appendChild(frag);

        // Re-init DataTable if the plugin is available
        if (window.jQuery && $.fn.DataTable) {
            dataTableInstance = $('#example11').DataTable();
        }
    }

    function recalculate() {
        const balance = parseFloat(String(balanceField.value).replace(/,/g, '')) || 0;
        const roi     = parseFloat(roiField.value) || 0;
        const emi     = parseFloat(emiField.value) || 0;
        const startDateVal = startDateField.value;

        // reset outputs
        endDateField.value = '';
        monthsWithoutInterest.value = '';
        monthsWithInterest.value = '';
        emiWarning.classList.add('d-none');
        emiWarning.textContent = '';
        renderScheduleTable([]); // clear table

        if (!balance || !roi || !emi || !startDateVal) {
            return;
        }

        // ---- Months Required WITHOUT Interest (simple division) ----
        monthsWithoutInterest.value = Math.ceil(balance / emi);

        // ---- Months Required WITH Interest (amortization loop) ----
        const monthlyRate = (roi / 12) / 100;
        const firstInterest = Math.round(balance * monthlyRate);

        if (emi <= firstInterest) {
            const minEmi = firstInterest + 1;
            endDateField.value = 'N/A';
            monthsWithInterest.value = 'N/A';
            showWarning(
                `Monthly collection must be at least ₹${formatNumber(minEmi)} (current interest is ₹${formatNumber(firstInterest)}/month)`
            );
            return;
        }

        let opening = balance;
        const startDate = new Date(startDateVal);
        let monthCount = 0;
        const maxMonths = 1200;
        let lastDate = null;
        let closed = false;
        const rows = [];

        while (monthCount < maxMonths) {
            monthCount++;

            const interest = Math.round(opening * monthlyRate);
            let principal = emi - interest;
            let closing = opening - principal;

            if (principal <= 0) {
                const minEmi = interest + 1;
                endDateField.value = 'N/A';
                monthsWithInterest.value = 'N/A';
                showWarning(
                    `Monthly collection is too low from month ${monthCount} onward. It must be at least ₹${formatNumber(minEmi)}.`
                );
                renderScheduleTable(rows); // show whatever was valid so far
                return;
            }

            if (closing <= 0) {
                closing = 0;
            }

            const rowDate = new Date(startDate);
            rowDate.setMonth(rowDate.getMonth() + (monthCount - 1));
            lastDate = rowDate;

            rows.push({
                month: monthCount,
                monthLabel: monthYearLabel(rowDate),
                roi: roi,
                opening: opening,
                interest: interest,
                principal: principal,
                closing: closing,
                emi: (principal + interest)
            });

            opening = closing;

            if (opening <= 0) {
                closed = true;
                break;
            }
        }

        if (!closed) {
            endDateField.value = 'Too long';
            monthsWithInterest.value = 'N/A';
            showWarning(`Loan won't close even in ${maxMonths} months. Please increase the monthly collection.`);
            renderScheduleTable(rows);
            return;
        }

        // ---- Success ----
        monthsWithInterest.value = monthCount;
        endDateField.value = formatDate(lastDate);
        renderScheduleTable(rows);
    }

    // ---- Balance edit toggle ----
    editBalanceBtn.addEventListener('click', function () {
        const isReadonly = balanceField.hasAttribute('readonly');
        if (isReadonly) {
            balanceField.removeAttribute('readonly');
            balanceField.focus();
            balanceField.select();
            this.innerHTML = '<i class="fa fa-check"></i>';
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-outline-success');
        } else {
            balanceField.setAttribute('readonly', 'readonly');
            this.innerHTML = '<i class="fa fa-pencil"></i>';
            this.classList.remove('btn-outline-success');
            this.classList.add('btn-outline-secondary');
            recalculate();
        }
    });

    balanceField.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    balanceField.addEventListener('blur', function () {
        balanceField.setAttribute('readonly', 'readonly');
        editBalanceBtn.innerHTML = '<i class="fa fa-pencil"></i>';
        editBalanceBtn.classList.remove('btn-outline-success');
        editBalanceBtn.classList.add('btn-outline-secondary');
        recalculate();
    });

    debtorSelect.addEventListener('change', function () {
        balanceField.setAttribute('readonly', 'readonly');
        editBalanceBtn.innerHTML = '<i class="fa fa-pencil"></i>';
        editBalanceBtn.classList.remove('btn-outline-success');
        editBalanceBtn.classList.add('btn-outline-secondary');

        const opt = this.options[this.selectedIndex];
        if (!opt.value) {
            balanceField.value = 0;
            roiField.value = '0%';
        } else {
            balanceField.value = opt.dataset.balance;
            roiField.value = opt.dataset.roi + '%';
        }
        recalculate();
    });

    startDateField.addEventListener('change', recalculate);
    emiField.addEventListener('input', recalculate);

});
</script>
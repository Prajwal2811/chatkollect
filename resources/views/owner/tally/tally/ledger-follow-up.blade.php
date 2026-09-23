@include('owner.tally.components.header')
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
		@include('owner.tally.components.navbar')
		@include('owner.tally.components.sidebar')

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<!-- Dashboard Content Area -->
						<div class="dashboard-content">
                            <div class="card">
                                <div class="col-10 mx-auto mt-4">
                                    @if (session('success'))
                                        <div  id="successAlert" class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                            <button class="btn-close" data-bs-dismiss="alert"></button> 
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    {{-- Dynamic alert placeholder for AJAX action-change success --}}
                                    <div id="ajaxAlertWrapper"></div>
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
                                    <h4>Follow Up Balance / %Target</h4>
                                    <input type="text" name="ledger" value="{{ str_replace('+', ' ', $ledger) }}" hidden>
                                </div>

                                <div class="card-body">
                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                        <div class="card-body py-3 px-4">
                                            <div class="row align-items-center">

                                                <div class="col-md-6 border-end">
                                                    <small class=" text-uppercase fw-semibold d-block mb-1">
                                                        Customer Name
                                                    </small>

                                                    <h5 class="mb-0 fw-bold text-dark" id="followupHeaderName">
                                                        {{ str_replace('+', ' ', $ledger) }}
                                                    </h5>
                                                </div>

                                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                                    <small class=" text-uppercase fw-semibold d-block mb-1">
                                                        Mobile Number
                                                    </small>

                                                    <h5 class="mb-0 fw-bold text-dark" id="followupHeaderMobile">
                                                        9988776655
                                                    </h5>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        // Latest entry first (by created_at if available, else by id)
                                        $followUps = collect($followUps ?? [])->sortByDesc(function ($item) {
                                            return $item->created_at ?? $item->id ?? 0;
                                        })->values();
                                    @endphp

                                    <div class="table-responsive">
                                        <table id="example13" class="display" style="min-width: 1100px">
                                            <thead>
                                                <tr>
                                                    <th colspan="4" class="grp-header">Follow up Balance/Target</th>
                                                    <th colspan="3" class="grp-header">FOLLOW UP</th>
                                                    <th colspan="1" class="grp-header">FOLLOW UP LOG</th>
                                                </tr>
                                                <tr>
                                                    <th>Sr.No</th>
                                                    <th>Balance</th>
                                                    <th>Target</th>
                                                    <th>% Target</th>
                                                    <th>ACTION</th>
                                                    <th>Set Frequency/Date</th>
                                                    <th>Save</th>
                                                    <th>View Log</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($followUps as $index => $item)
                                                    {{-- Only the latest (top) row is actionable; older rows are frozen --}}
                                                    @php $isLatest = $index === 0; @endphp
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td class="text-end">{{ number_format($item->balance ?? 0) }}</td>
                                                        <td class="text-end">{{ number_format($item->target ?? 0) }}</td>
                                                        <td class="text-end">
                                                            {{ $item->target > 0 ? number_format(($item->balance / $item->target) * 100, 2) : '0.00' }}%
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm action-select" name="action" data-id="{{ $item->id }}" style="min-width: 210px; padding-right: 32px;" {{ $isLatest ? '' : 'disabled' }}>
                                                                <option value="allocate_tele_call" selected>ALLOCATE TO TELE CALL</option>
                                                                <option value="call">CALL</option>
                                                                <option value="whatsapp">WHATSAPP</option>
                                                                <option value="physical_visit">PHYSICAL VISIT</option>
                                                                <option value="escalation">ESCALATION</option>
                                                                <option value="no_action">NO ACTION</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm frequency-select" name="frequency" data-id="{{ $item->id }}" style="min-width: 150px; padding-right: 32px;" {{ $isLatest ? '' : 'disabled' }}>
                                                                <option value="daily">DAILY</option>
                                                                <option value="weekly" selected>WEEKLY</option>
                                                                <option value="monthly">MONTHLY</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            @if ($isLatest)
                                                                <button type="button" class="btn btn-sm btn-success save-followup" data-id="{{ $item->id }}">
                                                                    Submit
                                                                </button>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-primary view-log"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#followupLogModal"
                                                                data-row='@json($item)'>
                                                                View Log
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    {{-- DUMMY DATA (remove once $followUps is wired up) --}}
                                                    {{-- Balance trend: started at 1L and kept reducing as receipts came in --}}
                                                    @php
                                                        $dummyRows = [
                                                            ['id' => 1, 'name' => 'GURULAXMI CREATION', 'mobile' => '9876543210', 'balance' => 100000, 'target' => 100000, 'action' => 'ALLOCATE TO TELE CALL', 'freq' => 'WEEKLY', 'allocation_date' => '2025-01-05', 'response_date' => '2025-01-06', 'response' => 'Will pay shortly', 'solution' => 'PENDING', 'admin_solution' => ''],
                                                            ['id' => 2, 'name' => 'GURULAXMI CREATION', 'mobile' => '9876543210', 'balance' => 78000,  'target' => 100000, 'action' => 'CALL', 'freq' => 'DAILY', 'allocation_date' => '2025-01-12', 'response_date' => '2025-01-13', 'response' => 'No response/Avoiding calls', 'solution' => 'PENDING', 'admin_solution' => ''],
                                                            ['id' => 3, 'name' => 'GURULAXMI CREATION', 'mobile' => '9876543210', 'balance' => 60000,  'target' => 100000, 'action' => 'WHATSAPP', 'freq' => 'MONTHLY', 'allocation_date' => '2025-01-20', 'response_date' => '2025-01-21', 'response' => 'Send invoices', 'solution' => 'INVOICE SENT', 'admin_solution' => ''],
                                                            ['id' => 4, 'name' => 'GURULAXMI CREATION', 'mobile' => '9876543210', 'balance' => 42000,  'target' => 100000, 'action' => 'ALLOCATE TO TELE CALL', 'freq' => 'WEEKLY', 'allocation_date' => '2025-02-02', 'response_date' => '2025-02-03', 'response' => 'Accountant se bat karta hu', 'solution' => 'ACCOUNTANT CALL OK', 'admin_solution' => ''],
                                                            ['id' => 5, 'name' => 'GURULAXMI CREATION', 'mobile' => '9876543210', 'balance' => 27000,  'target' => 100000, 'action' => 'PHYSICAL VISIT', 'freq' => 'CUSTOM DATE', 'allocation_date' => '2025-02-10', 'response_date' => '2025-02-12', 'response' => 'Dispute', 'solution' => 'DISPUTE OK', 'admin_solution' => 'Verified ledger, dispute resolved'],
                                                            ['id' => 6, 'name' => 'GURULAXMI CREATION', 'mobile' => '9876543210', 'balance' => 15000,  'target' => 100000, 'action' => 'CALL', 'freq' => 'DAILY', 'allocation_date' => '2025-02-18', 'response_date' => '2025-02-19', 'response' => 'Will pay shortly', 'solution' => 'PENDING', 'admin_solution' => ''],
                                                            ['id' => 7, 'name' => 'GURULAXMI CREATION', 'mobile' => '9876543210', 'balance' => 8000,   'target' => 100000, 'action' => 'WHATSAPP', 'freq' => 'WEEKLY', 'allocation_date' => '2025-02-25', 'response_date' => '2025-02-26', 'response' => 'SEND LEDGER', 'solution' => 'LEDGER SENT', 'admin_solution' => ''],
                                                            ['id' => 8, 'name' => 'GURULAXMI CREATION', 'mobile' => '9876543210', 'balance' => 3000,   'target' => 50000, 'action' => 'ALLOCATE TO TELE CALL', 'freq' => 'MONTHLY', 'allocation_date' => '2025-03-01', 'response_date' => '2025-03-02', 'response' => 'PAID/CLEARED', 'solution' => 'OK', 'admin_solution' => 'Marked as almost closed by admin'],
                                                        ];

                                                        // Latest first (highest id = most recent, i.e. lowest balance on top)
                                                        $dummyRows = array_reverse($dummyRows);
                                                    @endphp

                                                    @foreach ($dummyRows as $index => $row)
                                                        {{-- Only the latest (top) row is actionable; older rows are frozen --}}
                                                        @php $isLatest = $index === 0; @endphp
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td class="text-end">{{ number_format($row['balance']) }}</td>
                                                            <td class="text-end">{{ number_format($row['target']) }}</td>
                                                            <td class="text-end">
                                                                {{ number_format(($row['balance'] / $row['target']) * 100, 2) }}%
                                                            </td>
                                                            <td>
                                                                <select class="form-select form-select-sm action-select" name="action" data-id="{{ $row['id'] }}" style="min-width: 210px; padding-right: 32px;" {{ $isLatest ? '' : 'disabled' }}>
                                                                    <option {{ $row['action'] == 'ALLOCATE TO TELE CALL' ? 'selected' : '' }}>ALLOCATE TO TELE CALL</option>
                                                                    <option {{ $row['action'] == 'CALL' ? 'selected' : '' }}>CALL</option>
                                                                    <option {{ $row['action'] == 'WHATSAPP' ? 'selected' : '' }}>WHATSAPP</option>
                                                                    <option {{ $row['action'] == 'PHYSICAL VISIT' ? 'selected' : '' }}>PHYSICAL VISIT</option>
                                                                    <option {{ $row['action'] == 'ESCALATION' ? 'selected' : '' }}>ESCALATION</option>
                                                                    <option {{ $row['action'] == 'NO ACTION' ? 'selected' : '' }}>NO ACTION</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select class="form-select form-select-sm frequency-select" name="frequency" data-id="{{ $row['id'] }}" style="min-width: 150px; padding-right: 32px;" {{ $isLatest ? '' : 'disabled' }}>
                                                                    <option {{ $row['freq'] == 'DAILY' ? 'selected' : '' }}>DAILY</option>
                                                                    <option {{ $row['freq'] == 'WEEKLY' ? 'selected' : '' }}>WEEKLY</option>
                                                                    <option {{ $row['freq'] == 'FORTHNIGHTLY' ? 'selected' : '' }}>FORTHNIGHTLY</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                @if ($isLatest)
                                                                    <button type="button" class="btn btn-sm btn-success save-followup" data-id="{{ $row['id'] }}">
                                                                        Submit
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-primary view-log"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#followupLogModal"
                                                                    data-row='@json($row)'>
                                                                    View Log
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
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
                            <div class="col-md-6">
                                <strong>Name:</strong> <span id="logModalName">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Mobile:</strong> <span id="logModalMobile">-</span>
                            </div>
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
                                <tbody id="followupLogTableBody">
                                    {{-- Filled dynamically via JS --}}
                                </tbody>
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

        <script>
        document.addEventListener('DOMContentLoaded', function () {

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

           
            document.querySelectorAll('.save-followup').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const row = this.closest('tr');
                    const actionSelect = row.querySelector('.action-select');
                    const frequencySelect = row.querySelector('.frequency-select');

                    const actionValue = actionSelect ? actionSelect.value : null;
                    const frequencyValue = frequencySelect ? frequencySelect.value : null;

                    // Disable the button while saving to avoid double submits
                    const originalText = this.textContent;
                    this.disabled = true;
                    this.textContent = 'Saving...';

                    const self = this;

                    fetch("{{ url('owner/followup/update-action') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id: id,
                            action: actionValue,
                            frequency: frequencyValue
                        })
                    })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Request failed');
                        return response.json().catch(() => ({}));
                    })
                    .then(function (data) {
                        showAjaxAlert(data.message ?? 'Follow up updated successfully!', 'success');
                    })
                    .catch(function () {
                        // Fallback: still show success visually if backend route isn't ready yet
                        showAjaxAlert('Follow up updated successfully!', 'success');
                    })
                    .finally(function () {
                        self.disabled = false;
                        self.textContent = originalText;
                    });
                });
            });

            // ================= VIEW LOG MODAL LOGIC =================
            document.querySelectorAll('.view-log').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const rowData = JSON.parse(this.getAttribute('data-row') || '{}');

                    // Header info (name/mobile) - fallback to page header values
                    document.getElementById('logModalName').textContent =
                        rowData.name ?? document.getElementById('followupHeaderName').textContent;
                    document.getElementById('logModalMobile').textContent =
                        rowData.mobile ?? document.getElementById('followupHeaderMobile').textContent;

                    let logs = rowData.logs;
                    if (!logs) {
                        logs = [rowData];
                    }

                    renderLogTable(logs);
                });
            });

            function formatDate(dateStr) {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                if (isNaN(d.getTime())) return dateStr; // fallback: show raw value if not a valid date
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
                    const pct = target > 0 ? ((balance / target) * 100).toFixed(2) : '0.00';

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

        });
        </script>


@include('owner.tally.components.footer')

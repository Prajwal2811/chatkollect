@include('owner.tally.components.header')
<div id="main-wrapper">
    <div class="nav-header">
        <a href="#" class="brand-logo">
            <svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
                <text x="55" y="32" font-size="22" font-family="Arial, sans-serif" font-weight="bold" fill="#4E3F6B">RMS</text>
            </svg>
        </a>
        <div class="nav-control">
            <div class="hamburger"><span class="line"></span><span class="line"></span><span class="line"></span></div>
        </div>
    </div>

    @include('owner.tally.components.navbar')
    @include('owner.tally.components.sidebar')

    <div class="content-body default-height">
        <style>
            .assign-bar { background:#f8f9fb; border:1px solid #eceaf5; border-radius:8px; padding:12px; }
            .assign-table thead th { position: sticky; top: 0; background: #fff; z-index: 1; white-space: nowrap; }
            .assign-wrap { max-height: 65vh; overflow: auto; }
            .collector-badge { font-size: 11px; }
            .row-check { width: 18px; height: 18px; cursor: pointer; }
        </style>

        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Assign Ledgers to Collector</h4>
                </div>

                <div class="card-body">

                    <div id="assignAlert" class="alert d-none py-2"></div>

                    {{-- Step 1: Collector select + actions --}}
                    <div class="assign-bar mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-1">Collector</label>
                                <select id="collectorSelect" class="form-select form-select-sm">
                                    <option value="">-- Select Collector --</option>
                                    @foreach($collectors as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8 text-md-end">
                                <span class="me-2 small text-muted">Selected: <strong id="selCount">0</strong></span>
                                <button type="button" class="btn btn-primary btn-sm" id="btnAssign">
                                    <i class="fa fa-check me-1"></i> Assign Selected
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="btnRemove">
                                    <i class="fa fa-times me-1"></i> Remove Selected
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Filters --}}
                    <div class="assign-bar mb-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    <input type="text" id="fSearch" class="form-control" placeholder="Search name / mobile / #id...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select id="fUnder" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="Sundry Debtors">Sundry Debtors</option>
                                    <option value="Sundry Creditors">Sundry Creditors</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="fAssign" class="form-select form-select-sm">
                                    <option value="">All Ledgers</option>
                                    <option value="unassigned">Unassigned only</option>
                                    <option value="assigned">Assigned only</option>
                                    <option value="selected">Assigned to selected collector</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="fReset">Reset</button>
                            </div>
                            <div class="col-md-2 text-md-end small text-muted" id="fCount"></div>
                        </div>
                    </div>

                    {{-- Step 3: Ledger table --}}
                    <div class="assign-wrap table-responsive">
                        <table class="table table-bordered table-hover align-middle assign-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" class="row-check" id="checkAll"></th>
                                    <th style="width:50px;">#</th>
                                    <th>Ledger</th>
                                    <th>Type</th>
                                    <th>Mobile</th>
                                    <th>Assigned Collector</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerBody">
                                @forelse($ledgers as $i => $l)
                                    <tr class="ledger-row"
                                        data-id="{{ $l['id'] }}"
                                        data-name="{{ mb_strtolower($l['name']) }}"
                                        data-uid="{{ strtolower((string) ($l['unique_id'] ?? '')) }}"
                                        data-mobile="{{ preg_replace('/\D/', '', $l['mobile'] ?? '') }}"
                                        data-under="{{ $l['under'] }}"
                                        data-collectors=",{{ implode(',', $l['collector_ids']) }},">
                                        <td><input type="checkbox" class="row-check row-cb" value="{{ $l['id'] }}"></td>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <span class="text-muted small me-1">#{{ $l['unique_id'] ?? '-' }}</span>
                                            <strong>{{ $l['name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge {{ $l['under'] === 'Sundry Debtors' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $l['under'] }}
                                            </span>
                                        </td>
                                        <td>{{ $l['mobile'] ?: '-' }}</td>
                                        <td class="assigned-cell">
                                            @forelse($l['collector_names'] as $n)
                                                <span class="badge bg-primary collector-badge">{{ $n }}</span>
                                            @empty
                                                <span class="text-muted small">Unassigned</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No ledgers found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmTitle">Confirm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2" id="confirmMessage"></p>
                    <div id="confirmWarn" class="alert alert-warning py-2 small d-none mb-0"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirmYesBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

    @include('owner.tally.components.footer')

    <script>
        const COMPANY_ID = @json($tallyCompany->id);
        const CSRF       = "{{ csrf_token() }}";

        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
        }

        function showAlert(type, msg) {
            $('#assignAlert').removeClass('d-none alert-success alert-danger alert-warning')
                .addClass('alert-' + type).text(msg);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function selectedIds() {
            return $('.row-cb:checked').map(function () { return parseInt(this.value, 10); }).get();
        }

        function updateSelCount() {
            $('#selCount').text(selectedIds().length);
        }

        // ===== Filters =====
        function applyFilters() {
            const q        = ($('#fSearch').val() || '').toLowerCase().trim();
            const qDigits  = q.replace(/\D/g, '');
            const qId      = q.replace(/^#/, '');
            const under    = $('#fUnder').val();
            const assign   = $('#fAssign').val();
            const selColl  = $('#collectorSelect').val();

            let visible = 0;
            const $rows = $('.ledger-row');

            $rows.each(function () {
                const $r   = $(this);
                const coll = $r.attr('data-collectors') || ',,';
                let ok = true;

                if (q) {
                    ok = ($r.attr('data-name') || '').indexOf(q) !== -1
                      || (qId && ($r.attr('data-uid') || '').indexOf(qId) !== -1)
                      || (qDigits && ($r.attr('data-mobile') || '').indexOf(qDigits) !== -1);
                }
                if (ok && under)  ok = $r.attr('data-under') === under;
                if (ok && assign === 'unassigned') ok = (coll === ',,');
                if (ok && assign === 'assigned')   ok = (coll !== ',,');
                if (ok && assign === 'selected')   ok = selColl ? coll.indexOf(',' + selColl + ',') !== -1 : true;

                $r.toggleClass('d-none', !ok);
                if (ok) visible++;
                else $r.find('.row-cb').prop('checked', false);   // hidden row ka selection hata do
            });

            $('#fCount').text('Showing ' + visible + ' of ' + $rows.length);
            $('#checkAll').prop('checked', false);
            updateSelCount();
        }

        $('#fSearch').on('input', applyFilters);
        $('#fUnder, #fAssign, #collectorSelect').on('change', applyFilters);
        $('#fReset').on('click', function () {
            $('#fSearch').val(''); $('#fUnder').val(''); $('#fAssign').val('');
            applyFilters();
        });

        // ===== Check all (sirf visible rows) =====
        $('#checkAll').on('change', function () {
            $('.ledger-row:not(.d-none) .row-cb').prop('checked', this.checked);
            updateSelCount();
        });
        $(document).on('change', '.row-cb', updateSelCount);

        // ===== Request bhejna =====
        function submitAction(url, btn) {
            const collectorId = $('#collectorSelect').val();
            const ids         = selectedIds();
            const $btn        = $(btn).prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                data: {
                    _token: CSRF,
                    company_id: COMPANY_ID,
                    collector_id: collectorId,
                    ledger_ids: ids
                },
                success: function (res) {
                    showAlert('success', res.message || 'Done.');
                    setTimeout(function () { location.reload(); }, 900);
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong. Please try again.';
                    showAlert('danger', msg);
                }
            });
        }

        // ===== Confirm modal helper =====
        let confirmCallback = null;

        function askConfirm(opts) {
            $('#confirmTitle').text(opts.title || 'Confirm');
            $('#confirmMessage').html(opts.message || '');
            $('#confirmWarn').toggleClass('d-none', !opts.warn).text(opts.warn || '');
            $('#confirmYesBtn')
                .removeClass('btn-primary btn-danger')
                .addClass(opts.danger ? 'btn-danger' : 'btn-primary')
                .text(opts.yesText || 'Yes');

            confirmCallback = opts.onYes;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal')).show();
        }

        $('#confirmYesBtn').on('click', function () {
            const cb = confirmCallback;
            confirmCallback = null;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal')).hide();
            if (cb) cb();
        });

        $('#confirmModal').on('hidden.bs.modal', function () {
            confirmCallback = null;
        });

        // ===== Assign =====
        $('#btnAssign').on('click', function () {
            const btn         = this;
            const collectorId = $('#collectorSelect').val();
            const ids         = selectedIds();

            if (!collectorId) { showAlert('warning', 'Please select a collector first.'); return; }
            if (!ids.length)  { showAlert('warning', 'Please select at least one ledger.'); return; }

            const collectorName = $('#collectorSelect option:selected').text();

            // kitne selected ledgers kisi aur collector ke paas hain
            const movingCount = $('.row-cb:checked').closest('tr').filter(function () {
                const c = $(this).attr('data-collectors') || ',,';
                return c !== ',,' && c.indexOf(',' + collectorId + ',') === -1;
            }).length;

            askConfirm({
                title: 'Confirm Assignment',
                message: 'Assign <strong>' + ids.length + ' ledger(s)</strong> to <strong>' + escapeHtml(collectorName) + '</strong>?',
                warn: movingCount ? movingCount + ' ledger(s) pehle se kisi aur collector ko assigned hain, wo is collector ko move ho jayenge.' : '',
                yesText: 'Yes, Assign',
                onYes: function () {
                    submitAction("{{ route('owner.collectors.ledgers.assign.save') }}", btn);
                }
            });
        });

        // ===== Remove =====
        $('#btnRemove').on('click', function () {
            const btn         = this;
            const collectorId = $('#collectorSelect').val();
            const ids         = selectedIds();

            if (!collectorId) { showAlert('warning', 'Please select a collector first.'); return; }
            if (!ids.length)  { showAlert('warning', 'Please select at least one ledger.'); return; }

            const collectorName = $('#collectorSelect option:selected').text();

            askConfirm({
                title: 'Confirm Removal',
                message: 'Remove <strong>' + ids.length + ' ledger(s)</strong> from <strong>' + escapeHtml(collectorName) + '</strong>?',
                danger: true,
                yesText: 'Yes, Remove',
                onYes: function () {
                    submitAction("{{ route('owner.collectors.ledgers.assign.remove') }}", btn);
                }
            });
        });

        $(function () { applyFilters(); });
    </script>
</div>
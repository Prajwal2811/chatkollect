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
        <style>
            .mark-badge {
                padding: 3px 10px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
                display: inline-block;
            }
            .mark-badge.red     { background: #fde2e2; color: #b30000; }
            .mark-badge.green   { background: #dff5e1; color: #157347; }
            .mark-badge.unmarked{ background: #eee; color: #666; }

            .edit-row-btn {
                border: 1px solid #4E3F6B;
                color: #4E3F6B;
                background: #fff;
                font-size: 12px;
                padding: 4px 12px;
                border-radius: 6px;
                font-weight: 600;
            }
            .edit-row-btn:hover {
                background: #4E3F6B;
                color: #fff;
            }

            #editLedgerModal .mark-option {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-right: 16px;
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

            /* Red-mark reason shown as a hover tooltip icon, not as inline text in the column */
            .reason-icon {
                color: #b02a2a;
                margin-left: 6px;
                font-size: 13px;
                cursor: help;
            }
        </style>

        <div class="container-fluid">
            @php
                $debtorLedgers = collect($ledgers)->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Debtors')->values();
                $creditorLedgers = collect($ledgers)->filter(fn ($l) => ($l['under'] ?? '') == 'Sundry Creditors')->values();

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
            @endphp

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
                        </div>

                        <div class="card-body">
                            <input type="text" value="{{ $company }}" hidden>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                <ul class="nav nav-pills mb-0" id="ledgerTabs" role="tablist">
                                    <li class="nav-item me-2">
                                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#debtors-tab" type="button" role="tab">
                                            Sundry Debtors ({{ $debtorLedgers->count() }})
                                        </button>
                                    </li>
                                    <li class="nav-item me-2">
                                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#creditors-tab" type="button" role="tab">
                                            Sundry Creditors ({{ $creditorLedgers->count() }})
                                        </button>
                                    </li>
                                </ul>

                                <div class="source-filter-bar mb-0">
                                    <div id="debtorSourceFilterWrapper">
                                        <label for="debtorCreditSourceFilter">Credit Period Source</label>
                                        <select id="debtorCreditSourceFilter" class="form-control form-control-sm js-source-filter" data-table="#example11" data-column="credit">
                                            <option value="">All</option>
                                            <option value="tally">Tally</option>
                                            <option value="default">Default</option>
                                        </select>
                                    </div>
                                    <div id="creditorSourceFilterWrapper" class="d-none">
                                        <label for="creditorCreditSourceFilter">Credit Period Source</label>
                                        <select id="creditorCreditSourceFilter" class="form-control form-control-sm js-source-filter" data-table="#example" data-column="credit">
                                            <option value="">All</option>
                                            <option value="tally">Tally</option>
                                            <option value="default">Default</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-content" id="ledgerTabContent">
                                <!-- ===================== DEBTORS TAB ===================== -->
                                <div class="tab-pane fade show active" id="debtors-tab" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="example11" class="display">
                                            <thead>
                                                <tr>
                                                    <th width="3%">#</th>
                                                    <th width="26%">Name</th>
                                                    <th width="12%">Mob No</th>
                                                    <th width="10%">Credit Period</th>
                                                    <th width="14%">Collector Name</th>
                                                    <th width="10%">Balance Limit</th>
                                                    <th width="10%">Overlimit</th>
                                                    <th width="10%">Mark</th>
                                                    <th class="text-center" width="8%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($debtorLedgers as $index => $l)
                                                    @php
                                                        $mark = $l['mark'] ?? 'unmarked';
                                                        $collectorName = \App\Models\Collector::where('id', $l['assigned_collector'])->first();
                                                        $creditSrc = $sourceKey($l['credit_period_source'] ?? null);
                                                    @endphp
                                                    <tr data-credit-source="{{ $creditSrc }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $l['name'] }}</td>
                                                        <td class="cell-mobile">{{ $l['mobile'] ?? '-' }}</td>
                                                        <td>
                                                            {{ !empty($l['credit_period']) ? $l['credit_period'] : '-' }}
                                                            @if(!empty($l['credit_period']))
                                                                {!! $sourceBadge($creditSrc) !!}
                                                            @endif
                                                        </td>
                                                        <td>{{ $collectorName->name ?? '-' }}</td>
                                                        <td class="cell-balance-limit">{{ $l['balance_limit'] ?? '-' }}</td>
                                                        <td class="cell-overlimit">{{ $l['overlimit'] ?? '-' }}</td>
                                                        <td class="cell-mark">
                                                            <span class="mark-badge {{ $mark }}">{{ ucfirst($mark) }}</span>
                                                            @if($mark === 'red' && !empty($l['red_reason']))
                                                                <i class="fa fa-info-circle reason-icon"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ $l['red_reason'] }}"></i>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="edit-row-btn edit-ledger-row"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editLedgerModal"
                                                                data-id="{{ $l['id'] }}"
                                                                data-company="{{ $company }}"
                                                                data-under="Sundry Debtors"
                                                                data-name="{{ $l['name'] }}"
                                                                data-mobile="{{ $l['mobile'] ?? '' }}"
                                                                data-balance_limit="{{ $l['balance_limit'] ?? '' }}"
                                                                data-mark="{{ $mark }}"
                                                                data-red_reason="{{ $l['red_reason'] ?? '' }}"
                                                                data-mode="full">
                                                                Edit
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">No Debtors Found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- ===================== CREDITORS TAB ===================== -->
                                <div class="tab-pane fade" id="creditors-tab" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="example" class="display" style="min-width: 700px">
                                            <thead>
                                                <tr>
                                                    <th width="6%">#</th>
                                                    <th width="46%">Name</th>
                                                    <th width="24%">Mob No</th>
                                                    <th width="16%">Credit Period</th>
                                                    <th class="text-center" width="8%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($creditorLedgers as $index => $l)
                                                    @php
                                                        $creditSrc = $sourceKey($l['credit_period_source'] ?? null);
                                                    @endphp
                                                    <tr data-credit-source="{{ $creditSrc }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $l['name'] }}</td>
                                                        <td class="cell-mobile">{{ $l['mobile'] ?? '-' }}</td>
                                                        <td>
                                                            {{ !empty($l['credit_period']) ? $l['credit_period'] : '-' }}
                                                            @if(!empty($l['credit_period']))
                                                                {!! $sourceBadge($creditSrc) !!}
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="edit-row-btn edit-ledger-row"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editLedgerModal"
                                                                data-id="{{ $l['id'] }}"
                                                                data-company="{{ $company }}"
                                                                data-under="Sundry Creditors"
                                                                data-name="{{ $l['name'] }}"
                                                                data-mobile="{{ $l['mobile'] ?? '' }}"
                                                                data-mode="mobile_only">
                                                                Edit
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No Creditors Found</td>
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

    <!-- ===================== SHARED EDIT MODAL ===================== -->
    <div class="modal fade" id="editLedgerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLedgerModalTitle">Edit Ledger</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_id">
                    <input type="hidden" id="modal_company">
                    <input type="hidden" id="modal_under">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mobile Number</label>
                        <input type="text" class="form-control" id="modal_mobile" placeholder="Enter mobile number">
                    </div>

                    <!-- Full fields (debtors only) -->
                    <div id="modal_full_fields">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Balance Limit</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="modal_balance_limit" placeholder="Enter amount">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mark</label>
                            <div class="d-flex">
                                <label class="mark-option">
                                    <input type="radio" name="modal_mark" value="red"> Red
                                </label>
                                <label class="mark-option">
                                    <input type="radio" name="modal_mark" value="green"> Green
                                </label>
                                <label class="mark-option">
                                    <input type="radio" name="modal_mark" value="unmarked"> Unmarked
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="modal_reason_wrapper" style="display:none;">
                            <label class="form-label fw-semibold">Reason</label>
                            <input type="text" class="form-control" id="modal_red_reason" placeholder="Reason for red mark">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-sm" id="modalSaveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    @include('owner.tally.components.footer')

    <script>
        let currentMode = 'full';

        $(document).on('click', '.edit-ledger-row', function () {
            
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({
                'overflow': '',
                'padding-right': ''
            });

            let $btn = $(this);
            currentMode = $btn.data('mode'); // 'full' or 'mobile_only'

            $('#editLedgerModalTitle').text('Edit — ' + $btn.data('name'));
            $('#modal_id').val($btn.data('id'));
            $('#modal_company').val($btn.data('company'));
            $('#modal_under').val($btn.data('under'));
            $('#modal_mobile').val($btn.data('mobile'));

            if (currentMode === 'full') {
                $('#modal_full_fields').show();
                $('#modal_balance_limit').val($btn.data('balance_limit'));

                let mark = $btn.data('mark') || 'unmarked';
                $(`input[name="modal_mark"][value="${mark}"]`).prop('checked', true);

                $('#modal_red_reason').val($btn.data('red_reason'));
                $('#modal_reason_wrapper').toggle(mark === 'red');
            } else {
                $('#modal_full_fields').hide();
            }
        });

        $(document).on('change', 'input[name="modal_mark"]', function () {
            $('#modal_reason_wrapper').toggle($(this).val() === 'red');
            if ($(this).val() !== 'red') {
                $('#modal_red_reason').val('');
            }
        });

      
        $(document).on('click', '#modalSaveBtn', function () {
            let $btn = $(this);

            let payload = {
                id:      $('#modal_id').val(),
                under:   $('#modal_under').val(),
                mobile:  $('#modal_mobile').val(),
                _token:  "{{ csrf_token() }}"
            };

            if (currentMode === 'full') {
                payload.balance_limit = $('#modal_balance_limit').val();
                payload.mark          = $('input[name="modal_mark"]:checked').val() || 'unmarked';
                payload.red_reason    = $('#modal_red_reason').val();
            }

            let company = $('#modal_company').val();
            let url = "{{ route('owner.other.saveRow', ':company') }}".replace(':company', encodeURIComponent(company));

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                method: 'POST',
                data: payload,
                success: function (res) {
                    $btn.prop('disabled', false).text('Save');

                    if (res.success) {
                        updateRowInTable(payload);

                        let modalEl = document.getElementById('editLedgerModal');
                        let modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) {
                            modalInstance.hide();
                        }

                        showAjaxAlert('success', res.message || 'Saved successfully.');
                    } else {
                        let firstError = Object.values(res.errors)[0][0];
                        showAjaxAlert('danger', firstError);
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).text('Save');
                    let msg = 'Could not save details. Please try again.';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0][0];
                    }
                    showAjaxAlert('danger', msg);
                }
            });
        });

        $(document).on('hidden.bs.modal', '#editLedgerModal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({
                'overflow': '',
                'padding-right': ''
            });
        });

     
        function updateRowInTable(payload) {
            let $rowBtn = $(`.edit-ledger-row[data-id="${payload.id}"][data-under="${payload.under}"]`);
            if (!$rowBtn.length) return;

            let $row = $rowBtn.closest('tr');

            $row.find('.cell-mobile').text(payload.mobile || '-');
            $rowBtn.data('mobile', payload.mobile);
            $rowBtn.attr('data-mobile', payload.mobile);

            if (currentMode === 'full') {
                $row.find('.cell-balance-limit').text(payload.balance_limit || '-');
                $rowBtn.data('balance_limit', payload.balance_limit);
                $rowBtn.attr('data-balance_limit', payload.balance_limit);

                let markLabel = payload.mark.charAt(0).toUpperCase() + payload.mark.slice(1);
                let $markCell = $row.find('.cell-mark');
                $markCell.find('.mark-badge')
                    .removeClass('red green unmarked')
                    .addClass(payload.mark)
                    .text(markLabel);

                $markCell.find('.reason-icon').each(function () {
                    let existingTooltip = bootstrap.Tooltip.getInstance(this);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                }).remove();

                if (payload.mark === 'red' && payload.red_reason) {
                    let $icon = $(`<i class="fa fa-info-circle reason-icon" data-bs-toggle="tooltip" title="${payload.red_reason}"></i>`);
                    $markCell.append($icon);
                    bootstrap.Tooltip.getOrCreateInstance($icon[0]);
                }

                $rowBtn.data('mark', payload.mark);
                $rowBtn.attr('data-mark', payload.mark);
                $rowBtn.data('red_reason', payload.red_reason);
                $rowBtn.attr('data-red_reason', payload.red_reason);
            }
        }

        // Show a dismissible Bootstrap alert inside #ajaxAlertWrapper, scroll to it, and auto-hide it.
        function showAjaxAlert(type, message) {
            let $wrapper = $('#ajaxAlertWrapper');
            $wrapper.find('.ajax-dynamic-alert').remove();

            let $alert = $(`
                <div class="alert alert-${type} alert-dismissible fade show text-center ajax-dynamic-alert" tabindex="-1" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    ${message}
                </div>
            `);

            $wrapper.append($alert);
            $alert[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            $alert[0].focus();

            setTimeout(function () {
                if ($alert.length) {
                    let bsAlert = bootstrap.Alert.getOrCreateInstance($alert[0]);
                    bsAlert.close();
                }
            }, 3000);
        }

        // ---------- Credit Period Source (Tally / Default) filter for both tables ----------
        const sourceFilterState = {
            '#example11': { credit: '' },
            '#example':   { credit: '' },
        };

        if ($.fn.DataTable) {
            $.fn.dataTable.ext.search.push(function (settings, searchData, index) {
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
                return true;
            });
        }

        $(document).on('change', '.js-source-filter', function () {
            const $select = $(this);
            const tableSelector = $select.data('table');
            const column = $select.data('column'); // 'credit'
            const value = $select.val();

            if (!sourceFilterState[tableSelector]) {
                sourceFilterState[tableSelector] = { credit: '' };
            }
            sourceFilterState[tableSelector][column] = value;

            if ($.fn.DataTable && $.fn.DataTable.isDataTable(tableSelector)) {
                $(tableSelector).DataTable().draw();
            }
        });

        // Initialize DataTables on both tables if the plugin is loaded.
        $(function () {
            if ($.fn.DataTable) {
                $('#example11, #example').each(function () {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            paging: true,
                            searching: true,
                            info: true
                        });
                    }
                });
            }

            $('[data-bs-toggle="tooltip"]').each(function () {
                bootstrap.Tooltip.getOrCreateInstance(this);
            });

            $('#ledgerTabs button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
                const targetId = $(e.target).data('bs-target');

                if (targetId === '#debtors-tab') {
                    $('#debtorSourceFilterWrapper').removeClass('d-none');
                    $('#creditorSourceFilterWrapper').addClass('d-none');
                } else {
                    $('#creditorSourceFilterWrapper').removeClass('d-none');
                    $('#debtorSourceFilterWrapper').addClass('d-none');
                }

                if ($.fn.DataTable) {
                    let targetTable = $(targetId).find('table');
                    if ($.fn.DataTable.isDataTable(targetTable)) {
                        targetTable.DataTable().columns.adjust();
                    }
                }
            });
        });
    </script>
</div>
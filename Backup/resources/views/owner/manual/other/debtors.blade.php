@include('owner.components.header')
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

    @php
        $debtorLedgers = [
                [
                    'name'            => 'Rahul Sharma',
                    'mobile'          => '9876543210',
                    'credit_period'   => '30 Days',
                    'collector_name'  => 'Amit Verma',
                    'balance_limit'   => 50000,
                    'overlimit'       => 5000,
                ],
                [
                    'name'            => 'Priya Singh',
                    'mobile'          => '9123456780',
                    'credit_period'   => '15 Days',
                    'collector_name'  => 'Suresh Yadav',
                    'balance_limit'   => 30000,
                    'overlimit'       => 0,
                ],
                [
                    'name'            => 'Ankit Patel',
                    'mobile'          => '9988776655',
                    'credit_period'   => '45 Days',
                    'collector_name'  => 'Amit Verma',
                    'balance_limit'   => 75000,
                    'overlimit'       => 10000,
                ],
                [
                    'name'            => 'Deepak Kumar',
                    'mobile'          => '9876543210',
                    'credit_period'   => '30 Days',
                    'collector_name'  => 'Amit Verma',
                    'balance_limit'   => 50000,
                    'overlimit'       => 5000,
                ],
                [
                    'name'            => 'Rohit Sharma',
                    'mobile'          => '9876543210',
                    'credit_period'   => '30 Days',
                    'collector_name'  => 'Amit Verma',
                    'balance_limit'   => 50000,
                    'overlimit'       => 5000,
                ],
                [
                    'name'            => 'Rahul Sharma',
                    'mobile'          => '9876543210',
                    'credit_period'   => '30 Days',
                    'collector_name'  => 'Amit Verma',
                    'balance_limit'   => 50000,
                    'overlimit'       => 5000,
                ],
                [
                    'name'            => 'Rahul Sharma',
                    'mobile'          => '9876543210',
                    'credit_period'   => '30 Days',
                    'collector_name'  => 'Amit Verma',
                    'balance_limit'   => 50000,
                    'overlimit'       => 5000,
                ],
            ];
    @endphp
    <div class="content-body default-height">
        <style>
            .accordion-button:not(.collapsed) {
                background-color: #f1f0f8;
                color: #4E3F6B;
            }

            table.example11 tbody tr {
                cursor: default;
            }

            #debtorLedgerTable,
            #creditorLedgerTable {
                table-layout: fixed;
                min-width: 1300px;
            }

            #creditorLedgerTable {
                min-width: 700px;
            }

            #debtorLedgerTable th,
            #debtorLedgerTable td,
            #creditorLedgerTable th,
            #creditorLedgerTable td {
                word-wrap: break-word;
                white-space: normal;
                vertical-align: middle;
            }

            .mark-cell {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: nowrap;
            }

            .mark-cell input[type="radio"] {
                width: 16px;
                height: 16px;
                cursor: pointer;
                margin: 0;
                flex-shrink: 0;
            }

            .mark-cell input[type="text"] {
                flex: 1 1 auto;
                min-width: 0;
                display: none;
            }

            .mark-cell input[type="text"].show-note {
                display: block;
            }

            .th-balance-limits { background-color: #ffff00; }
            .th-overlimit      { background-color: #ff00ff; color:#fff; }
            .th-red            { background-color: #ff0000; color:#fff; }
            .th-green          { background-color: #00ff00; }
            .th-unmarked       { background-color: #f1f0f8; }

            .ledger-tabs .nav-link {
                color: #4E3F6B;
                font-weight: 600;
            }

            .ledger-tabs .nav-link.active {
                background-color: #4E3F6B;
                color: #fff;
            }
        </style>

        <div class="container-fluid">
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
                            <h4 class="card-title mb-0">Students List</h4>
                        </div>

                        <div class="card-body">
                            <input type="text" value="" hidden>
                            <div class="tab-content" id="ledgerTabContent">
                                <!-- ===================== DEBTORS TAB ===================== -->
                                <div class="tab-pane fade show active" id="debtors-tab" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover example11" id="debtorLedgerTable">
                                            <thead>
                                                <tr>
                                                    <th width="3%">#</th>
                                                    <th width="20%">Student Name</th>
                                                    <th width="12%">Mob No</th>
                                                    <th width="10%">Credit Period</th>
                                                    <th width="14%">Collector Name</th>
                                                    <th class="th-balance-limits" width="12%">Balance Limits</th>
                                                    <th class="th-overlimit" width="12%">Overlimit</th>
                                                    <th class="th-red" width="16%">Red Marked<br>With Reason</th>
                                                    <th class="th-green" width="8%">Green</th>
                                                    <th class="th-unmarked" width="10%">Unmarked</th>
                                                    <th class="text-center" width="6%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($debtorLedgers as $index => $l)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $l['name'] }}</td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" name="mobile" value="{{ $l['mobile'] ?? '' }}" placeholder="Mob No">
                                                        </td>
                                                        <td>{{ $l['credit_period'] ?? '-' }}</td>
                                                        <td>{{ $l['collector_name'] ?? '-' }}</td>
                                                        <td>
                                                            <input type="number" min="0" step="0.01" class="form-control form-control-sm" name="balance_limit" value="{{ $l['balance_limit'] ?? '' }}" placeholder="Amount">
                                                        </td>
                                                        <td>
                                                            <input type="number" min="0" step="0.01" class="form-control form-control-sm" name="overlimit" value="{{ $l['overlimit'] ?? '' }}" placeholder="Amount">
                                                        </td>
                                                        <td>
                                                            <div class="mark-cell">
                                                                <input type="radio" name="mark_debtor_{{ $index }}" value="red" class="mark-radio">
                                                                <input type="text" class="form-control form-control-sm" name="red_reason" placeholder="Reason">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="mark-cell">
                                                                <input type="radio" name="mark_debtor_{{ $index }}" value="green" class="mark-radio">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="mark-cell">
                                                                <input type="radio" name="mark_debtor_{{ $index }}" value="unmarked" class="mark-radio" checked>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-success save-ledger-row"
                                                                data-company="{{ $company }}"
                                                                data-ledger="{{ $l['name'] }}"
                                                                data-under="Sundry Debtors">
                                                                Save
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="11" class="text-center text-muted">No Debtors Found</td>
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

    @include('owner.components.footer')

    <script>
        // Show the reason input only when the row's mark is set to "red".
        // For green/unmarked there is no note field, so it just stays hidden.
        $(document).on('change', '.mark-radio', function () {
            let $row = $(this).closest('tr');
            let checkedVal = $row.find('.mark-radio:checked').val();
            let $reason = $row.find('input[name="red_reason"]');

            if (checkedVal === 'red') {
                $reason.addClass('show-note');
            } else {
                $reason.removeClass('show-note').val('');
            }
        });

        // Save a single Debtor ledger row (mobile, limits, mark + reason).
        $(document).on('click', '#debtorLedgerTable .save-ledger-row', function () {
            let $btn  = $(this);
            let $row  = $btn.closest('tr');
            let markName = $row.find('.mark-radio').first().attr('name');
            let mark = $row.find(`input[name="${markName}"]:checked`).val();

            let payload = {
                company: $btn.data('company'),
                ledger:  $btn.data('ledger'),
                under:   $btn.data('under'),
                mobile:         $row.find('[name="mobile"]').val(),
                balance_limit:  $row.find('[name="balance_limit"]').val(),
                overlimit:      $row.find('[name="overlimit"]').val(),
                mark:           mark,
                red_reason:     $row.find('[name="red_reason"]').val(),
                _token: "{{ csrf_token() }}"
            };

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                // TODO: point this to your actual save route, e.g.
                url: "{{ route('owner.tally.ledger.saveDetails') }}",
                method: 'POST',
                data: payload,
                success: function (res) {
                    $btn.text('Saved');
                    setTimeout(() => $btn.prop('disabled', false).text('Save'), 1500);
                },
                error: function (xhr) {
                    alert('Could not save details. Please try again.');
                    $btn.prop('disabled', false).text('Save');
                }
            });
        });

        // Initialize DataTables on both tables if the plugin is loaded.
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

            // Fix column widths when switching to a tab (DataTables needs
            // a redraw/adjust after being shown inside a hidden tab-pane).
            $('#ledgerTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($.fn.DataTable) {
                    let targetTable = $($(e.target).data('bs-target')).find('table.example11');
                    if ($.fn.DataTable.isDataTable(targetTable)) {
                        targetTable.DataTable().columns.adjust();
                    }
                }
            });
        });
    </script>
</div>
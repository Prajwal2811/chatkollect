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
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h4 class="card-title mb-0">Ledger Vouchers — {{ $ledger }}</h4>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary">{{ $under }}</span>

                                    <form method="GET" id="fyFilterForm" class="d-flex align-items-center gap-2 mb-0">
                                        <label class="mb-0 fw-semibold small text-muted">FY:</label>

                                        <select name="fy" id="fySelect" class="form-select form-select-sm" style="width:auto;">
                                            @foreach(array_reverse($fyOptions) as $fy)
                                                <option value="{{ $fy }}" {{ $fy === $selectedFyLabel ? 'selected' : '' }}>
                                                    {{ $fy }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </div>

                            <div class="px-3 pt-2">
                                <small class="text-muted">
                                    Summary cards: <strong>FY {{ $selectedFyLabel }}</strong> &nbsp;|&nbsp;
                                    Voucher tables: <strong>FY {{ $previousFyLabel }}</strong>
                                </small>
                            </div>

                            <div class="card-body">

                                {{-- Summary Cards --}}
                                <div class="row g-3 mb-4">
                                    @php
                                        $cards = [
                                             [
                                                'title' => 'Opening Balance',
                                                'value' => abs($openingBalance ?? 0),
                                                'bg'    => 'primary',
                                                'target'=> 'pendingVouchersSection',
                                            ],
                                            [
                                                'title' => 'Closing Balance',
                                                'value' => abs($closingBalance ?? 0),
                                                'bg'    => 'success',
                                                'target'=> 'closingVouchersSection',
                                            ],
                                            [
                                                'title' => $under === 'Sundry Creditors' ? 'Total Credit (Purchase + Others)' : 'Total Debit (Sales + Others)',
                                                'value' => $summary['sale'] ?? 0,
                                                'bg'    => 'warning',
                                                'target'=> null,
                                            ],
                                            [
                                                'title' => $under === 'Sundry Creditors' ? 'Total Payment (Debit)' : 'Total Receipt (Credit)',
                                                'value' => $summary['receipts'] ?? 0,
                                                'bg'    => 'info',
                                                'target'=> null,
                                            ],

                                            // [
                                            //     'title' => 'Pending Amount',
                                            //     'value' => $summary['pending'] ?? 0,
                                            //     'bg'    => ($summary['pending'] ?? 0) > 0 ? 'danger' : 'success',
                                            //     'target'=> null,
                                            // ],
                                        ];
                                    @endphp

                                    @foreach($cards as $card)
                                        <div class="col-xl-3 col-lg-4 col-md-6">
                                            <div class="card border-0 shadow-sm h-100 {{ $card['target'] ? 'summary-clickable' : '' }}"
                                                @if($card['target']) onclick="toggleVoucherSection('{{ $card['target'] }}', event)" style="cursor:pointer;" @endif>
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="text-muted mb-2">{{ $card['title'] }}</h6>
                                                            <h4 class="mb-0 fw-bold">
                                                                ₹ {{ number_format($card['value'], 2) }}
                                                            </h4>
                                                        </div>
                                                        <div class="rounded-circle bg-{{ $card['bg'] }}"
                                                            style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:bold;">
                                                            ₹
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>

                                {{-- Pending Vouchers Table --}}
                                <div class="row mt-4" id="pendingVouchersSection" style="display:none;">
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-danger-subtle d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    Pending Vouchers ({{ count($pendingVouchers) }}) — FY {{ $previousFyLabel }}
                                                </h5>
                                                <span class="fw-bold">
                                                    ₹ {{ number_format(collect($pendingVouchers)->sum('pending'), 2) }}
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                 <div class="table-responsive">
                                                    <table id="example13" class="display">
                                                        <thead>
                                                            <tr>
                                                                <th>Sr.No</th>
                                                                <th>Date</th>
                                                                <th>Particulars</th>
                                                                <th>Voucher Type</th>
                                                                <th>Vch No</th>
                                                                <th class="text-end">
                                                                    {{ $under === 'Sundry Creditors' ? 'Total Credit' : 'Total Debit' }}
                                                                </th>
                                                                <th class="text-end">Pending</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($pendingVouchers as $index => $voucher)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>
                                                                        {{ !empty($voucher['date'])
                                                                            ? \Carbon\Carbon::parse($voucher['date'])->format('d M Y')
                                                                            : '-' }}
                                                                    </td>
                                                                    <td>{{ $voucher['particulars'] ?? '-' }}</td>
                                                                    <td>
                                                                        <span class="badge bg-secondary">
                                                                            {{ $voucher['voucher_type'] ?? '-' }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $voucher['voucher_number'] ?? '-' }}</td>
                                                                    <td class="text-end">
                                                                        ₹ {{ number_format(
                                                                            $under === 'Sundry Creditors'
                                                                                ? ($voucher['credit'] ?? 0)
                                                                                : ($voucher['debit'] ?? 0),
                                                                            2
                                                                        ) }}
                                                                    </td>
                                                                    <td class="text-end fw-bold text-danger">
                                                                        {{ number_format($voucher['pending'] ?? 0, 2) }}
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="7" class="text-center text-muted py-3">
                                                                        No Pending Vouchers — Sab Clear! 🎉
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="fw-bold">
                                                                <th colspan="6" class="text-end">Total Pending</th>
                                                                <th class="text-end">
                                                                    ₹ {{ number_format(collect($pendingVouchers)->sum('pending'), 2) }}
                                                                </th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Closing Balance Vouchers Table — FY {{ $selectedFyLabel }} --}}
                                <div class="row mt-4" id="closingVouchersSection" style="display:none;">
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    Closing Balance Vouchers ({{ count($closingBalanceVouchers) }}) — FY {{ $selectedFyLabel }}
                                                </h5>
                                                <span class="fw-bold">
                                                    ₹ {{ number_format(collect($closingBalanceVouchers)->sum('pending'), 2) }}
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table id="example11" class="display">
                                                        <thead>
                                                            <tr>
                                                                <th>Sr.No</th>
                                                                <th>Date</th>
                                                                <th>Particulars</th>
                                                                <th>Voucher Type</th>
                                                                <th>Vch No</th>
                                                                <th class="text-end">
                                                                    {{ $under === 'Sundry Creditors' ? 'Total Credit' : 'Total Debit' }}
                                                                </th>
                                                                <th class="text-end">Pending</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($closingBalanceVouchers as $index => $voucher)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>
                                                                        {{ !empty($voucher['date'])
                                                                            ? \Carbon\Carbon::parse($voucher['date'])->format('d M Y')
                                                                            : '-' }}
                                                                    </td>
                                                                    <td>{{ $voucher['particulars'] ?? '-' }}</td>
                                                                    <td>
                                                                        <span class="badge bg-secondary">
                                                                            {{ $voucher['voucher_type'] ?? '-' }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $voucher['voucher_number'] ?? '-' }}</td>
                                                                    <td class="text-end">
                                                                        ₹ {{ number_format(
                                                                            $under === 'Sundry Creditors'
                                                                                ? ($voucher['credit'] ?? 0)
                                                                                : ($voucher['debit'] ?? 0),
                                                                            2
                                                                        ) }}
                                                                    </td>
                                                                    <td class="text-end fw-bold text-success">
                                                                        {{ number_format($voucher['pending'] ?? 0, 2) }}
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="7" class="text-center text-muted py-3">
                                                                        Koi Closing Balance Voucher nahi mila.
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="fw-bold">
                                                                <th colspan="6" class="text-end">Total Closing</th>
                                                                <th class="text-end">
                                                                    ₹ {{ number_format(collect($closingBalanceVouchers)->sum('pending'), 2) }}
                                                                </th>
                                                            </tr>
                                                        </tfoot>
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
            </div>
        </div>

        <style>
            .card {
                border-radius: 12px;
                transition: all .3s ease;
            }
            .card:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, .12);
            }

            .card h4 {
                font-size: 22px;
            }

            .card h6 {
                font-size: 14px;
                text-transform: uppercase;
            }

            .summary-clickable {
                border: 2px solid transparent;
            }
            .summary-clickable:hover {
                border-color: #0d6efd;
            }
            .summary-clickable.active-card {
                border-color: #0d6efd;
                box-shadow: 0 0 0 2px rgba(13,110,253,.25);
            }
        </style>

        <script>
        document.getElementById('fySelect').addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('fy', this.value);
            window.location.href = url.toString();
        });

        function toggleVoucherSection(targetId, evt) {
            const pending = document.getElementById('pendingVouchersSection');
            const closing = document.getElementById('closingVouchersSection');
            const target  = document.getElementById(targetId);

            const isAlreadyOpen = target.style.display !== 'none';

            pending.style.display = 'none';
            closing.style.display = 'none';

            document.querySelectorAll('.summary-clickable').forEach(el => el.classList.remove('active-card'));

            if (!isAlreadyOpen) {
                target.style.display = 'block';
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (evt && evt.currentTarget) {
                    evt.currentTarget.classList.add('active-card');
                }
            }
        }
        </script>

    </div>
@include('owner.tally.components.footer')
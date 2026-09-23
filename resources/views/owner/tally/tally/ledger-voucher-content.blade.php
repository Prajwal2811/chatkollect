{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            [
                'title'=>'Opening Balance',
                'value'=>$summary['opening'] ?? 0,
                'bg'=>'primary'
            ],
            [
                'title'=>$under=='Sundry Creditors' ? 'Total Credit' : 'Total Debit',
                'value'=>$summary['sale'] ?? 0,
                'bg'=>'warning'
            ],
            [
                'title'=>$under=='Sundry Creditors' ? 'Total Debit' : 'Total Credit',
                'value'=>$summary['receipts'] ?? 0,
                'bg'=>'info'
            ],
            [
                'title'=>'Closing Balance',
                'value'=>$summary['closing'] ?? 0,
                'bg'=>'success'
            ],
            [
                'title'=>'Pending Amount',
                'value'=>$summary['pending'] ?? 0,
                'bg'=>'danger'
            ]
        ];
    @endphp

    @foreach($cards as $card)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
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

    <div class="col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6>Pending Vouchers</h6>
                <h2>{{ $summary['pending_count'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-pills mb-4" id="voucherTabs">
    <li class="nav-item me-2">
        <button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#sales-tab">
            {{ $under === 'Sundry Creditors' ? 'Purchases' : 'Sales' }} (Outstanding)
            ({{ count($primaryVouchers) }})
        </button>
    </li>
    <li class="nav-item me-2">
        <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#receipt-tab">
            {{ $under === 'Sundry Creditors' ? 'Payments' : 'Receipts' }} (Unallocated)
            ({{ count($secondaryVouchers) }})
        </button>
    </li>
    <li class="nav-item me-2">
        <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#others-tab">
            Others (Outstanding) ({{ count($journalVouchers) }})
        </button>
    </li>
    @if(!empty($summary['opening_breakup']))
    <li class="nav-item me-2">
        <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#opening-breakup-tab">
            Opening Balance Breakup ({{ count($summary['opening_breakup']) }})
        </button>
    </li>
    @endif
</ul>

<div class="tab-content">

    {{-- Sales / Purchase Tab --}}
    <div class="tab-pane fade show active" id="sales-tab">
        <div class="table-responsive">
            <table id="example11" class="display">
                <thead>
                    <tr>
                        <th>Sr.No</th>
                        <th>Date</th>
                        <th>Particulars</th>
                        <th>Vch No</th>
                        <th class="text-end">
                            {{ $under === 'Sundry Creditors' ? 'Credit' : 'Debit' }}
                        </th>
                        <th class="text-end">Pending</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($primaryVouchers as $index => $voucher)
                        <tr class="{{ !empty($voucher['is_bf']) ? 'table-warning' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ !empty($voucher['date'])
                                    ? \Carbon\Carbon::parse($voucher['date'])->format('d M Y')
                                    : '-' }}
                                @if(!empty($voucher['is_bf']))
                                    <span class="badge bg-secondary ms-1">B/F</span>
                                @endif
                            </td>
                            <td>{{ $voucher['particulars'] ?? '-' }}</td>
                            <td>{{ $voucher['voucher_number'] ?? '-' }}</td>
                            <td class="text-end">
                                ₹ {{ number_format(
                                    $under === 'Sundry Creditors'
                                        ? ($voucher['credit'] ?? 0)
                                        : ($voucher['debit'] ?? 0),
                                    2
                                ) }}
                            </td>
                            <td class="text-end text-danger fw-semibold">
                                ₹ {{ number_format($voucher['pending'] ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                {{ $under === 'Sundry Creditors' ? 'No Outstanding Purchase Vouchers' : 'No Outstanding Sales Vouchers' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @php
                    $primaryTotal = $under === 'Sundry Creditors'
                        ? collect($primaryVouchers)->sum('credit')
                        : collect($primaryVouchers)->sum('debit');
                    $primaryPendingTotal = collect($primaryVouchers)->sum('pending');
                @endphp
                <tfoot>
                    <tr class="fw-bold">
                        <th colspan="4" class="text-end">Total</th>
                        <th class="text-end">₹ {{ number_format($primaryTotal, 2) }}</th>
                        <th class="text-end text-danger">₹ {{ number_format($primaryPendingTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Receipts / Payments Tab --}}
    <div class="tab-pane fade" id="receipt-tab">
        <div class="table-responsive">
            <table id="example12" class="display">
                <thead>
                    <tr>
                        <th>Sr.No</th>
                        <th>Date</th>
                        <th>Particulars</th>
                        <th class="text-end">
                            {{ $under === 'Sundry Creditors' ? 'Debit' : 'Credit' }}
                        </th>
                        <th class="text-end">Unallocated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($secondaryVouchers as $index => $voucher)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ !empty($voucher['date'])
                                    ? \Carbon\Carbon::parse($voucher['date'])->format('d M Y')
                                    : '-' }}
                            </td>
                            <td>{{ $voucher['particulars'] ?? '-' }}</td>
                            <td class="text-end">
                                ₹ {{ number_format(
                                    $under === 'Sundry Creditors'
                                        ? ($voucher['debit'] ?? 0)
                                        : ($voucher['credit'] ?? 0),
                                    2
                                ) }}
                            </td>
                            <td class="text-end text-danger fw-semibold">
                                ₹ {{ number_format($voucher['pending'] ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                {{ $under === 'Sundry Creditors' ? 'No Unallocated Payments' : 'No Unallocated Receipts' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @php
                    $secondaryTotal = $under === 'Sundry Creditors'
                        ? collect($secondaryVouchers)->sum('debit')
                        : collect($secondaryVouchers)->sum('credit');
                    $secondaryPendingTotal = collect($secondaryVouchers)->sum('pending');
                @endphp
                <tfoot>
                    <tr class="fw-bold">
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-end">₹ {{ number_format($secondaryTotal, 2) }}</th>
                        <th class="text-end text-danger">₹ {{ number_format($secondaryPendingTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Others Tab --}}
    <div class="tab-pane fade" id="others-tab">
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
                            {{ $under === 'Sundry Creditors' ? 'Credit' : 'Debit' }}
                        </th>
                        <th class="text-end">Pending</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journalVouchers as $index => $voucher)
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
                            <td class="text-end text-danger fw-semibold">
                                ₹ {{ number_format($voucher['pending'] ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                No Outstanding "Others" Vouchers
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @php
                    $othersTotal = $under === 'Sundry Creditors'
                        ? collect($journalVouchers)->sum('credit')
                        : collect($journalVouchers)->sum('debit');
                    $othersPendingTotal = collect($journalVouchers)->sum('pending');
                @endphp
                <tfoot>
                    <tr class="fw-bold">
                        <th colspan="5" class="text-end">Total</th>
                        <th class="text-end">₹ {{ number_format($othersTotal, 2) }}</th>
                        <th class="text-end text-danger">₹ {{ number_format($othersPendingTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Opening Balance Breakup Tab --}}
    @if(!empty($summary['opening_breakup']))
    <div class="tab-pane fade" id="opening-breakup-tab">
        <div class="table-responsive">
            <table id="example14" class="display">
                <thead>
                    <tr>
                        <th>Sr.No</th>
                        <th>Financial Year</th>
                        <th class="text-center">Vouchers</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['opening_breakup'] as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['fy'] }}</td>
                            <td class="text-center">{{ $row['count'] ?? '-' }}</td>
                            <td class="text-end">₹ {{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <th colspan="2" class="text-end">Total</th>
                        <th class="text-center">{{ collect($summary['opening_breakup'])->sum('count') }}</th>
                        <th class="text-end">₹ {{ number_format($summary['opening'] ?? 0, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

</div>
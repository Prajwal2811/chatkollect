{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            [
                'title' => $under === 'Sundry Creditors' ? 'Total Credit (Purchase + Others)' : 'Total Debit (Sales + Others)',
                'value' => $summary['sale'] ?? 0,
                'bg'    => 'warning',
            ],
            [
                'title' => $under === 'Sundry Creditors' ? 'Total Payment (Debit)' : 'Total Receipt (Credit)',
                'value' => $summary['receipts'] ?? 0,
                'bg'    => 'info',
            ],
            [
                'title' => 'Opening Balance',
                'value' => abs($openingBalance ?? 0),
                'bg'    => 'primary',
            ],
            [
                'title' => 'Closing Balance',
                'value' => abs($closingBalance ?? 0),
                'bg'    => 'success',
            ],
            [
                'title' => 'Pending Amount',
                'value' => $summary['pending'] ?? 0,
                'bg'    => ($summary['pending'] ?? 0) > 0 ? 'danger' : 'success',
            ],
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
</div>

{{-- Tabs --}}
<ul class="nav nav-pills mb-4" id="voucherTabs">
    <li class="nav-item me-2">
        <button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#sales-tab">
            {{ $under === 'Sundry Creditors' ? 'Purchases' : 'Sales' }}
            ({{ count($primaryVouchers) }})
        </button>
    </li>
    <li class="nav-item me-2">
        <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#receipt-tab">
            {{ $under === 'Sundry Creditors' ? 'Payments' : 'Receipts' }}
            ({{ count($secondaryVouchers) }})
        </button>
    </li>
    <li class="nav-item me-2">
        <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#others-tab">
            Others ({{ count($journalVouchers) }})
        </button>
    </li>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($primaryVouchers as $index => $voucher)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ !empty($voucher['date'])
                                    ? \Carbon\Carbon::parse($voucher['date'])->format('d M Y')
                                    : '-' }}
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                {{ $under === 'Sundry Creditors' ? 'No Purchase Vouchers Found' : 'No Sales Vouchers Found' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @php
                    $primaryTotal = $under === 'Sundry Creditors'
                        ? collect($primaryVouchers)->sum('credit')
                        : collect($primaryVouchers)->sum('debit');
                @endphp
                <tfoot>
                    <tr class="fw-bold">
                        <th colspan="4" class="text-end">Total</th>
                        <th class="text-end">₹ {{ number_format($primaryTotal, 2) }}</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                {{ $under === 'Sundry Creditors' ? 'No Payment Vouchers Found' : 'No Receipt Vouchers Found' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @php
                    $secondaryTotal = $under === 'Sundry Creditors'
                        ? collect($secondaryVouchers)->sum('debit')
                        : collect($secondaryVouchers)->sum('credit');
                @endphp
                <tfoot>
                    <tr class="fw-bold">
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-end">₹ {{ number_format($secondaryTotal, 2) }}</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                No Other Vouchers Found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @php
                    $othersTotal = $under === 'Sundry Creditors'
                        ? collect($journalVouchers)->sum('credit')
                        : collect($journalVouchers)->sum('debit');
                @endphp
                <tfoot>
                    <tr class="fw-bold">
                        <th colspan="5" class="text-end">Total</th>
                        <th class="text-end">₹ {{ number_format($othersTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
{{-- end tab-content --}}

{{-- Pending Summary Footer --}}
<div class="row mt-4">
    <div class="col-md-6 offset-md-6">
        <table class="table table-bordered text-end fw-bold">
            <tr>
                <td>{{ $under === 'Sundry Creditors' ? 'Total Purchase' : 'Total Sales' }}</td>
                <td>
                    ₹ {{ number_format(
                        $under === 'Sundry Creditors'
                            ? collect($primaryVouchers)->sum('credit')
                            : collect($primaryVouchers)->sum('debit'),
                        2
                    ) }}
                </td>
            </tr>
            <tr>
                <td>Total Others</td>
                <td>
                    ₹ {{ number_format(
                        $under === 'Sundry Creditors'
                            ? collect($journalVouchers)->sum('credit')
                            : collect($journalVouchers)->sum('debit'),
                        2
                    ) }}
                </td>
            </tr>
            <tr class="table-dark">
                <td>{{ $under === 'Sundry Creditors' ? 'Total Credit' : 'Total Debit' }}</td>
                <td>₹ {{ number_format($summary['sale'], 2) }}</td>
            </tr>
            <tr>
                <td>{{ $under === 'Sundry Creditors' ? 'Total Payment (Debit)' : 'Total Receipt (Credit)' }}</td>
                <td>₹ {{ number_format($summary['receipts'], 2) }}</td>
            </tr>
            <tr class="{{ ($summary['pending'] ?? 0) > 0 ? 'table-danger' : 'table-success' }}">
                <td>Pending Amount</td>
                <td>₹ {{ number_format($summary['pending'] ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>
</div>
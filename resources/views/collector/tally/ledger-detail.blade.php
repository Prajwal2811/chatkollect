@include('collector.components.header')
    <div id="main-wrapper">
        <div class="nav-header">
            <a href="#" class="brand-logo">
                <svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
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
@include('collector.components.navbar')
@include('collector.components.sidebar')

        <div class="content-body default-height">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title">Ledger Detail</h4>
                            </div>
                            <div class="card-body">

                                @php
                                    // Static demo voucher data (replace with real data later)
                                    $vouchers = [
                                        ['date' => '01-04-2025', 'voucher_no' => 'INV-001', 'type' => 'Sales',   'amount' => 50000],
                                        ['date' => '05-04-2025', 'voucher_no' => 'INV-002', 'type' => 'Sales',   'amount' => 75000],
                                        ['date' => '10-04-2025', 'voucher_no' => 'RCT-010', 'type' => 'Receipt', 'amount' => 100000],
                                        ['date' => '15-04-2025', 'voucher_no' => 'PAY-004', 'type' => 'Payment', 'amount' => 40000],
                                        ['date' => '20-04-2025', 'voucher_no' => 'INV-003', 'type' => 'Sales',   'amount' => 125000],
                                    ];

                                    $total = array_sum(array_column($vouchers, 'amount'));
                                @endphp

                                <div class="table-responsive">
                                      <table id="example13" class="display">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Voucher No.</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($vouchers as $i => $v)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $v['date'] }}</td>
                                                    <td>{{ $v['voucher_no'] }}</td>
                                                    <td>{{ $v['type'] }}</td>
                                                    <td>₹ {{ number_format($v['amount'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary fw-bold">
                                                <td colspan="4">Total</td>
                                                <td>₹ {{ number_format($total, 2) }}</td>
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
@include('collector.components.footer')
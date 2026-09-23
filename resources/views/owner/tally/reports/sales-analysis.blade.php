@include('owner.tally.components.header')
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
    @include('owner.tally.components.navbar')
    @include('owner.tally.components.sidebar')

    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="debtor-card">
                        <div class="debtor-card-header">
                            <h5>Sales to Debtors Summary</h5>
                        </div>
                        <div class="debtor-card-body">
                            <div class="table-responsive">
                                <table id="example" class="display" style="min-width: 845px">
                                    <thead>
                                        <tr>
                                            <th>Particulars</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $debtorRows = [
                                                ['label' => 'Sales to A_Debtors (ex Red and Out)', 'amount' => 120000, 'percent' => 18],
                                                ['label' => 'Sales to B_Debtors (ex Red and Out)', 'amount' => 500000, 'percent' => 74],
                                                ['label' => 'Sales to C_Debtors (ex Red and Out)', 'amount' => 50000,  'percent' => 7],
                                                ['label' => 'Sales to D_Debtors (ex Red and Out)', 'amount' => 10000,  'percent' => 1],
                                                ['label' => 'Sales to Red Marked Debtors',        'amount' => 25000,  'percent' => 4],
                                                ['label' => 'Sales to Out of Limit Debtors',       'amount' => 50000,  'percent' => 7],
                                            ];
                                            $total = collect($debtorRows)->sum('amount');
                                        @endphp

                                        @foreach($debtorRows as $row)
                                            <tr>
                                                <td>{{ $row['label'] }}</td>
                                                <td class="text-end">{{ number_format($row['amount']) }}</td>
                                                <td class="text-center percent-cell">{{ $row['percent'] }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="total-row">
                                            <td>Total</td>
                                            <td class="text-end">{{ number_format($total) }}</td>
                                            <td class="text-center percent-cell">100%</td>
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

    @include('owner.tally.components.footer')
</div>

<style>
.debtor-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    overflow: hidden;
    margin-top: 20px;
}
.debtor-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #eee;
    background: #4E3F6B;
}
.debtor-card-header h5 {
    margin: 0;
    color: #fff;
    font-weight: 600;
}
.debtor-card-body {
    padding: 10px 20px 20px;
}
.debtor-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.debtor-table thead th {
    text-align: left;
    padding: 12px 10px;
    border-bottom: 2px solid #4E3F6B;
    color: #4E3F6B;
    font-weight: 600;
}
.debtor-table tbody td {
    padding: 10px;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
}
.debtor-table tbody tr:hover {
    background: #faf9fc;
}
.percent-cell {
    background: #F6C79A;
    font-weight: 700;
    color: #4E3F6B;
    border-radius: 4px;
}
.total-row td {
    font-weight: 700;
    border-top: 2px solid #4E3F6B;
    color: #4E3F6B;
}
.text-end { text-align: right; }
.text-center { text-align: center; }
</style>
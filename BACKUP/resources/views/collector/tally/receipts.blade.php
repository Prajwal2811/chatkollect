
<div class="container-fluid">

    <div class="card">
        <div class="card-header">
            <h4>Receipts - {{ $ledger }}</h4>
        </div>

        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Voucher No</th>
                        <th>Party</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($receipts as $key => $receipt)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $receipt['date'] }}</td>
                            <td>{{ $receipt['voucher_no'] }}</td>
                            <td>{{ $receipt['party'] }}</td>
                            <td>{{ $receipt['amount'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                No Receipts Found
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>
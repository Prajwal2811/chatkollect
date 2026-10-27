@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if(isset($error))
    <div class="alert alert-danger">
        {{ $error }}
    </div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Date</th>
            <th>Voucher No</th>
            <th>Party</th>
            <th>Amount</th>
        </tr>
    </thead>

    <tbody>
        @forelse($invoices as $invoice)
            <tr>
                <td>{{ $invoice['date'] }}</td>
                <td>{{ $invoice['voucher_no'] }}</td>
                <td>{{ $invoice['party'] }}</td>
                <td>{{ $invoice['amount'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No Invoices Found</td>
            </tr>
        @endforelse
    </tbody>
</table>
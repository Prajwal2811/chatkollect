@include('owner.components.header')
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

    @include('owner.components.navbar')
    @include('owner.components.sidebar')

    <div class="content-body default-height">

        <div class="container-fluid">

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-header d-flex justify-content-between">

                            <h4 class="card-title">
                                Ledger Details
                            </h4>

                            <a href="{{ url()->previous() }}"
                                class="btn btn-secondary">
                                Back
                            </a>

                        </div>

                        <div class="card-body">

                            {{-- Details Table --}}

                            <div class="table-responsive">

                                <table class="table table-bordered table-striped">

                                    <tbody>

                                        <tr>
                                            <th width="30%">Ledger Name</th>
                                            <td>{{ $details['name'] }}</td>
                                        </tr>

                                        <tr>
                                            <th>Parent Group</th>
                                            <td>{{ $details['parent'] }}</td>
                                        </tr>

                                        <tr>
                                            <th>Mailing Name</th>
                                            <td>{{ $details['mailing_name'] }}</td>
                                        </tr>

                                        <tr>
                                            <th>Address</th>
                                            <td>{{ $details['address'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>State</th>
                                            <td>{{ $details['state'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Country</th>
                                            <td>{{ $details['country'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Pincode</th>
                                            <td>{{ $details['pincode'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>GST Registration Type</th>
                                            <td>{{ $details['gst_type'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>GST Number</th>
                                            <td>{{ $details['gst_number'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Opening Balance</th>
                                            <td>₹ {{ number_format((float)$details['opening_balance'],2) }}</td>
                                        </tr>

                                        <tr>
                                            <th>Closing Balance</th>
                                            <td>₹ {{ number_format((float)$details['closing_balance'],2) }}</td>
                                        </tr>

                                        <tr>
                                            <th>Credit Limit</th>
                                            <td>
                                                {{ $details['credit_limit'] != '' ? '₹ '.number_format((float)$details['credit_limit'],2) : '-' }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Phone</th>
                                            <td>{{ $details['phone'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Mobile</th>
                                            <td>{{ $details['mobile'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $details['email'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Contact Person</th>
                                            <td>{{ $details['contact_person'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>PAN Number</th>
                                            <td>{{ $details['pan'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Bank Name</th>
                                            <td>{{ $details['bank_name'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>Account Number</th>
                                            <td>{{ $details['account_number'] ?: '-' }}</td>
                                        </tr>

                                        <tr>
                                            <th>IFSC Code</th>
                                            <td>{{ $details['ifsc'] ?: '-' }}</td>
                                        </tr>

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
@include('owner.components.footer')
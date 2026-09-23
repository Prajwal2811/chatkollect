<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
   <!-- PAGE TITLE HERE -->
	<title>
		RMS |
		@switch(Route::currentRouteName())

			@case('owner.dashboard')
				Dashboard
				@break

			@case('owner.accountants.index')
				Accountants List
				@break

			@case('owner.accountants.create')
				Create Accountant
				@break

			@case('owner.accountants.edit')
				Edit Accountant
				@break

			@case('owner.collectors.index')
				Collectors List
				@break

			@case('owner.collectors.create')
				Create Collector
				@break

			@case('owner.collectors.edit')
				Edit Collector
				@break

			@case('owner.subscription')
				Subscription
				@break

			@case('owner.tally.dashboard')
				Tally Dashboard
				@break

			@case('owner.tally.company.details')
				Company Details
				@break

			@case('owner.tally.company.ledgers')
				Company Ledgers
				@break

			@case('owner.tally.ledger.invoices')
				Ledger Invoices
				@break

			@case('owner.tally.ledger.receipts')
				Ledger Receipts
				@break

			@case('owner.tally.ledger.vouchers')
				Ledger Vouchers
				@break

			@case('owner.ledgers.index')
				Ledgers List
				@break

			@default
				Owner
		@endswitch
	</title>


	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="Prajwal-Ingole">
	 

	<!-- MOBILE SPECIFIC -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png">
	<link href="{{ asset('asset/vendor/bootstrap-select/css/bootstrap-select.min.css') }}" rel="stylesheet">
	<link href="{{ asset('asset/vendor/owl-carousel/owl.carousel.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('asset/vendor/nouislider/nouislider.min.css') }}">
	
	<!-- Style css -->
    <link href="{{ asset('asset/css/style.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


	<link href="{{ asset('asset/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('asset/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet">
	 
	<!-- jQuery CDN -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

	<!-- Bootstrap JS (optional but usually needed) -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>
    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
		<div class="lds-ripple">
			<div></div>
			<div></div>
		</div>
    </div>
    <!--*******************
        Preloader end
    ********************-->
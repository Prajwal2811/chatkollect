	@include('owner.components.header')
    <div id="main-wrapper">
		<div class="nav-header">
            <a href="#" class="brand-logo">
				<svg width="120" height="50" viewBox="0 0 120 50" xmlns="http://www.w3.org/2000/svg">
					<!-- RMS Text -->
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
		@include('owner.components.navbar')
		@include('owner.components.sidebar')

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<!-- Dashboard Content Area -->
						<div class="dashboard-content">
							<div class="row">
								<div class="col-12">
									<div class="dashboard-placeholder text-center">
										<table class="table table-bordered">
                                            <tr>
                                                <th>Company Name</th>
                                                <td>{{ $company }}</td>
                                            </tr>

                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    <span class="badge bg-success">
                                                        Active
                                                    </span>
                                                </td>
                                            </tr>
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
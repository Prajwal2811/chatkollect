@include('accountant.components.header')
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
@include('accountant.components.navbar')
@include('accountant.components.sidebar')

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<!-- Dashboard Content Area -->
						<div class="card">
							<div class="card-body">
								<div class="dashboard-content">
									<div class="text-center py-5">
										<i class="fa fa-cogs" style="font-size: 48px; color: #cccccc;"></i>
											<h4 class="mt-3 text-muted">Under Development</h4>
										<p class="text-muted">This page is currently under development. Please check back later.</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
@include('accountant.components.footer')
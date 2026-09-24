@include('owner.tally.components.header')
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

@include('owner.tally.components.navbar')
@include('owner.tally.components.sidebar')

		@php
			$owner = Auth::guard('owner')->user();

			$tallyConnectionRecord = \App\Models\TallyConnection::where('owner_id', $owner->id)->first();

			$isTallyConneted = $tallyConnectionRecord
				&& !empty($tallyConnectionRecord->tailscale_ip)
				&& !empty($tallyConnectionRecord->port);

			$ownerBankDetail = \App\Models\OwnerBankDetail::where('owner_id', $owner->id)->first();

			$isBankDetailsAdded = !is_null($ownerBankDetail);

			$company = $companies->first();
		@endphp

		<div class="content-body default-height tally-dashboard">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<div class="dashboard-content">

							<div class="tds-page-head mb-4">
								<div>
									<h2 class="tds-page-title">Tally Overview </h2>
									<p class="tds-page-sub">Your Tally connection, at a glance</p>
								</div>
								<div class="tds-actions">
									@if($isBankDetailsAdded)
										<button type="button" id="editBankDetailsBtn" class="tds-btn tds-btn-ghost">
											<i class="fas fa-university"></i>
											<span>Bank Details</span>
										</button>
									@endif

									<button type="button" id="editTallyConnectionBtn" class="tds-btn tds-btn-ghost">
										<i class="fas fa-plug"></i>
										<span>Connection</span>
									</button>

									<button type="button" id="syncTallyBtn" class="tds-btn tds-btn-primary">
										<i class="fas fa-sync-alt"></i>
										<span>Sync From Tally</span>
									</button>
								</div>
							</div>

							<div class="row mb-4 g-3">
								<div class="col-xl-4 col-md-6">
									<div class="tds-stat-card">
										<div class="tds-stat-icon tds-icon-violet">
											<i class="fas fa-building"></i>
										</div>
										<div class="tds-stat-body">
											<span class="tds-stat-label">Companies</span>
											<span class="tds-stat-value">{{ count($companies) }}</span>
										</div>
									</div>
								</div>

								<div class="col-xl-4 col-md-6">
									<div class="tds-stat-card">
										<div class="tds-stat-icon {{ $isTallyConneted ? 'tds-icon-green' : 'tds-icon-red' }}">
											<i class="fas {{ $isTallyConneted ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
										</div>
										<div class="tds-stat-body">
											<span class="tds-stat-label">Tally Status</span>
											<span class="tds-stat-value">
												{{ $isTallyConneted ? 'Connected' : 'Disconnected' }}
											</span>
										</div>
									</div>
								</div>

								<div class="col-xl-4 col-md-6">
									<div class="tds-stat-card">
										<div class="tds-stat-icon tds-icon-amber">
											<i class="fas fa-clock"></i>
										</div>
										<div class="tds-stat-body">
											<span class="tds-stat-label">Last Sync</span>
											<span class="tds-stat-value tds-stat-value-sm" id="lastSyncTime">
												{{ session('last_sync')
													? \Carbon\Carbon::parse(session('last_sync'))->format('d M Y, H:i')
													: 'Never Synced'
												}}
											</span>
										</div>
									</div>
								</div>
							</div>

							<div id="syncMessage"></div>

							<h3 class="tds-section-title">
								<i class="fas fa-building"></i>
								Connected Company
							</h3>

							@if($company)
								<div class="tds-company-hero">
									<div class="tds-company-hero-glow"></div>

									<div class="tds-company-mark">
										<i class="fas fa-building"></i>
									</div>

									<div class="tds-company-info">
										<div class="tds-company-status">
											<span class="tds-pulse-dot"></span>
											Active &middot; Synced with Tally
										</div>
										<h4 class="tds-company-name">{{ $company->company_name }}</h4>
									</div>

									<div class="tds-company-actions">
										<a href="{{ route('owner.tally.company.ledgers', urlencode($company->company_name)) }}"
											class="tds-btn tds-btn-light">
											<i class="fas fa-book"></i>
											<span>Ledgers</span>
										</a>

										<a href="{{ route('owner.tally.voucher.mappings', urlencode($company->company_name)) }}"
											class="tds-btn tds-btn-amber">
											<i class="fas fa-random"></i>
											<span>Voucher Mapping</span>
										</a>
									</div>
								</div>
							@else
								<div class="tds-empty-state">
									<i class="fas fa-building"></i>
									<p>No company found in Tally yet. Connect Tally and run a sync to bring your company in.</p>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>

		<style>
			.tally-dashboard {
				--tds-violet: #4E3F6B;
				--tds-violet-deep: #392c52;
				--tds-violet-tint: #EFEAFB;
				--tds-lilac: #E9E2F8;
				--tds-amber: #C98A2C;
				--tds-amber-tint: #FBF0DD;
				--tds-green: #1F9D6B;
				--tds-green-tint: #E4F7EE;
				--tds-red: #D14B4B;
				--tds-red-tint: #FBEBEB;
				--tds-ink: #2A2438;
				--tds-muted: #7C7290;
				font-family: inherit;
			}

			/* ---------- Page head ---------- */
			.tds-page-head {
				display: flex;
				align-items: flex-end;
				justify-content: space-between;
				flex-wrap: wrap;
				gap: 16px;
			}

			.tds-page-title {
				font-weight: 700;
				color: var(--tds-ink);
				margin: 0 0 4px;
				letter-spacing: -0.01em;
			}
			.tds-page-sub {
				margin: 0;
				color: var(--tds-muted);
				font-size: 0.92rem;
			}

			.tds-actions {
				display: flex;
				gap: 10px;
				flex-wrap: wrap;
			}

			/* ---------- Buttons ---------- */
			.tds-btn {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				padding: 10px 18px;
				border-radius: 10px;
				font-size: 0.9rem;
				font-weight: 600;
				border: 1px solid transparent;
				cursor: pointer;
				transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
				text-decoration: none;
				line-height: 1;
			}
			.tds-btn:hover { transform: translateY(-1px); text-decoration: none; }

			.tds-btn-primary {
				background: linear-gradient(135deg, var(--tds-violet), var(--tds-violet-deep));
				color: #fff;
				box-shadow: 0 6px 16px rgba(78, 63, 107, 0.28);
			}
			.tds-btn-primary:hover { box-shadow: 0 8px 20px rgba(78, 63, 107, 0.36); color: #fff; }

			.tds-btn-ghost {
				background: #fff;
				color: var(--tds-violet);
				border-color: #E4DEF3;
			}
			.tds-btn-ghost:hover { background: var(--tds-violet-tint); color: var(--tds-violet); }

			.tds-btn-light {
				background: var(--tds-violet-tint);
				color: var(--tds-violet);
			}
			.tds-btn-light:hover { background: var(--tds-lilac); color: var(--tds-violet-deep); }

			.tds-btn-amber {
				background: var(--tds-amber-tint);
				color: #8A5D16;
			}
			.tds-btn-amber:hover { background: #f5e2bb; color: #6f4a10; }

			/* ---------- Stat cards ---------- */
			.tds-stat-card {
				display: flex;
				align-items: center;
				gap: 16px;
				background: #fff;
				border-radius: 14px;
				padding: 20px;
				height: 100%;
				box-shadow: 0 1px 2px rgba(42, 36, 56, 0.04), 0 8px 20px rgba(42, 36, 56, 0.05);
				border: 1px solid #EFECF7;
				transition: box-shadow 0.2s ease, transform 0.2s ease;
			}
			.tds-stat-card:hover {
				box-shadow: 0 4px 8px rgba(42, 36, 56, 0.06), 0 14px 28px rgba(42, 36, 56, 0.08);
				transform: translateY(-2px);
			}

			.tds-stat-icon {
				width: 48px;
				height: 48px;
				min-width: 48px;
				border-radius: 12px;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 1.15rem;
			}
			.tds-icon-violet { background: var(--tds-violet-tint); color: var(--tds-violet); }
			.tds-icon-green  { background: var(--tds-green-tint); color: var(--tds-green); }
			.tds-icon-red    { background: var(--tds-red-tint); color: var(--tds-red); }
			.tds-icon-amber  { background: var(--tds-amber-tint); color: var(--tds-amber); }

			.tds-stat-body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
			.tds-stat-label { font-size: 0.78rem; color: var(--tds-muted); font-weight: 600; }
			.tds-stat-value { font-size: 1.4rem; font-weight: 700; color: var(--tds-ink); }
			.tds-stat-value-sm { font-size: 1rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

			/* ---------- Section title ---------- */
			.tds-section-title {
				display: flex;
				align-items: center;
				gap: 10px;
				font-size: 1.05rem;
				font-weight: 700;
				color: var(--tds-ink);
				margin-bottom: 16px;
			}
			.tds-section-title i { color: var(--tds-violet); font-size: 0.95rem; }

			/* ---------- Company hero ---------- */
			.tds-company-hero {
				position: relative;
				display: flex;
				align-items: center;
				gap: 22px;
				flex-wrap: wrap;
				background: linear-gradient(120deg, var(--tds-violet) 0%, var(--tds-violet-deep) 100%);
				border-radius: 18px;
				padding: 28px 30px;
				overflow: hidden;
				box-shadow: 0 14px 34px rgba(57, 44, 82, 0.28);
			}

			.tds-company-hero-glow {
				position: absolute;
				top: -60px;
				right: -60px;
				width: 220px;
				height: 220px;
				background: radial-gradient(circle, rgba(255,255,255,0.16), transparent 70%);
				pointer-events: none;
			}

			.tds-company-mark {
				width: 64px;
				height: 64px;
				min-width: 64px;
				border-radius: 16px;
				background: rgba(255,255,255,0.14);
				border: 1px solid rgba(255,255,255,0.22);
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 1.6rem;
				color: #fff;
			}

			.tds-company-info { flex: 1; min-width: 220px; z-index: 1; }

			.tds-company-status {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				font-size: 0.8rem;
				font-weight: 600;
				color: #D9F7E7;
				margin-bottom: 6px;
			}

			.tds-pulse-dot {
				width: 8px;
				height: 8px;
				border-radius: 50%;
				background: #4CE0A0;
				box-shadow: 0 0 0 rgba(76, 224, 160, 0.6);
				animation: tds-pulse 2s infinite;
			}

			@keyframes tds-pulse {
				0%   { box-shadow: 0 0 0 0 rgba(76, 224, 160, 0.55); }
				70%  { box-shadow: 0 0 0 9px rgba(76, 224, 160, 0); }
				100% { box-shadow: 0 0 0 0 rgba(76, 224, 160, 0); }
			}

			.tds-company-name {
				color: #fff;
				font-weight: 700;
				margin: 0;
				letter-spacing: -0.01em;
			}

			.tds-company-actions {
				display: flex;
				gap: 10px;
				flex-wrap: wrap;
				z-index: 1;
			}

			/* ---------- Empty state ---------- */
			.tds-empty-state {
				background: #fff;
				border: 1px dashed #D9D2ED;
				border-radius: 16px;
				padding: 40px 24px;
				text-align: center;
				color: var(--tds-muted);
			}
			.tds-empty-state i {
				font-size: 1.8rem;
				color: var(--tds-violet);
				margin-bottom: 10px;
				display: block;
			}
			.tds-empty-state p { margin: 0; font-size: 0.92rem; }

			@media (max-width: 576px) {
				.tds-page-head { align-items: flex-start; }
				.tds-company-hero { padding: 22px 18px; }
			}
		</style>

		@include('owner.tally.components.footer')

		{{-- ================= BANK DETAILS MODAL (mandatory, non-closable) ================= --}}
		{{-- Ye modal sabse pehle check hota hai. Jab tak owner ki bank details DB me save
			 nahi hain, tab tak ye forcefully open rahega (koi close button nahi), aur
			 Tally connect modal ka auto-open isi ke baad trigger hoga. --}}
		<div class="modal fade" id="bankDetailsModal" tabindex="-1"
			data-bs-backdrop="{{ $isBankDetailsAdded ? 'true' : 'static' }}"
			data-bs-keyboard="{{ $isBankDetailsAdded ? 'true' : 'false' }}"
			aria-labelledby="bankDetailsModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title" id="bankDetailsModalLabel">
							<i class="fas fa-university text-primary me-2"></i>
							<span id="bankDetailsModalTitleText">
								{{ $isBankDetailsAdded ? 'Edit Bank Details' : 'Add Your Bank Details' }}
							</span>
						</h5>

						{{-- Close (X) button sirf tab dikhega jab bank details already saved hain.
							 Jab missing ho, add karna mandatory hai isliye X nahi hai. --}}
						@if($isBankDetailsAdded)
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						@endif
					</div>

					<form id="bankDetailsForm" action="{{ route('owner.bank.save') }}" method="POST">
						@csrf

						<div class="modal-body">
							<p class="text-muted mb-3" id="bankDetailsModalDesc">
								@if($isBankDetailsAdded)
									Aapki bank details neeche di gayi hain. Zaroorat ho to update karein.
								@else
									Please add your bank details before continuing. This is required once and can be
									used for settlements/payouts.
								@endif
							</p>

							<div class="row">
								<div class="col-md-6 mb-3">
									<label for="account_holder_name" class="form-label">
										Account Holder Name <span class="text-danger">*</span>
									</label>
									<input type="text" class="form-control" id="account_holder_name"
										name="account_holder_name" placeholder="e.g. Rahul Sharma"
										value="{{ old('account_holder_name', $ownerBankDetail->account_holder_name ?? '') }}" required>
								</div>

								<div class="col-md-6 mb-3">
									<label for="bank_name" class="form-label">
										Bank Name <span class="text-danger">*</span>
									</label>
									<input type="text" class="form-control" id="bank_name" name="bank_name"
										placeholder="e.g. State Bank of India"
										value="{{ old('bank_name', $ownerBankDetail->bank_name ?? '') }}" required>
								</div>

								<div class="col-md-6 mb-3">
									<label for="account_number" class="form-label">
										Account Number <span class="text-danger">*</span>
									</label>
									<input type="text" class="form-control" id="account_number" name="account_number"
										placeholder="e.g. 123456789012"
										value="{{ old('account_number', $ownerBankDetail->account_number ?? '') }}" required>
								</div>

								<div class="col-md-6 mb-3">
									<label for="ifsc_code" class="form-label">
										IFSC Code <span class="text-danger">*</span>
									</label>
									<input type="text" class="form-control text-uppercase" id="ifsc_code" name="ifsc_code"
										placeholder="e.g. SBIN0001234"
										value="{{ old('ifsc_code', $ownerBankDetail->ifsc_code ?? '') }}" required>
								</div>

								<div class="col-md-6 mb-3">
									<label for="upi" class="form-label">UPI</label>
									<input type="text" class="form-control" id="upi" name="upi"
										placeholder="e.g. googlepay@upi"
										value="{{ old('upi', $ownerBankDetail->upi ?? '') }}">
								</div>

							</div>

							<div id="bankDetailsMessage"></div>
						</div>

						<div class="modal-footer">
							<button type="submit" id="bankDetailsSubmitBtn" class="btn btn-primary w-100">
								<i class="fas fa-save me-2"></i>
								<span id="bankDetailsSubmitBtnText">
									{{ $isBankDetailsAdded ? 'Update Bank Details' : 'Save Bank Details' }}
								</span>
							</button>
						</div>
					</form>

				</div>
			</div>
		</div>
		{{-- ================= /BANK DETAILS MODAL ================= --}}

		{{-- ================= TALLY CONNECT / EDIT MODAL ================= --}}
		{{-- Isko main-wrapper ke bahar, body ke bilkul end me rakha hai taaki parent divs ka
			 overflow/transform/position CSS ye modal ko clip ya hide na kare.
			 Ab ye modal hamesha DOM me rahega — disconnected hone par forcefully open hoga
			 (bina close button ke), aur connected hone par "Edit Tally Connection" button se
			 manually open hoga (close button ke saath), aur existing values pre-filled hongi.
			 NOTE: Iska auto-open sirf tab trigger hoga jab bank details already save ho chuki hain. --}}
		<div class="modal fade" id="tallyConnectModal" tabindex="-1"
			data-bs-backdrop="{{ $isTallyConneted ? 'true' : 'static' }}"
			data-bs-keyboard="{{ $isTallyConneted ? 'true' : 'false' }}"
			aria-labelledby="tallyConnectModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title" id="tallyConnectModalLabel">
							<i class="fas fa-plug text-primary me-2"></i>
							<span id="tallyConnectModalTitleText">
								{{ $isTallyConneted ? 'Edit Tally Connection' : 'Connect Your Tally' }}
							</span>
						</h5>

						{{-- Close (X) button sirf tab dikhega jab Tally already connected ho.
							 Jab disconnected ho, connect karna mandatory hai isliye X nahi hai. --}}
						@if($isTallyConneted)
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						@endif
					</div>

					<form id="tallyConnectForm" action="{{ route('owner.tally.connect') }}" method="POST">
						@csrf

						<div class="modal-body">
							<p class="text-muted mb-3" id="tallyConnectModalDesc">
								@if($isTallyConneted)
									Aapki Tally connection details neeche di gayi hain. Zaroorat ho to update karein.
								@else
									You need to connect Tally before you can access the dashboard. Please fill in the details below.
								@endif
							</p>

							<div class="mb-3">
								<label for="tailscale_ip" class="form-label">Tailscale IP <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="tailscale_ip" name="tailscale_ip"
									placeholder="e.g. 100.x.x.x"
									value="{{ old('tailscale_ip', $tallyConnectionRecord->tailscale_ip ?? '') }}"
									required>
							</div>

							<div class="mb-3">
								<label for="port" class="form-label">Port <span class="text-danger">*</span></label>
								<input type="number" class="form-control" id="port" name="port"
									placeholder="e.g. 9000"
									value="{{ old('port', $tallyConnectionRecord->port ?? '') }}"
									required>
							</div>

							<div id="tallyConnectMessage"></div>
						</div>

						<div class="modal-footer">
							<button type="submit" id="tallyConnectSubmitBtn" class="btn btn-primary w-100">
								<i class="fas fa-link me-2"></i>
								<span id="tallyConnectSubmitBtnText">
									{{ $isTallyConneted ? 'Update Connection' : 'Connect Now' }}
								</span>
							</button>
						</div>
					</form>

				</div>
			</div>
		</div>
		{{-- ================= /TALLY CONNECT / EDIT MODAL ================= --}}

		{{-- ================= SYNC CONFIRMATION MODAL ================= --}}
		<div class="modal fade" id="syncConfirmModal" tabindex="-1" aria-labelledby="syncConfirmModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content sync-confirm-modal">

					<div class="modal-body text-center pt-4 pb-3 px-4">

						<div class="sync-icon-wrapper mb-3">
							<i class="fas fa-cloud-download-alt"></i>
						</div>

						<h4 class="mb-2 fw-bold" id="syncConfirmModalLabel">Sync Data From Tally?</h4>
						<p class="text-muted mb-4">
							This will pull the latest companies, ledgers and vouchers from your connected Tally instance.
						</p>

						<div class="sync-info-list text-start mb-4">
							<div class="sync-info-item">
								<i class="fas fa-check-circle text-success"></i>
								<span>Fresh data will be fetched from Tally</span>
							</div>
							<div class="sync-info-item">
								<i class="fas fa-exclamation-circle text-danger"></i>
								<span>Existing data on this platform will be <strong>overwritten</strong></span>
							</div>
							<div class="sync-info-item">
								<i class="fas fa-undo text-warning"></i>
								<span>This action <strong>cannot be undone</strong></span>
							</div>
						</div>

					</div>

					<div class="modal-footer border-0 pt-0 pb-4 px-4">
						<button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">
							Cancel
						</button>
						<button type="button" id="confirmSyncBtn" class="btn btn-primary flex-fill">
							<i class="fas fa-sync-alt me-2"></i> Yes, Sync Now
						</button>
					</div>

				</div>
			</div>
		</div>
		{{-- ================= /SYNC CONFIRMATION MODAL ================= --}}

		{{-- ================= SYNC LOADING MODAL ================= --}}
		<div class="modal fade" id="syncLoadingModal" tabindex="-1"
			data-bs-backdrop="static"
			data-bs-keyboard="false"
			aria-hidden="true">

			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">

					<div class="modal-body text-center py-5">

						<div class="mb-3">
							<i class="fas fa-sync-alt fa-spin text-primary"
							style="font-size: 40px;"></i>
						</div>

						<h5 class="mb-3">Syncing data from Tally...</h5>

						<!-- Percentage -->
						<h3 id="syncProgressPercent" class="mb-3">0%</h3>

						<!-- Progress Bar -->
						<div class="progress"
							style="height: 20px; border-radius: 10px;">

							<div id="syncProgressBar"
								class="progress-bar progress-bar-striped progress-bar-animated"
								role="progressbar"
								style="width: 0%;"
								aria-valuenow="0"
								aria-valuemin="0"
								aria-valuemax="100">
							</div>

						</div>

						<small id="syncProgressText"
							class="text-muted d-block mt-3">
							Starting sync...
						</small>

					</div>

				</div>
			</div>
		</div>
		{{-- ================= /SYNC LOADING MODAL ================= --}}

		{{-- ================= SYNC DEFAULTS MODAL (Credit Period / Interest Rate) ================= --}}
		<div class="modal fade" id="syncDefaultsModal" tabindex="-1"
			data-bs-backdrop="static" data-bs-keyboard="false"
			aria-labelledby="syncDefaultsModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title" id="syncDefaultsModalLabel">
							<i class="fas fa-sliders-h text-primary me-2"></i>
							Set Default Values
						</h5>
					</div>

					<div class="modal-body">
						<p class="text-muted mb-3">
							Some ledgers didn't get these values from Tally. You can set separate defaults for Debtors and Creditors — ledgers that already have values fetched from Tally won't be touched.
						</p>

						<div class="row">
							<div class="col-md-6 mb-3 mb-md-0">
								<h6 class="fw-bold mb-3"><i class="fas fa-arrow-down text-success me-1"></i> Debtors</h6>

								<div class="mb-3">
									<label class="form-label">Mobile Number</label>
									<input type="text" class="form-control" id="debtor_mobile_number" placeholder="e.g. 9876543210">
								</div>
								<div class="mb-3">
									<label class="form-label">Credit Period (days)</label>
									<input type="number" min="0" class="form-control" id="debtor_credit_period" placeholder="e.g. 30">
								</div>
								<div class="mb-3">
									<label class="form-label">Interest Rate (% p.a.)</label>
									<input type="number" min="0" step="0.01" class="form-control" id="debtor_interest_rate" placeholder="e.g. 12">
								</div>
								<div class="mb-3">
									<label class="form-label">Balance Limit</label>
									<input type="number" min="0" step="0.01" class="form-control" id="debtor_balance_limit" placeholder="e.g. 50000">
								</div>
							</div>

							<div class="col-md-6">
								<h6 class="fw-bold mb-3"><i class="fas fa-arrow-up text-danger me-1"></i> Creditors</h6>

								<div class="mb-3">
									<label class="form-label">Mobile Number</label>
									<input type="text" class="form-control" id="creditor_mobile_number" placeholder="e.g. 9876543210">
								</div>
								<div class="mb-3">
									<label class="form-label">Credit Period (days)</label>
									<input type="number" min="0" class="form-control" id="creditor_credit_period" placeholder="e.g. 30">
								</div>
								<div class="mb-3">
									<label class="form-label">Interest Rate (% p.a.)</label>
									<input type="number" min="0" step="0.01" class="form-control" id="creditor_interest_rate" placeholder="e.g. 12">
								</div>
								<div class="mb-3">
									<label class="form-label">Balance Limit</label>
									<input type="number" min="0" step="0.01" class="form-control" id="creditor_balance_limit" placeholder="e.g. 50000">
								</div>
							</div>
						</div>

						<div id="syncDefaultsMessage"></div>
					</div>

					<div class="modal-footer">
						<button type="button" id="skipSyncDefaultsBtn" class="btn btn-outline-secondary flex-fill">
							Skip For Now
						</button>
						<button type="button" id="applySyncDefaultsBtn" class="btn btn-primary flex-fill">
							<i class="fas fa-check me-2"></i>
							Apply Defaults
						</button>
					</div>

				</div>
			</div>
		</div>
		{{-- ================= /SYNC DEFAULTS MODAL ================= --}}

		<style>
			.sync-confirm-modal {
				border-radius: 16px;
				border: none;
				overflow: hidden;
			}

			.sync-icon-wrapper {
				width: 72px;
				height: 72px;
				margin: 0 auto;
				border-radius: 50%;
				background: linear-gradient(135deg, #E9E2F8, #d7c9f5);
				display: flex;
				align-items: center;
				justify-content: center;
			}

			.sync-icon-wrapper i {
				font-size: 30px;
				color: #4E3F6B;
			}

			.sync-info-list {
				background: #f8f7fc;
				border-radius: 12px;
				padding: 14px 16px;
			}

			.sync-info-item {
				display: flex;
				align-items: flex-start;
				gap: 10px;
				font-size: 14px;
				padding: 6px 0;
			}

			.sync-info-item i {
				margin-top: 3px;
				width: 16px;
			}
		</style>

		<script>
			$(function () {

				// ========== BANK DETAILS FLOW (checked first) ==========

				var $bankModal = $('#bankDetailsModal');
				var isBankDetailsAdded = @json($isBankDetailsAdded);

				if ($bankModal.length) {
					$bankModal.appendTo('body');
				} else {
					console.error('#bankDetailsModal element DOM me nahi mila.');
				}

				function getBankModalInstance() {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						return bootstrap.Modal.getOrCreateInstance($bankModal[0], {
							backdrop: isBankDetailsAdded ? true : 'static',
							keyboard: isBankDetailsAdded
						});
					}
					return null;
				}

				function showBankModal() {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						var modalInstance = getBankModalInstance();
						modalInstance.show();
					} else if (typeof $.fn.modal !== 'undefined') {
						$bankModal.modal({
							backdrop: isBankDetailsAdded ? true : 'static',
							keyboard: isBankDetailsAdded,
							show: true
						});
					} else {
						console.error('Bootstrap JS load nahi hua hai. Check script tag order.');
					}
				}

				function hideBankModal() {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						var instance = bootstrap.Modal.getInstance($bankModal[0]);
						if (instance) instance.hide();
					} else if (typeof $.fn.modal !== 'undefined') {
						$bankModal.modal('hide');
					}
				}

				// Bank details missing -> forcefully open this modal FIRST, before Tally check
				if (!isBankDetailsAdded && $bankModal.length) {
					if (document.readyState === 'complete') {
						showBankModal();
					} else {
						$(window).on('load', showBankModal);
					}
				}

				// "Edit Bank Details" button - manually modal open karega (edit mode me)
				$('#editBankDetailsBtn').on('click', function () {
					$('#bankDetailsMessage').html('');
					showBankModal();
				});

				$('#bankDetailsForm').on('submit', function (e) {
					e.preventDefault();

					let form = $(this);
					let submitBtn = $('#bankDetailsSubmitBtn');
					let originalBtnText = isBankDetailsAdded ? 'Update Bank Details' : 'Save Bank Details';

					submitBtn.prop('disabled', true);
					submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i> ' + (isBankDetailsAdded ? 'Updating...' : 'Saving...'));

					$('#bankDetailsMessage').html('');

					$.ajax({
						url: form.attr('action'),
						type: 'POST',
						data: form.serialize(),
						success: function (response) {
							$('#bankDetailsMessage').html(`
								<div class="alert alert-success mt-2">
									${response.message || (isBankDetailsAdded ? 'Bank details updated successfully.' : 'Bank details saved successfully.')}
								</div>
							`);

							setTimeout(function () {
								location.reload();
							}, 1000);
						},
						error: function (xhr) {
							let message = isBankDetailsAdded
								? 'Bank details update nahi ho payi. Details check karke phir try karo.'
								: 'Bank details save nahi hui. Details check karke phir try karo.';

							if (xhr.responseJSON?.message) {
								message = xhr.responseJSON.message;
							}

							$('#bankDetailsMessage').html(`
								<div class="alert alert-danger mt-2">
									${message}
								</div>
							`);
						},
						complete: function () {
							submitBtn.prop('disabled', false);
							submitBtn.html('<i class="fas fa-save me-2"></i> ' + originalBtnText);
						}
					});
				});

				// ========== TALLY CONNECT FLOW (checked only after bank details exist) ==========

				var $modal = $('#tallyConnectModal');
				var isTallyConneted = @json($isTallyConneted);

				if ($modal.length) {
					$modal.appendTo('body');
				} else {
					console.error('#tallyConnectModal element DOM me nahi mila.');
				}

				function getModalInstance() {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						// Bootstrap 5
						return bootstrap.Modal.getOrCreateInstance($modal[0], {
							backdrop: isTallyConneted ? true : 'static',
							keyboard: isTallyConneted
						});
					}
					return null;
				}

				function showTallyModal() {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						// Bootstrap 5
						var modalInstance = getModalInstance();
						modalInstance.show();
					} else if (typeof $.fn.modal !== 'undefined') {
						// Bootstrap 4
						$modal.modal({
							backdrop: isTallyConneted ? true : 'static',
							keyboard: isTallyConneted,
							show: true
						});
					} else {
						console.error('Bootstrap JS load nahi hua hai. Check script tag order.');
					}
				}
				
				if (isBankDetailsAdded && !isTallyConneted && $modal.length) {
					if (document.readyState === 'complete') {
						showTallyModal();
					} else {
						$(window).on('load', showTallyModal);
					}
				}

				// "Edit Tally Connection" button - manually modal open karega (edit mode me)
				$('#editTallyConnectionBtn').on('click', function () {
					$('#tallyConnectMessage').html('');
					showTallyModal();
				});

				$('#tallyConnectForm').on('submit', function (e) {
					e.preventDefault();

					let form = $(this);
					let submitBtn = $('#tallyConnectSubmitBtn');
					let originalBtnText = isTallyConneted ? 'Update Connection' : 'Connect Now';

					submitBtn.prop('disabled', true);
					submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i> ' + (isTallyConneted ? 'Updating...' : 'Connecting...'));

					$('#tallyConnectMessage').html('');

					$.ajax({
						url: form.attr('action'),
						type: 'POST',
						data: form.serialize(),
						success: function (response) {
							$('#tallyConnectMessage').html(`
								<div class="alert alert-success mt-2">
									${response.message || (isTallyConneted ? 'Tally connection updated successfully.' : 'Tally connected successfully.')}
								</div>
							`);

							setTimeout(function () {
								location.reload();
							}, 1200);
						},
						error: function (xhr) {
							let message = isTallyConneted
								? 'Tally connection update nahi ho payi. Details check karke phir try karo.'
								: 'Tally connect nahi ho paya. Details check karke phir try karo.';

							if (xhr.responseJSON?.message) {
								message = xhr.responseJSON.message;
							}

							$('#tallyConnectMessage').html(`
								<div class="alert alert-danger mt-2">
									${message}
								</div>
							`);
						},
						complete: function () {
							submitBtn.prop('disabled', false);
							submitBtn.html('<i class="fas fa-link me-2"></i> ' + originalBtnText);
						}
					});
				});

				var $syncConfirmModal = $('#syncConfirmModal');
				var $syncLoadingModal = $('#syncLoadingModal');
				var $syncDefaultsModal = $('#syncDefaultsModal');

				function getBsModal($el, options) {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						return bootstrap.Modal.getOrCreateInstance($el[0], options || {});
					}
					return null;
				}

				function showModalEl($el, options) {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						getBsModal($el, options).show();
					} else if (typeof $.fn.modal !== 'undefined') {
						$el.modal(Object.assign({ show: true }, options || {}));
					}
				}

				function hideModalEl($el) {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						var instance = bootstrap.Modal.getInstance($el[0]);
						if (instance) instance.hide();
					} else if (typeof $.fn.modal !== 'undefined') {
						$el.modal('hide');
					}
				}

				// Step 1: Sync button click -> confirmation modal open (with overwrite warning)
				$('#syncTallyBtn').click(function () {
					$('#syncMessage').html('');
					showModalEl($syncConfirmModal);
				});

				// Step 2: Confirm button click -> close confirm modal, open loading modal, run sync
				$('#confirmSyncBtn').click(function () {

					hideModalEl($syncConfirmModal);

					// Reset progress
					$('#syncProgressBar')
						.css('width', '0%')
						.attr('aria-valuenow', 0);

					$('#syncProgressPercent').text('0%');
					$('#syncProgressText').text('Starting sync...');

					showModalEl($syncLoadingModal, {
						backdrop: 'static',
						keyboard: false
					});

					// Fake progress for better UX
					let progress = 0;

					let progressInterval = setInterval(function () {

						if (progress < 90) {

							// Slow down as it approaches 90%
							if (progress < 30) {
								progress += 3;
							} else if (progress < 60) {
								progress += 2;
							} else {
								progress += 1;
							}

							updateSyncProgress(progress);

						}

					}, 500);


					$.ajax({

						url: "{{ route('owner.tally.sync-all') }}",

						type: "POST",

						data: {
							_token: "{{ csrf_token() }}"
						},

						success: function (response) {

							// Stop fake progress
							clearInterval(progressInterval);

							// Complete progress
							updateSyncProgress(100);

							$('#syncProgressText').text('Sync completed successfully!');

							$('#syncMessage').html(`
								<div class="alert alert-success alert-dismissible fade show mt-3">
									${response.message || 'Data synced successfully.'}
									<button type="button"
											class="btn-close"
											data-bs-dismiss="alert">
									</button>
								</div>
							`);

							if (response.last_sync) {
								$('#lastSyncTime').text(response.last_sync);
							}

							// Small delay so user can see 100%
							setTimeout(function () {
								hideModalEl($syncLoadingModal);

								// Defaults modal sirf tab dikhega jab backend confirm kare
								// ki ye is owner ka pehla sync tha (DB me ledger data nahi tha).
								// Doosri/teesri baar sync karne par ye seedha reload ho jaayega.
								if (response.show_defaults_prompt) {
									$('#syncDefaultsMessage').html('');
									$('#default_credit_period').val('');
									$('#default_interest_rate').val('');
									showModalEl($syncDefaultsModal, {
										backdrop: 'static',
										keyboard: false
									});
								} else {
									location.reload();
								}
							}, 800);
						},

						error: function (xhr) {

							clearInterval(progressInterval);

							let message = 'Failed to sync data from Tally.';

							if (xhr.responseJSON?.message) {
								message = xhr.responseJSON.message;
							}

							$('#syncProgressText').text('Sync failed.');

							$('#syncMessage').html(`
								<div class="alert alert-danger alert-dismissible fade show mt-3">
									${message}
									<button type="button"
											class="btn-close"
											data-bs-dismiss="alert">
									</button>
								</div>
							`);

							setTimeout(function () {
								hideModalEl($syncLoadingModal);
							}, 500);
						},

						complete: function () {

							// Safety clear
							clearInterval(progressInterval);

						}

					});

				});

				// Skip button -> just close and reload, no defaults applied
				$('#skipSyncDefaultsBtn').on('click', function () {
					hideModalEl($syncDefaultsModal);
					location.reload();
				});

				// Apply button -> send defaults to server, then reload
				$('#applySyncDefaultsBtn').on('click', function () {

					let applyBtn = $(this);

					let debtor = {
						mobile_number:  $('#debtor_mobile_number').val(),
						credit_period:  $('#debtor_credit_period').val(),
						interest_rate:  $('#debtor_interest_rate').val(),
						balance_limit:  $('#debtor_balance_limit').val(),
					};

					let creditor = {
						mobile_number:  $('#creditor_mobile_number').val(),
						credit_period:  $('#creditor_credit_period').val(),
						interest_rate:  $('#creditor_interest_rate').val(),
						balance_limit:  $('#creditor_balance_limit').val(),
					};

					let hasAnyValue = Object.values(debtor).some(v => v !== '') ||
									Object.values(creditor).some(v => v !== '');

					if (!hasAnyValue) {
						$('#syncDefaultsMessage').html(`
							<div class="alert alert-warning mt-2">
								Please enter at least one value, or use Skip.
							</div>
						`);
						return;
					}

					applyBtn.prop('disabled', true);
					applyBtn.html('<i class="fas fa-spinner fa-spin me-2"></i> Applying...');

					$('#syncDefaultsMessage').html('');

					$.ajax({
						url: "{{ route('owner.tally.apply-sync-defaults') }}",
						type: 'POST',
						data: {
							_token: "{{ csrf_token() }}",
							debtor: debtor,
							creditor: creditor
						},
						success: function (response) {
							$('#syncDefaultsMessage').html(`
								<div class="alert alert-success mt-2">
									${response.message || 'Defaults applied successfully.'}
								</div>
							`);

							setTimeout(function () {
								hideModalEl($syncDefaultsModal);
								location.reload();
							}, 800);
						},
						error: function (xhr) {
							let message = 'Defaults apply nahi ho paye. Phir try karo.';

							if (xhr.responseJSON?.message) {
								message = xhr.responseJSON.message;
							}

							$('#syncDefaultsMessage').html(`
								<div class="alert alert-danger mt-2">
									${message}
								</div>
							`);

							applyBtn.prop('disabled', false);
							applyBtn.html('<i class="fas fa-check me-2"></i> Apply Defaults');
						}
					});
				});

			});

			function updateSyncProgress(percent) {

				percent = Math.min(100, Math.max(0, percent));

				$('#syncProgressBar')
					.css('width', percent + '%')
					.attr('aria-valuenow', percent);

				$('#syncProgressPercent').text(percent + '%');

				if (percent < 30) {
					$('#syncProgressText').text('Connecting to Tally...');
				}
				else if (percent < 60) {
					$('#syncProgressText').text('Fetching companies and ledgers...');
				}
				else if (percent < 90) {
					$('#syncProgressText').text('Processing voucher data...');
				}
				else if (percent < 100) {
					$('#syncProgressText').text('Finalizing sync...');
				}
				else {
					$('#syncProgressText').text('Sync completed successfully!');
				}
			}
		</script>
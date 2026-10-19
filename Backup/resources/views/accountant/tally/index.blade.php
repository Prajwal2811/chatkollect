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

		<div class="content-body default-height tally-dashboard">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<div class="dashboard-content">

							<div class="tds-page-head mb-4">
								<div>
									<h2 class="tds-page-title">Assigned Companies List</h2>
									<p class="tds-page-sub">View your assigned companies at a glance</p>
								</div>
								<div class="tds-actions">
									

									<!-- <button type="button" id="editTallyConnectionBtn" class="tds-btn tds-btn-ghost">
										<i class="fas fa-plug"></i>
										<span>Connection</span>
									</button>

									<button type="button" id="syncTallyBtn" class="tds-btn tds-btn-primary">
										<i class="fas fa-sync-alt"></i>
										<span>Sync From Tally</span>
									</button> -->
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

							@forelse($companies as $company)
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
										<a href="{{ route('accountant.tally.company.ledgers', urlencode($company->company_name)) }}"
											class="tds-btn tds-btn-light">
											<i class="fas fa-book"></i>
											<span>Ledgers</span>
										</a>

										<!-- <a href="{{ route('owner.tally.voucher.mappings', urlencode($company->company_name)) }}"
											class="tds-btn tds-btn-amber">
											<i class="fas fa-random"></i>
											<span>Voucher Mapping</span>
										</a> -->
									</div>
								</div>
							@empty
								<div class="tds-empty-state">
									<i class="fas fa-building"></i>
									<p>No assigned companies found. Please contact your owner to get a company assigned to your account.</p>
								</div>
							@endforelse
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

		
		
		@include('accountant.components.footer')
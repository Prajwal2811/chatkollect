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

		<style>
			#targetTable .diff-cell {
				background-color: #f8f5ff;
			}
			#targetTable .diff-badge {
				display: inline-block;
				padding: 4px 10px;
				border-radius: 12px;
				font-weight: 600;
				font-size: 0.85rem;
			}
			#targetTable .diff-badge.positive {
				background-color: #d1f7e0;
				color: #0f9d58;
			}
			#targetTable .diff-badge.negative {
				background-color: #fde2e2;
				color: #d93025;
			}
			#targetTable .diff-badge.neutral {
				background-color: #eee;
				color: #666;
			}
		</style>

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">

					<!-- ===================== PAGE HEADER ===================== -->
					<div class="col-12">
						<div class="d-flex align-items-center justify-content-between mb-3">
							<h3 class="mb-0">Overdue Target % Settings</h3>
						</div>
					</div>

					<!-- ===================== TARGET % SECTION ===================== -->
					<div class="col-12 mb-4">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title mb-0">Receivable &amp; Payable Target %</h4>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-striped align-middle mb-0" id="targetTable">
										<thead>
											<tr>
												<th>Overdue Bucket</th>
												<th>Receivable Target %</th>
												<th>Payable Target %</th>
												<th class="gap-col-header">Overdue target % (gap)</th>
											</tr>
										</thead>
										<tbody>
											<!-- rows injected by JS -->
										</tbody>
									</table>
								</div>
								<div class="d-flex justify-content-end mt-3">
									<button type="button" class="btn btn-light me-2" id="resetAllBtn">Reset</button>
									<button type="button" class="btn btn-primary" id="saveAllBtn">Save Settings</button>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<script>
			// Overdue buckets, matching the reference layout
			const buckets = ["Till last 1 month", "Till last 2 month", "Till last 3 month", "Till last 4 month", "Till last 5 month", "Till last 6 month"];

			function buildRows() {
				const tbody = document.querySelector("#targetTable tbody");
				tbody.innerHTML = buckets.map((label, i) => `
					<tr>
						<td>${label}</td>
						<td>
							<div class="input-group input-group-sm">
								<input type="number" step="1" min="0" max="100"
									class="form-control rcv-target-input" id="rcv-target-${i}"
									placeholder="0" value="">
								<span class="input-group-text">%</span>
							</div>
							<small class="text-danger row-error" id="rcv-error-${i}"></small>
						</td>
						<td>
							<div class="input-group input-group-sm">
								<input type="number" step="1" min="0" max="100"
									class="form-control pay-target-input" id="pay-target-${i}"
									placeholder="0" value="">
								<span class="input-group-text">%</span>
							</div>
							<small class="text-danger row-error" id="pay-error-${i}"></small>
						</td>
						<td class="diff-cell fw-semibold" id="diff-${i}">-</td>
					</tr>
				`).join("");
			}

			// Returns null for a blank input instead of coercing it to 0,
			// so empty fields don't get flagged by the order validation.
			function getValues(prefix) {
				return buckets.map((_, i) => {
					const raw = document.getElementById(`${prefix}-target-${i}`).value;
					return raw === "" ? null : parseFloat(raw);
				});
			}

			// Highlights inputs that break decreasing order (each value must not be greater than the previous bucket's value).
			// Shows the warning right under the offending row's input only (single place, no duplicate summary).
			// Blank inputs are skipped and never flagged.
			function validateDecreasingOrder(values, prefix) {
				let hasError = false;
				values.forEach((val, i) => {
					const input    = document.getElementById(`${prefix}-target-${i}`);
					const rowErrEl = document.getElementById(`${prefix}-error-${i}`);
					const prev     = i > 0 ? values[i - 1] : null;
					const broken   = val !== null && prev !== null && val > prev;

					input.classList.toggle("is-invalid", broken);

					if (broken) {
						rowErrEl.innerText = `⚠ "${buckets[i]}" (${val}%) must not be greater than "${buckets[i - 1]}" (${prev}%)`;
						hasError = true;
					} else {
						rowErrEl.innerText = "";
					}
				});
				return hasError;
			}

			function renderDifference() {
				const receivable = getValues("rcv");
				const payable    = getValues("pay");

				buckets.forEach((_, i) => {
					const cell = document.getElementById(`diff-${i}`);

					if (receivable[i] === null || payable[i] === null) {
						cell.innerHTML = `<span class="diff-badge neutral">-</span>`;
						return;
					}

					const diff = (receivable[i] - payable[i]).toFixed(2);
					const cls = diff > 0 ? "positive" : diff < 0 ? "negative" : "neutral";
					cell.innerHTML = `<span class="diff-badge ${cls}">${diff}%</span>`;
				});

				validateDecreasingOrder(receivable, "rcv");
				validateDecreasingOrder(payable, "pay");
			}

			function bindLiveUpdates() {
				document.querySelectorAll(".rcv-target-input, .pay-target-input")
					.forEach(el => el.addEventListener("input", renderDifference));
			}

			function init() {
				buildRows();
				bindLiveUpdates();
				renderDifference();
			}

			document.getElementById("resetAllBtn").addEventListener("click", init);

			// Save (UI only, wire this up to your backend endpoint)
			document.getElementById("saveAllBtn").addEventListener("click", function () {
				const receivable = getValues("rcv");
				const payable    = getValues("pay");

				const rcvBroken = validateDecreasingOrder(receivable, "rcv");
				const payBroken = validateDecreasingOrder(payable, "pay");
				if (rcvBroken || payBroken) {
					alert("Target % values must be in decreasing order for both Receivable and Payable. Please fix the highlighted fields.");
					return;
				}

				const payload = {
					receivable: buckets.map((label, i) => ({ bucket: label, target: receivable[i] })),
					payable: buckets.map((label, i) => ({ bucket: label, target: payable[i] })),
				};
				console.log("Overdue target settings payload:", payload);
				// TODO: POST `payload` to your save endpoint, e.g. fetch('/owner/overdue-target-settings', {...})
				alert("Settings ready to save (see console for payload). Wire this button to your backend route.");
			});

			init();
		</script>

		@include('owner.components.footer')
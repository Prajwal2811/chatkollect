@include('owner.tally.components.header')
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
		@include('owner.tally.components.navbar')
		@include('owner.tally.components.sidebar')

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
			#saveAllBtn:disabled {
				opacity: .5;
				cursor: not-allowed;
			}
		</style>

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">

					<!-- ===================== PAGE HEADER ===================== -->
					<div class="col-12">
						<div class="d-flex align-items-center justify-content-between mb-3">
							<h3 class="mb-0">
								Overdue Target % Settings
							</h3>
						</div>
					</div>

					<!-- ===================== ALERT MESSAGE ===================== -->
					<div class="col-12">
						<div id="alertBox"></div>
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
			const buckets = ["Till last 1 month", "Till last 2 month", "Till last 3 month", "Till last 4 month", "Till last 5 month", "Till last 6 month"];

			// DB se aaya saved data: { "1": {receivable: 50, payable: 40, diff: 10}, ... }
			let savedSettings = @json($settings ?? []);

			const saveUrl   = "{{ route('owner.overdue-target.save') }}";
			const csrf      = "{{ csrf_token() }}";
			const companyId = {{ $tallyCompany->id }};

			// value ko input ke liye safe string me convert karta hai
			function val(v) {
				return (v === null || v === undefined) ? "" : v;
			}

			// alert() ki jagah page par Bootstrap alert dikhata hai
			function showAlert(message, type = "success") {
				const box = document.getElementById("alertBox");

				const alertEl = document.createElement("div");
				alertEl.className = `alert alert-${type} alert-dismissible fade show`;
				alertEl.setAttribute("role", "alert");

				const lines = String(message).split("\n");
				if (lines.length > 1) {
					const ul = document.createElement("ul");
					ul.className = "mb-0";
					lines.forEach(m => {
						const li = document.createElement("li");
						li.textContent = m;
						ul.appendChild(li);
					});
					alertEl.appendChild(ul);
				} else {
					alertEl.appendChild(document.createTextNode(message));
				}

				const closeBtn = document.createElement("button");
				closeBtn.type = "button";
				closeBtn.className = "btn-close";
				closeBtn.setAttribute("data-bs-dismiss", "alert");
				alertEl.appendChild(closeBtn);

				box.innerHTML = "";
				box.appendChild(alertEl);
				box.scrollIntoView({ behavior: "smooth", block: "start" });

				// Sirf success 3 second baad auto close
				if (type === "success") {
					setTimeout(() => {
						if (alertEl.isConnected) {
							bootstrap.Alert.getOrCreateInstance(alertEl).close();
						}
					}, 3000);
				}
			}

			function buildRows(data = {}) {
				const tbody = document.querySelector("#targetTable tbody");
				tbody.innerHTML = buckets.map((label, i) => {
					const row = data[i + 1] || {};
					return `
						<tr>
							<td>${label}</td>
							<td>
								<div class="input-group input-group-sm">
									<input type="number" step="1" min="0" max="100"
										class="form-control rcv-target-input" id="rcv-target-${i}"
										placeholder="0" value="${val(row.receivable)}">
									<span class="input-group-text">%</span>
								</div>
								<small class="text-danger row-error" id="rcv-error-${i}"></small>
							</td>
							<td>
								<div class="input-group input-group-sm">
									<input type="number" step="1" min="0" max="100"
										class="form-control pay-target-input" id="pay-target-${i}"
										placeholder="0" value="${val(row.payable)}">
									<span class="input-group-text">%</span>
								</div>
								<small class="text-danger row-error" id="pay-error-${i}"></small>
							</td>
							<td class="diff-cell fw-semibold" id="diff-${i}">-</td>
						</tr>
					`;
				}).join("");
			}

			function getValues(prefix) {
				return buckets.map((_, i) => {
					const raw = document.getElementById(`${prefix}-target-${i}`).value;
					return raw === "" ? null : parseFloat(raw);
				});
			}

			function validateDecreasingOrder(values, prefix) {
				let hasError = false;
				let lastIdx  = -1; // index of last valid filled value

				values.forEach((v, i) => {
					const input = document.getElementById(`${prefix}-target-${i}`);
					const errEl = document.getElementById(`${prefix}-error-${i}`);
					let msg = "";

					if (v !== null) {
						if (Number.isNaN(v) || v < 0 || v > 100) {
							msg = "⚠ Value must be between 0 and 100.";
						} else {
							if (lastIdx !== -1 && v > values[lastIdx]) {
								msg = `⚠ "${buckets[i]}" (${v}%) must not be greater than "${buckets[lastIdx]}" (${values[lastIdx]}%)`;
							}
							lastIdx = i;
						}
					}

					input.classList.toggle("is-invalid", msg !== "");
					errEl.innerText = msg;
					if (msg) hasError = true;
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

					const diff = (receivable[i] - payable[i]);
					const cls  = diff > 0 ? "positive" : diff < 0 ? "negative" : "neutral";
					cell.innerHTML = `<span class="diff-badge ${cls}">${diff.toFixed(2)}%</span>`;
				});

				const rcvBroken = validateDecreasingOrder(receivable, "rcv");
				const payBroken = validateDecreasingOrder(payable, "pay");

				// Validation pass hone par hi Save button active
				document.getElementById("saveAllBtn").disabled = (rcvBroken || payBroken);
			}

			function bindLiveUpdates() {
				document.querySelectorAll(".rcv-target-input, .pay-target-input")
					.forEach(el => el.addEventListener("input", renderDifference));
			}

			// Saved data ke saath table load karta hai (page load + Reset dono me use hota hai)
			function init() {
				buildRows(savedSettings);
				bindLiveUpdates();
				renderDifference();
			}

			// Reset = last saved values wapas laata hai
			document.getElementById("resetAllBtn").addEventListener("click", init);

			document.getElementById("saveAllBtn").addEventListener("click", async function () {
				const btn        = this;
				const receivable = getValues("rcv");
				const payable    = getValues("pay");

				// Safety check (button disabled hai toh yahan tak nahi aayega)
				if (validateDecreasingOrder(receivable, "rcv") || validateDecreasingOrder(payable, "pay")) {
					showAlert("Target % values must be in decreasing order for both Receivable and Payable. Please fix the highlighted fields.", "danger");
					return;
				}

				btn.disabled = true; // double-click se bachne ke liye
				const oldText = btn.innerText;
				btn.innerText = "Saving...";

				try {
					const res = await fetch(saveUrl, {
						method: "POST",
						headers: {
							"Content-Type": "application/json",
							"Accept": "application/json",
							"X-CSRF-TOKEN": csrf
						},
						// diff client se nahi bhejte, server khud calculate karta hai
						body: JSON.stringify({ company_id: companyId, receivable, payable })
					});

					const data = await res.json();

					if (!res.ok) {
						// Laravel validation errors (422) ya custom message
						let msg = data.message || "Something went wrong.";
						if (data.errors) {
							msg = Object.values(data.errors).flat().join("\n");
						}
						showAlert(msg, "danger");
						return;
					}

					// Local saved copy update karo taaki Reset naye saved data par jaye
					savedSettings = {};
					buckets.forEach((_, i) => {
						const rcv = receivable[i];
						const pay = payable[i];
						savedSettings[i + 1] = {
							receivable: rcv,
							payable: pay,
							diff: (rcv !== null && pay !== null) ? +(rcv - pay).toFixed(2) : null
						};
					});

					showAlert(data.message || "Settings saved successfully.", "success");
				} catch (e) {
					console.error(e);
					showAlert("Network error. Please try again.", "danger");
				} finally {
					btn.innerText = oldText;
					renderDifference(); // button ki state validation ke hisaab se set hogi
				}
			});

			init();
		</script>
		@include('owner.tally.components.footer')
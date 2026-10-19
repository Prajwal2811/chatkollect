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

		<!-- CKEditor 5 (Classic Build) -->
		<script src="https://cdn.ckeditor.com/ckeditor5/41.3.0/classic/ckeditor.js"></script>

		<style>
			.ai-box {
				border: 1px dashed #b9aee0;
				background: #f8f6ff;
				border-radius: 10px;
				padding: 16px;
			}
			.ai-box-header {
				display: flex;
				align-items: center;
				gap: 8px;
				font-weight: 600;
				color: #4E3F6B;
				margin-bottom: 14px;
			}
			.ai-badge {
				background: #4E3F6B;
				color: #fff;
				font-size: 11px;
				letter-spacing: .5px;
				padding: 2px 8px;
				border-radius: 20px;
			}
			.ai-actions {
				display: flex;
				align-items: center;
				gap: 12px;
				flex-wrap: wrap;
			}
			#aiStatus { color: #6c757d; }

			.ai-param-title {
				font-size: 12px;
				color: #6c757d;
				margin: 10px 0 6px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 10px;
			}
			.ai-param-list {
				display: flex;
				flex-wrap: wrap;
				gap: 6px;
				align-items: center;
			}
			.ai-param-list span.param-chip {
				background: #fff;
				border: 1px solid #d8d2ec;
				color: #4E3F6B;
				font-size: 12px;
				padding: 3px 9px;
				border-radius: 20px;
				cursor: pointer;
				user-select: none;
				transition: all .15s ease;
				display: inline-flex;
				align-items: center;
				gap: 6px;
			}
			.ai-param-list span.param-chip:hover {
				background: #4E3F6B;
				color: #fff;
			}
			.param-chip .remove-param {
				font-size: 13px;
				line-height: 1;
				color: inherit;
				opacity: .6;
			}
			.param-chip .remove-param:hover {
				opacity: 1;
			}
			.add-param-btn {
				background: #4E3F6B;
				color: #fff;
				font-size: 12px;
				padding: 3px 10px;
				border-radius: 20px;
				border: none;
				cursor: pointer;
			}
			.add-param-btn:hover { background: #3c3055; }

			.add-param-form {
				display: none;
				gap: 8px;
				align-items: center;
				margin-top: 8px;
				flex-wrap: wrap;
			}
			.add-param-form.show { display: flex; }
			.add-param-form input {
				font-size: 12px;
				padding: 4px 8px;
				border: 1px solid #d8d2ec;
				border-radius: 6px;
				min-width: 200px;
			}
			.add-param-form button {
				font-size: 12px;
				padding: 4px 10px;
				border-radius: 6px;
				border: none;
			}
			.add-param-form .btn-save-param { background: #4E3F6B; color: #fff; }
			.add-param-form .btn-cancel-param { background: #eee; color: #444; }

			.badge-soft {
				background: #eee9fb;
				color: #4E3F6B;
				font-size: 11px;
				padding: 3px 8px;
				border-radius: 20px;
				display: inline-block;
				margin-right: 4px;
			}
		</style>

		<div class="content-body default-height">
			<div class="container-fluid">
				<div class="row">

					<!-- ===================== ADD / EDIT TEMPLATE FORM ===================== -->
					<div class="col-12">
						<div class="card">

							<div class="card-header">
								<h4 class="card-title" id="formTitle">Create WhatsApp Template</h4>
							</div>

							<div class="card-body">

								<form id="templateForm">
									<input type="hidden" id="template_id">

									<div class="row">

										<!-- Follow-up Type -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Follow-up Type</label>
											<select id="followup_type" class="form-control">
												<option value="">-- Select Follow-up Type --</option>
												<option value="Follow Up-Balances">Follow Up-Balances</option>
												<option value="Follow Up-Balances/%Targets">Follow Up-Balances/%Targets</option>
												<option value="Follow Up-Balances/Targets(months)">Follow Up-Balances/Targets(months)</option>
												<option value="Follow Up-Balances/Days(agewise)">Follow Up-Balances/Days(agewise)</option>
												<option value="Follow Up-Balances/Days(10-20-30)">Follow Up-Balances/Days(10-20-30)</option>
												<option value="Follow Up-Due">Follow Up-Due</option>
												<option value="Follow Up-Not Due">Follow Up-Not Due</option>
												<option value="Follow Up Overlimits">Follow Up Overlimits</option>
											</select>
											<small class="text-danger error-followup_type"></small>
										</div>

										<!-- Template Name -->
										<div class="col-md-6 mb-3">
											<label class="form-label">Template Name</label>
											<input type="text" id="name" class="form-control" placeholder="e.g. Order Confirmation">
											<small class="text-danger error-name"></small>
										</div>

										<div class="col-md-6 mb-3">
											<label class="form-label">Tone</label>
											<select id="ai_tone" class="form-control">
												<option value="Polite" selected>Polite</option>
												<option value="Urgent">Urgent</option>
												<option value="Firm">Firm</option>
												<option value="Friendly">Friendly</option>
											</select>
										</div>

										<div class="col-md-6 mb-3">
											<label class="form-label">Language</label>
											<select id="ai_language" class="form-control">
												<option value="English" selected>English</option>
												<option value="Hindi">Hindi</option>
												<option value="Hinglish">Hinglish</option>
											</select>
										</div>

										<!-- Message Body (Rich Text Editor) -->
										<div class="col-md-12 mb-3">
											<label class="form-label">Message Body</label>
											<textarea id="message" rows="4" class="form-control" placeholder="Type your message. Use @{{1}}, @{{2}} for dynamic variables e.g. Hello @{{1}}, your outstanding is ₹@{{2}}."></textarea>
											<small class="text-danger error-message"></small>

											<!-- Parameters: click to insert into the message body -->
											<div class="ai-param-title">
												<span>Click a parameter to insert it into the message — values are mapped automatically from Tally</span>
												<button type="button" class="add-param-btn" id="addParamToggleBtn">+ Add Parameter</button>
											</div>

											<div class="ai-param-list" id="paramList">
												<!-- Fixed + custom parameter chips are rendered here by JS -->
											</div>

											<!-- Inline form to add a new custom parameter -->
											<div class="add-param-form" id="addParamForm">
												<input type="text" id="newParamLabel" placeholder="e.g. Contact Person Name">
												<button type="button" class="btn-save-param" id="saveParamBtn">Add</button>
												<button type="button" class="btn-cancel-param" id="cancelParamBtn">Cancel</button>
											</div>
										</div>

									</div>

									<div class="mt-3">
										<button type="submit" class="btn btn-primary" id="submitBtn">
											Save Template
										</button>
										<button type="button" class="btn btn-light" id="resetBtn">
											Reset
										</button>
									</div>

								</form>

							</div>

						</div>
					</div>

					<!-- ===================== TEMPLATE LIST ===================== -->
					<div class="col-12">
						<div class="card">

							<div class="card-header">
								<h4 class="card-title">WhatsApp Templates List</h4>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-striped" id="templateTable">
										<thead>
											<tr>
												<th>#</th>
												<th>Follow-up Type</th>
												<th>Template Name</th>
												<th>Tone / Language</th>
												<th style="width: 450px;">Message Body</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody id="templateTableBody">
											<!-- Rows are rendered by JS from the DB-seeded `templates` array below -->
										</tbody>
									</table>
								</div>
							</div>

						</div>
					</div>

				</div>
			</div>
		</div>

		<script>
			const form           = document.getElementById("templateForm");
			const formTitle      = document.getElementById("formTitle");
			const submitBtn      = document.getElementById("submitBtn");
			const resetBtn       = document.getElementById("resetBtn");
			const tableBody      = document.getElementById("templateTableBody");
			const templateIdEl   = document.getElementById("template_id");
			const followupTypeEl = document.getElementById("followup_type");

			// AI elements
			const aiToneEl      = document.getElementById("ai_tone");
			const aiLanguageEl  = document.getElementById("ai_language");
			const aiGenerateBtn = document.getElementById("aiGenerateBtn");
			const aiClearBtn    = document.getElementById("aiClearBtn");
			const aiStatus      = document.getElementById("aiStatus");

			// Parameter elements
			const paramListEl       = document.getElementById("paramList");
			const addParamToggleBtn = document.getElementById("addParamToggleBtn");
			const addParamForm      = document.getElementById("addParamForm");
			const newParamLabelEl   = document.getElementById("newParamLabel");
			const saveParamBtn      = document.getElementById("saveParamBtn");
			const cancelParamBtn    = document.getElementById("cancelParamBtn");

			// ------------------------------------------------------------
			// Real backend endpoints (Blade route helpers, $company comes from controller)
			// ------------------------------------------------------------
			const USE_AI_API  = false; // no ai-generate route on backend yet — mock used
			const AI_ENDPOINT = "/owner/tally/whatsapp-template/ai-generate";

			const USE_PARAM_API = true; // storeParameter route is ready
			const PARAM_ENDPOINT = "{{ route('owner.other.template.parameters.store', $company) }}";

			const TEMPLATE_STORE_ENDPOINT = "{{ route('owner.other.templates.store', $company) }}";

			// Fixed parameters that are always mapped from Tally
			let fixedParams = [
				{ num: 1, label: "Party Name" },
				{ num: 2, label: "Outstanding Balance" },
				{ num: 3, label: "Voucher/Invoice No." },
				{ num: 4, label: "Due Date" },
				{ num: 5, label: "Company Name" },
				{ num: 6, label: "GSTIN" },
				{ num: 7, label: "Payment Link" },
				{ num: 8, label: "Days Overdue" }
			];

			// Custom parameters added by the user at runtime (numbering continues after fixed ones)
			let customParams = [];

			function getAllParams() {
				return [...fixedParams, ...customParams];
			}

			function nextParamNumber() {
				const nums = getAllParams().map(p => p.num);
				return nums.length ? Math.max(...nums) + 1 : 1;
			}

			function renderParamList() {
				paramListEl.innerHTML = getAllParams().map(p => `
					<span class="param-chip" data-param="@{{${p.num}}}" data-custom="${p.custom ? '1' : '0'}">
						@{{${p.num}}} ${escapeHtml(p.label)}
						${p.custom ? `<span class="remove-param" data-num="${p.num}" title="Remove">&times;</span>` : ''}
					</span>
				`).join("");
			}

			// Click a chip (but not its remove "x") -> insert into message body
			paramListEl.addEventListener("click", function (e) {
				if (e.target.classList.contains("remove-param")) {
					const num = Number(e.target.dataset.num);
					customParams = customParams.filter(p => p.num !== num);
					renderParamList();
					return;
				}

				const chip = e.target.closest(".param-chip");
				if (!chip || !ckEditor) return;

				const param = chip.dataset.param;
				ckEditor.model.change(writer => {
					ckEditor.model.insertContent(
						writer.createText(param),
						ckEditor.model.document.selection
					);
				});
				ckEditor.editing.view.focus();
			});

			// Show / hide "add parameter" inline form
			addParamToggleBtn.addEventListener("click", () => {
				addParamForm.classList.toggle("show");
				if (addParamForm.classList.contains("show")) {
					newParamLabelEl.value = "";
					newParamLabelEl.focus();
				}
			});

			cancelParamBtn.addEventListener("click", () => {
				addParamForm.classList.remove("show");
			});

			saveParamBtn.addEventListener("click", async () => {
				const label = newParamLabelEl.value.trim();
				if (!label) {
					newParamLabelEl.focus();
					return;
				}

				const newParam = { num: nextParamNumber(), label, custom: true };

				if (USE_PARAM_API) {
					saveParamBtn.disabled = true;
					try {
						const token = document.querySelector('meta[name="csrf-token"]');
						const res = await fetch(PARAM_ENDPOINT, {
							method: "POST",
							headers: {
								"Content-Type": "application/json",
								"X-Requested-With": "XMLHttpRequest",
								...(token ? { "X-CSRF-TOKEN": token.content } : {})
							},
							body: JSON.stringify({ label })
						});

						if (!res.ok) {
							const errJson = await res.json().catch(() => ({}));
							if (errJson.errors && errJson.errors.label) {
								alert(errJson.errors.label[0]);
							} else {
								alert("Could not save the parameter to the server, added it locally for this session only.");
							}
							throw new Error("Request failed: " + res.status);
						}

						const json = await res.json();
						// Server returns { num, label } — server assigns the number
						if (json.num) newParam.num = json.num;
						if (json.label) newParam.label = json.label;
					} catch (err) {
						console.error(err);
					} finally {
						saveParamBtn.disabled = false;
					}
				}

				customParams.push(newParam);
				renderParamList();
				addParamForm.classList.remove("show");
			});

			// Allow pressing Enter inside the label field to add the parameter
			newParamLabelEl.addEventListener("keydown", (e) => {
				if (e.key === "Enter") {
					e.preventDefault();
					saveParamBtn.click();
				}
			});

			// ------------------------------------------------------------
			// Templates: seeded from DB via controller (no more dummy data)
			// ------------------------------------------------------------
			let templates = @json($templates->map(function ($t) {
				return [
					'id'            => $t->id,
					'followup_type' => $t->followup_type,
					'name'          => $t->name,
					'tone'          => $t->tone,
					'language'      => $t->language,
					'message'       => $t->message,
				];
			}));

			let nextId = (templates.length ? Math.max(...templates.map(t => t.id)) : 0) + 1;

			let ckEditor = null;

			// Initialize CKEditor on the message textarea
			ClassicEditor
				.create(document.querySelector('#message'), {
					toolbar: [
						'heading', '|',
						'bold', 'italic', 'underline', '|',
						'bulletedList', 'numberedList', '|',
						'link', 'blockQuote', '|',
						'undo', 'redo'
					]
				})
				.then(editor => {
					ckEditor = editor;
					renderParamList();
					renderTable(); // render DB-seeded rows once editor is ready
				})
				.catch(error => {
					console.error(error);
				});

			/* ============================================================
			 * AI MESSAGE GENERATION
			 * ============================================================ */
			aiClearBtn.addEventListener("click", () => {
				if (ckEditor) ckEditor.setData("");
				aiStatus.innerText = "";
			});

			aiGenerateBtn.addEventListener("click", async function () {
				const payload = {
					followup_type: followupTypeEl.value,
					tone:          aiToneEl.value,
					language:      aiLanguageEl.value
				};

				if (!payload.followup_type) {
					document.querySelector(".error-followup_type").innerText = "Please select a follow-up type first";
					return;
				}

				aiGenerateBtn.disabled = true;
				aiStatus.innerText = "Generating...";

				try {
					let message;

					if (USE_AI_API) {
						const token = document.querySelector('meta[name="csrf-token"]');
						const res = await fetch(AI_ENDPOINT, {
							method: "POST",
							headers: {
								"Content-Type": "application/json",
								"X-Requested-With": "XMLHttpRequest",
								...(token ? { "X-CSRF-TOKEN": token.content } : {})
							},
							body: JSON.stringify(payload)
						});
						if (!res.ok) throw new Error("Request failed: " + res.status);
						const json = await res.json();
						message = json.message;
					} else {
						// Local mock so the UI is fully testable without a backend
						await new Promise(r => setTimeout(r, 600));
						message = mockGenerate(payload);
					}

					if (ckEditor) ckEditor.setData(message);
					document.querySelector(".error-message").innerText = "";
					aiStatus.innerText = "Message generated. Edit it if needed.";
				} catch (err) {
					console.error(err);
					aiStatus.innerText = "Could not generate. Please try again.";
				} finally {
					aiGenerateBtn.disabled = false;
				}
			});

			// ---- Mock generator (replace with real AI response from backend) ----
			function mockGenerate({ followup_type, tone, language }) {
				const L = {
					English: {
						greet: {
							Polite:   "Dear @{{1}},",
							Urgent:   "Dear @{{1}},",
							Firm:     "Dear @{{1}},",
							Friendly: "Hi @{{1}},"
						},
						body: {
							"Follow Up-Balances":                 "As per our books, an outstanding balance of ₹@{{2}} is pending against your account with @{{5}}.",
							"Follow Up-Balances/%Targets":        "Against your agreed collection target, an outstanding balance of ₹@{{2}} is still pending with @{{5}}.",
							"Follow Up-Balances/Targets(months)": "The monthly target is not met and an outstanding balance of ₹@{{2}} is still pending with @{{5}}.",
							"Follow Up-Balances/Days(agewise)":   "An amount of ₹@{{2}} against invoice @{{3}} is overdue by @{{8}} days.",
							"Follow Up-Balances/Days(10-20-30)":  "An amount of ₹@{{2}} against invoice @{{3}} has crossed @{{8}} days from the due date @{{4}}.",
							"Follow Up-Due":                      "Your payment of ₹@{{2}} against invoice @{{3}} is due on @{{4}}.",
							"Follow Up-Not Due":                  "This is regarding invoice @{{3}} of ₹@{{2}}, which will become due on @{{4}}.",
							"Follow Up Overlimits":               "Your account has crossed the approved credit limit with an outstanding of ₹@{{2}}."
						},
						link: "You may pay using this link: @{{7}}.",
						close: {
							Polite:   "We request you to kindly arrange the payment at the earliest. Thank you.",
							Urgent:   "Kindly arrange the payment today itself to avoid further follow-up.",
							Firm:     "Please ensure the payment is released immediately, failing which further supplies may be put on hold.",
							Friendly: "Whenever you get a moment, please help us close this. Thanks a lot!"
						},
						sign: "Regards,<br>@{{5}} (GSTIN: @{{6}})"
					},
					Hindi: {
						greet: {
							Polite:   "आदरणीय @{{1}},",
							Urgent:   "आदरणीय @{{1}},",
							Firm:     "आदरणीय @{{1}},",
							Friendly: "नमस्ते @{{1}},"
						},
						body: {
							"Follow Up-Balances":                 "हमारे रिकॉर्ड के अनुसार @{{5}} के साथ आपके खाते में ₹@{{2}} की राशि बकाया है।",
							"Follow Up-Balances/%Targets":        "निर्धारित लक्ष्य के मुकाबले आपके खाते में ₹@{{2}} की राशि अभी भी बकाया है।",
							"Follow Up-Balances/Targets(months)": "इस माह का लक्ष्य पूरा नहीं हुआ है और ₹@{{2}} की राशि बकाया है।",
							"Follow Up-Balances/Days(agewise)":   "इनवॉइस @{{3}} के विरुद्ध ₹@{{2}} की राशि @{{8}} दिनों से बकाया है।",
							"Follow Up-Balances/Days(10-20-30)":  "इनवॉइस @{{3}} की ₹@{{2}} राशि नियत तिथि @{{4}} से @{{8}} दिन पार कर चुकी है।",
							"Follow Up-Due":                      "इनवॉइस @{{3}} के विरुद्ध ₹@{{2}} का भुगतान @{{4}} को देय है।",
							"Follow Up-Not Due":                  "इनवॉइस @{{3}} की राशि ₹@{{2}} @{{4}} को देय होगी।",
							"Follow Up Overlimits":               "आपका खाता स्वीकृत क्रेडिट सीमा पार कर चुका है, बकाया राशि ₹@{{2}} है।"
						},
						link: "भुगतान इस लिंक से किया जा सकता है: @{{7}}।",
						close: {
							Polite:   "कृपया शीघ्र भुगतान करने का कष्ट करें। धन्यवाद।",
							Urgent:   "कृपया आज ही भुगतान करें ताकि आगे फॉलो-अप की आवश्यकता न पड़े।",
							Firm:     "कृपया तुरंत भुगतान सुनिश्चित करें, अन्यथा आगे की सप्लाई रोकी जा सकती है।",
							Friendly: "समय मिलते ही कृपया इसे निपटा दें। बहुत धन्यवाद!"
						},
						sign: "सादर,<br>@{{5}} (GSTIN: @{{6}})"
					},
					Hinglish: {
						greet: {
							Polite:   "Dear @{{1}},",
							Urgent:   "Dear @{{1}},",
							Firm:     "Dear @{{1}},",
							Friendly: "Hi @{{1}},"
						},
						body: {
							"Follow Up-Balances":                 "Hamare records ke hisaab se @{{5}} ke saath aapke account me ₹@{{2}} outstanding pending hai.",
							"Follow Up-Balances/%Targets":        "Target ke against aapke account me ₹@{{2}} ka outstanding abhi bhi pending hai.",
							"Follow Up-Balances/Targets(months)": "Is month ka target pura nahi hua hai aur ₹@{{2}} outstanding pending hai.",
							"Follow Up-Balances/Days(agewise)":   "Invoice @{{3}} ke against ₹@{{2}} ka payment @{{8}} din se overdue hai.",
							"Follow Up-Balances/Days(10-20-30)":  "Invoice @{{3}} ka ₹@{{2}} amount due date @{{4}} se @{{8}} din cross kar chuka hai.",
							"Follow Up-Due":                      "Invoice @{{3}} ke against ₹@{{2}} ka payment @{{4}} ko due hai.",
							"Follow Up-Not Due":                  "Invoice @{{3}} ka ₹@{{2}} amount @{{4}} ko due hoga.",
							"Follow Up Overlimits":               "Aapka account credit limit cross kar chuka hai, outstanding ₹@{{2}} hai."
						},
						link: "Payment ke liye yeh link use karein: @{{7}}.",
						close: {
							Polite:   "Kripya jaldi se payment arrange kar dijiye. Dhanyawad.",
							Urgent:   "Kripya aaj hi payment karein taaki aage follow-up na karna pade.",
							Firm:     "Kripya turant payment release karein, warna aage ki supply hold ho sakti hai.",
							Friendly: "Jab time mile, please ise clear kar dijiye. Thanks a lot!"
						},
						sign: "Regards,<br>@{{5}} (GSTIN: @{{6}})"
					}
				};

				const pack  = L[language] || L.English;
				const greet = pack.greet[tone] || pack.greet.Polite;
				const body  = pack.body[followup_type] || pack.body["Follow Up-Balances"];
				const close = pack.close[tone] || pack.close.Polite;

				// Payment link is added for every type except "Not Due"
				const withLink = followup_type !== "Follow Up-Not Due";

				const urgentPrefix = tone === "Urgent"
					? (language === "Hindi" ? "<strong>अत्यावश्यक:</strong> " : "<strong>URGENT:</strong> ")
					: "";

				return [
					`<p>${greet}</p>`,
					`<p>${urgentPrefix}${body}${withLink ? " " + pack.link : ""}</p>`,
					`<p>${close}</p>`,
					`<p>${pack.sign}</p>`
				].join("");
			}

			/* ============================================================
			 * FORM / TABLE
			 * ============================================================ */

			function clearErrors() {
				document.querySelectorAll("small.text-danger").forEach(el => el.innerText = "");
			}

			function resetForm() {
				form.reset();
				templateIdEl.value = "";
				followupTypeEl.value = "";
				aiToneEl.value = "Polite";
				aiLanguageEl.value = "English";
				if (ckEditor) ckEditor.setData("");
				aiStatus.innerText = "";
				formTitle.innerText = "Create WhatsApp Template";
				submitBtn.innerText = "Save Template";
				clearErrors();
			}

			resetBtn.addEventListener("click", resetForm);

			function validateForm(data) {
				let isValid = true;
				clearErrors();

				if (!data.followup_type.trim()) {
					document.querySelector(".error-followup_type").innerText = "Please select a follow-up type";
					isValid = false;
				}
				if (!data.name.trim()) {
					document.querySelector(".error-name").innerText = "Template name is required";
					isValid = false;
				}
				if (!data.message.trim()) {
					document.querySelector(".error-message").innerText = "Message body is required";
					isValid = false;
				}

				return isValid;
			}

			function escapeHtml(str) {
				const div = document.createElement("div");
				div.innerText = str ?? "";
				return div.innerHTML;
			}

			function buildRow(template, index) {
				return `
					<tr id="row-${template.id}">
						<td>${index + 1}</td>
						<td class="col-followup">${escapeHtml(template.followup_type)}</td>
						<td class="col-name">${escapeHtml(template.name)}</td>
						<td class="col-meta">
							<span class="badge-soft">${escapeHtml(template.tone || "-")}</span>
							<span class="badge-soft">${escapeHtml(template.language || "-")}</span>
						</td>
						<td class="col-message">${template.message}</td>
						<td>
							<button type="button" class="btn btn-sm btn-info edit-btn" data-id="${template.id}">
								Edit
							</button>
							<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${template.id}">
								Delete
							</button>
						</td>
					</tr>
				`;
			}

			function renderTable() {
				if (!templates.length) {
					tableBody.innerHTML = `<tr id="noDataRow"><td colspan="6" class="text-center">No templates found</td></tr>`;
					return;
				}
				tableBody.innerHTML = templates.map((t, i) => buildRow(t, i)).join("");
			}

			// Submit (Create / Update)
			form.addEventListener("submit", async function (e) {
				e.preventDefault();

				const data = {
					followup_type: followupTypeEl.value,
					name:          document.getElementById("name").value.trim(),
					tone:          aiToneEl.value,
					language:      aiLanguageEl.value,
					message:       ckEditor ? ckEditor.getData().trim() : ""
				};

				if (!validateForm(data)) return;

				const id = templateIdEl.value;

				if (id) {
					// Update existing — no update route on backend yet, kept local
					const idx = templates.findIndex(t => t.id == id);
					if (idx !== -1) {
						templates[idx] = { id: Number(id), ...data };
					}
					renderTable();
					resetForm();
					return;
				}

				// Create new — hits real backend (storeTemplate)
				submitBtn.disabled = true;
				try {
					const token = document.querySelector('meta[name="csrf-token"]');
					const res = await fetch(TEMPLATE_STORE_ENDPOINT, {
						method: "POST",
						headers: {
							"Content-Type": "application/json",
							"X-Requested-With": "XMLHttpRequest",
							...(token ? { "X-CSRF-TOKEN": token.content } : {})
						},
						body: JSON.stringify(data)
					});

					if (!res.ok) {
						const errJson = await res.json().catch(() => ({}));
						if (errJson.errors) {
							Object.entries(errJson.errors).forEach(([field, msgs]) => {
								const el = document.querySelector(`.error-${field}`);
								if (el) el.innerText = msgs[0];
							});
						}
						throw new Error("Request failed: " + res.status);
					}

					const json = await res.json();
					const saved = json.template ?? { id: nextId++, ...data };
					templates.push(saved);
					renderTable();
					resetForm();
				} catch (err) {
					console.error(err);
					alert("Could not save the template. Please try again.");
				} finally {
					submitBtn.disabled = false;
				}
			});

			// Edit / Delete button clicks
			tableBody.addEventListener("click", function (e) {
				const id = e.target.dataset.id;
				if (!id) return;

				if (e.target.classList.contains("edit-btn")) {
					const template = templates.find(t => t.id == id);
					if (!template) return;

					templateIdEl.value = template.id;
					followupTypeEl.value = template.followup_type;
					document.getElementById("name").value = template.name;
					aiToneEl.value = template.tone || "Polite";
					aiLanguageEl.value = template.language || "English";
					if (ckEditor) ckEditor.setData(template.message);

					formTitle.innerText = "Edit WhatsApp Template";
					submitBtn.innerText = "Update Template";

					window.scrollTo({ top: 0, behavior: "smooth" });
				}

				if (e.target.classList.contains("delete-btn")) {
					if (!confirm("Are you sure you want to delete this template?")) return;
					templates = templates.filter(t => t.id != id);
					renderTable();
					// Note: no delete route on backend yet — removal is local only for now.
				}
			});
		</script>

@include('owner.tally.components.footer')
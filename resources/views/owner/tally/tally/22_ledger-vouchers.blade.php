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
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h4 class="card-title mb-0">Ledger Vouchers — {{ $ledger }}</h4>
                                    <small class="text-muted" id="fy-label">{{ $fyLabel ?? '' }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="text-muted small fw-semibold">Financial Year</span>
                                        <div class="btn-group shadow-sm" role="group" id="fy-btn-group">
                                            @foreach($availableFYs as $fy)
                                                <button type="button"
                                                        class="btn btn-sm fy-btn {{ (string)$selectedYear === (string)$fy['value'] ? 'btn-primary' : 'btn-outline-primary' }}"
                                                        data-url="{{ request()->fullUrlWithQuery(['fy' => $fy['value']]) }}">
                                                    {{ $fy['label'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <span class="badge bg-primary">{{ $under }}</span>
                                </div>
                            </div>

                            <div class="card-body position-relative">
                                <div id="voucher-loading" class="d-none position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                                    style="background:rgba(255,255,255,.6); z-index:10;">
                                    <div class="spinner-border text-primary"></div>
                                </div>

                                <div id="voucher-content">
                                    @include('owner.tally.ledger-voucher-content')
                                </div>
                            </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const btnGroup = document.getElementById('fy-btn-group');
                            const content  = document.getElementById('voucher-content');
                            const loading  = document.getElementById('voucher-loading');
                            const fyLabel  = document.getElementById('fy-label');

                            function initVoucherTables() {
                                try {
                                    ['#example11', '#example12', '#example13', '#example14'].forEach(function (id) {
                                        const $table = $(id);
                                        if ($table.length === 0) return;

                                        if ($.fn.DataTable.isDataTable(id)) {
                                            $table.DataTable().destroy();
                                        }

                                        $table.DataTable({
                                            // config
                                        });
                                    });
                                } catch (err) {
                                    console.error('DataTable init failed:', err);
                                }
                            }

                            $(document).on('shown.bs.tab', 'button[data-bs-toggle="pill"]', function (e) {
                                try {
                                    const target = $(e.target).data('bs-target');
                                    $(target).find('table.display').each(function () {
                                        if ($.fn.DataTable.isDataTable(this)) {
                                            $(this).DataTable().columns.adjust();
                                        }
                                    });
                                } catch (err) {
                                    console.error('Tab adjust failed:', err);
                                }
                            });

                            // FY button listener PEHLE attach karo — yeh kabhi na toote
                            btnGroup.addEventListener('click', function (e) {
                                const btn = e.target.closest('.fy-btn');
                                if (!btn) return;

                                btnGroup.querySelectorAll('.fy-btn').forEach(b => {
                                    b.classList.remove('btn-primary');
                                    b.classList.add('btn-outline-primary');
                                });
                                btn.classList.remove('btn-outline-primary');
                                btn.classList.add('btn-primary');

                                loading.classList.remove('d-none');

                                fetch(btn.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(res => {
                                        if (!res.ok) throw new Error('Request failed: ' + res.status);
                                        return res.json();
                                    })
                                    .then(data => {
                                        content.innerHTML = data.html;
                                        if (data.fyLabel) fyLabel.textContent = data.fyLabel;
                                        history.pushState({}, '', btn.dataset.url);
                                        initVoucherTables();
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        alert('Could not load data for that financial year.');
                                    })
                                    .finally(() => loading.classList.add('d-none'));
                            });

                            // Ab initVoucherTables call karo — agar yeh fail bhi ho, upar wala listener already attach ho chuka
                            initVoucherTables();
                        });
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .card {
                border-radius: 12px;
                transition: all .3s ease;
            }
            .card:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, .12);
            }

            .card h4 {
                font-size: 22px;
            }

            .card h6 {
                font-size: 14px;
                text-transform: uppercase;
            }
        </style>
        
    </div>
@include('owner.components.footer')
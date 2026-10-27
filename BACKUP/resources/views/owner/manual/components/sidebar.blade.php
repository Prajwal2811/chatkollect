<style>
    /* Level 1: Main Menu Items */
    #menu > li > a {
        font-weight: 600;
    }

    /* Level 2: Sub Menu (first nested ul) */
    #menu > li > ul {
        border-left: 3px solid #4a90e2;
        margin-left: 10px;
    }
    #menu > li > ul > li > a {
        padding-left: 25px;
        background: #fafafa;
        color: #333;
    }

    /* Level 3: Sub-Sub Menu (nested inside sub menu) */
    #menu > li > ul > li > ul {
        border-left: 3px solid #f5a623;
        margin-left: 15px;
    }
    #menu > li > ul > li > ul > li > a {
        padding-left: 40px;
        background: #fff;
        color: #666;
        font-size: 0.9em;
    }
</style>

<div class="dlabnav">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            <li>
                <a href="{{ route('owner.manual.dashboard') }}" aria-expanded="false">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" aria-expanded="false">
                    <i class="fas fa-calculator"></i>
                    <span class="nav-text">Accountants</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{  route('owner.accountants.index') }}"><i class="fas fa-list"></i> All Accountants</a></li>
                    <li><a href="{{ route('owner.accountants.create') }}"><i class="fas fa-user-plus"></i> Add Accountant </a></li>
                </ul>
            </li>
            <li>
                <a href="javascript:void(0);" aria-expanded="false">
                    <i class="fas fa-people-carry"></i>
                    <span class="nav-text">Collectors</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{  route('owner.collectors.index') }}"><i class="fas fa-list"></i> All Collectors</a></li>
                    <li><a href="{{ route('owner.collectors.create') }}"><i class="fas fa-user-plus"></i> Add Collector </a></li>
                </ul>
            </li>
            @if(!empty($company))
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="nav-text">Receivable</span>
                    </a>
                    <ul aria-expanded="false">
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false"><i class="fas fa-map-marker-alt"></i> Track</a>
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);"><i class="fas fa-user-tie"></i> Collector</a></li>
                                <li><a href="javascript:void(0);"><i class="fas fa-user"></i> Debtor</a></li>
                                <li><a href="javascript:void(0);"><i class="fas fa-chart-pie"></i> Charts</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false"><i class="fas fa-wallet"></i> Collect</a>
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);"><i class="fas fa-percentage"></i> Invoices Received Interest Pending</a></li>
                                <li><a href="javascript:void(0);"><i class="fas fa-file-invoice"></i> Invoices Pending</a></li>
                            </ul>
                        </li>
                        @if(!empty($company))
                            <li>
                                <a href="javascript:void(0);" aria-expanded="false"><i class="fas fa-tasks"></i> Follow Up Allocate</a>
                                <ul aria-expanded="false">
                                    <li>
                                        <a href="{{ route('owner.tally.followup.hub', ['company' => urlencode($company)]) }}">
                                            <i class="fas fa-th-large"></i> Follow Up Hub
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('owner.tally.followup.hub', ['company' => urlencode($company)]) }}?ledger=&under=&type={{ urlencode('Follow Up-Quick /Smart - Follow -up') }}">
                                            <i class="fas fa-bolt"></i> Quick /Smart
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if(!empty($company))
                            <li>
                                <a href="javascript:void(0);" aria-expanded="false"><i class="fas fa-clipboard-list"></i> Follow Up Action</a>
                                <ul aria-expanded="false">
                                    <li>
                                        <a href="{{ route('owner.tally.company.followup-action.telecaller', ['company' => urlencode($company)]) }}">
                                            <i class="fas fa-headset"></i> Telecaller
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('owner.tally.company.followup-action.call', ['company' => urlencode($company)]) }}">
                                            <i class="fas fa-phone-alt"></i> Call
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('owner.tally.company.followup-action.whatsapp-message', ['company' => urlencode($company)]) }}">
                                            <i class="fab fa-whatsapp"></i> WhatsApp / Message
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('owner.tally.company.followup-action.physical-visit', ['company' => urlencode($company)]) }}">
                                            <i class="fas fa-walking"></i> Physical Visit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('owner.tally.company.followup-action.escalation', ['company' => urlencode($company)]) }}">
                                            <i class="fas fa-exclamation-triangle"></i> Escalation
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if(!empty($company))
                            <li>
                                <a href="{{ route('owner.tally.company.followup_history', ['company' => urlencode($company)]) }}">
                                    <i class="fas fa-history"></i> Follow Up History
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if(!empty($company))
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="nav-text">Payable</span>
                    </a>
                    <ul aria-expanded="false">
                        <li>
                            <a href="javascript:void(0);" aria-expanded="false"><i class="fas fa-map-marker-alt"></i> Track</a>
                            <ul aria-expanded="false">
                                <li><a href="javascript:void(0);"><i class="fas fa-coins"></i> Total</a></li>
                                <li><a href="javascript:void(0);"><i class="fas fa-user-shield"></i> Creditor</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
            @endif

            @if(!empty($company))
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-balance-scale"></i>
                        <span class="nav-text">Net</span>
                    </a>
                </li>
            @endif


            @if(!empty($company))
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-cogs"></i>
                        <span class="nav-text">Others</span>
                    </a>
                    <ul aria-expanded="false">
                        <li>
                            <a href="{{  route('owner.other.templates' , ['company' => urlencode($company)]) }}"><i class="fas fa-file-alt"></i> Templates</a>
                        </li>
                        <li>
                            <a href="{{ route('owner.other.responses', ['company' => urlencode($company)]) }}"><i class="fas fa-comments"></i> Response/Solution</a>
                        </li>
                        <li>
                            <a href="{{ route('owner.other.overdue-target', ['company' => urlencode($company)]) }}"><i class="fas fa-bullseye"></i> Overdue target % gap</a>
                        </li>
                        <li>
                            <a href="{{ route('owner.other.default-settings', ['company' => urlencode($company)]) }}"><i class="fas fa-sliders-h"></i> Default Settings</a>
                        </li>
                        <li>
                            <a href="{{ route('owner.other.master-settings', ['company' => urlencode($company)]) }}"><i class="fas fa-cog"></i> Master Settings</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if(!empty($company))
                <li>
                    <a href="javascript:void(0);" aria-expanded="false">
                        <i class="fas fa-chart-line"></i>
                        <span class="nav-text">Reports</span>
                    </a>
                    <ul aria-expanded="false">
                        <li>
                            <a href="{{  route('owner.reports.charts' , ['company' => urlencode($company)]) }}"><i class="fas fa-chart-bar"></i> Charts</a>
                        </li>
                        <li>
                            <a href="{{ route('owner.reports.sales-analysis', ['company' => urlencode($company)]) }}"><i class="fas fa-chart-area"></i> Sales Analysis</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if(!empty($company))
                <li>
                    <a href="{{ route('owner.set-debtor-emi' , ['company' => urlencode($company)]) }}" aria-expanded="false">
                        <i class="fas fa-calendar-check"></i>
                        <span class="nav-text">Set Debtor EMI</span>
                    </a>
                </li>
            @endif

            <li>
                <a href="{{ route('owner.signOut') }}" aria-expanded="false">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Sign Out</span>
                </a>
            </li>
        </ul>
    </div>
</div>
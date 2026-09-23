@php
    $collector = Auth::guard('collector')->user();
@endphp
@if ($collector->business_type === 'tally') 
    <div class="dlabnav">
        <div class="dlabnav-scroll">
            <ul class="metismenu" id="menu">                
                <li>
                    <a href="{{ route('collector.tally.dashboard') }}" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                


                <li>
                    <a href="{{ route('collector.signOut') }}" aria-expanded="false">
                        <i class="fas fa-balance-scale"></i>
                        <span class="nav-text">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

@elseif($collector->business_type === 'manual')
    <div class="dlabnav">
        <div class="dlabnav-scroll">
            <ul class="metismenu" id="menu">
                <li>
                    <a href="{{ route('collector.tally.dashboard') }}" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Dashboard manual</span>
                    </a>
                </li>



                <li>
                    <a href="{{ route('collector.signOut') }}" aria-expanded="false">
                        <i class="fas fa-balance-scale"></i>
                        <span class="nav-text">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endif
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\TallyService;
use App\Models\LedgerCollector;
use App\Models\VoucherMapping;


class CollectorController extends Controller
{
    public function subscription()
    {
        return view('collector.subscription');
    }


    public function subscribe(Request $request)
    {
        $collector = auth('collector')->user();

        $collector->update([
            'is_subscribed' => "true"
        ]);

        return redirect()->route('collector.tally.dashboard')->with('success', 'Subscription activated successfully.');
    }


    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'account_type' => 'required|in:tally,manual',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => 'active',
        ];

        if (Auth::guard('collector')->attempt($credentials, $request->boolean('remember'))) {
        
            $collector = Auth::guard('collector')->user();

            // Match model's actual column: business_type
            if ($collector->business_type !== $request->account_type) {
                Auth::guard('collector')->logout();

                Log::warning('Collector login type mismatch', [
                    'email'         => $collector->email,
                    'selected_type' => $request->account_type,
                    'actual_type'   => $collector->business_type,
                    'time'          => now(),
                ]);

                session()->flash('error', 'This account is not registered under the selected type.');

                return back()->withInput($request->only('email', 'account_type'));
            }

            $request->session()->regenerate();

            Log::info('Collector login successful', [
                'email' => $collector->email,
                'name'  => $collector->name,
                'type'  => $collector->business_type,
                'time'  => now(),
            ]);

            // Redirect based on account type
            if ($collector->business_type === 'manual') {
                return redirect()->route('collector.manual.dashboard');
            }

            // Redirect to Tally dashboard if business type is tally
            if ($collector->business_type === 'tally') {
                return redirect()->route('collector.tally.dashboard');
            }
        }

        Log::warning('Accountant login failed', [
            'email' => $request->email,
            'time'  => now(),
        ]);

        session()->flash('error', 'Either Email/Password is incorrect');

        return back()->withInput($request->only('email', 'account_type'));
    }


    public function signOut()
    {
        // Capture collector info before logout
        $collector = Auth::guard('collector')->user();

        if ($collector) {
            Log::info('Owner logged out', [
                'email' => $collector->email,
                'name' => $collector->collector_name,
                'time' => now()
            ]);
        }

        Auth::guard('collector')->logout(); // Logs out the collector user
        session()->flash('success', 'You have been logged out successfully.');

        return redirect()->route('collector.login'); // Redirect to named route
    }


    protected TallyService $tally;

    public function __construct(TallyService $tally)
    {
        $this->tally = $tally;
    }

    public function dashboard()
    {
        try {
            $xml = $this->tally->getCompanies();
            $xmlObj = simplexml_load_string($xml);

            $tallyConnected = false;
            $companies = [];

            if ($xmlObj) {
                $tallyConnected = true;
                $nodes = $xmlObj->xpath("//*[local-name()='COMPANY']");
                if ($nodes) {
                    foreach ($nodes as $company) {
                        $name = trim((string)($company['NAME'] ?? $company->NAME));
                        if ($name != '') {
                            $companies[] = [
                                'name' => $name,
                            ];
                        }
                    }
                }
            }

            return view('collector.tally.index', compact(
                'companies',
                'xml',
                'tallyConnected'
            ));

        } catch (\Exception $e) {

            return view('collector.tally.index', [
                'companies' => [],
                'tallyConnected' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function syncAll()
    {
        try {
            // Tally se data fetch
            $companies = $this->tally->getCompanies();

            session([
                'last_sync' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tally Sync Completed',
                'data' => [
                    'companies' => $companies,
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function asignLedgers($company)
    {
        try {

            $company = urldecode($company);

            $xml = $this->tally->getLedgers($company);

            // Remove invalid XML entities like &#4;
            $xml = preg_replace('/&#x?0*4;?/i', '', $xml);
            $xml = preg_replace('/&#[0-8];|&#1[0-9];|&#2[0-9];|&#3[0-1];/', '', $xml);

            // Remove control characters
            $xml = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $xml);

            libxml_use_internal_errors(true);

            $xmlObj = simplexml_load_string($xml);

            if ($xmlObj === false) {
                foreach (libxml_get_errors() as $error) {
                    dump($error->message);
                }
            }

            $xmlObj = simplexml_load_string($xml);

            $ledgers = [];

            
            if ($xmlObj) {
                $nodes = $xmlObj->xpath("//*[local-name()='LEDGER']");
                if ($nodes) {
                    foreach ($nodes as $ledger) {
                        $name = (string) ($ledger['NAME'] ?? '');
                        $under = (string) ($ledger->PARENT ?? '');

                        // Sirf Sundry Debtors aur Sundry Creditors
                        if (
                            in_array(
                                trim($under),
                                ['Sundry Debtors', 'Sundry Creditors']
                            )
                        ) {
                            $ledgers[] = [
                                'name' => $name,
                                'under' => $under,
                            ];
                        }
                    }
                }
            }

            $assignedLedgers = LedgerCollector::leftJoin('rms_collectors', 'rms_collectors.id', '=', 'rms_ledger_collectors.collector_id')
                ->where('rms_ledger_collectors.company', $company)
                ->select(
                    'rms_ledger_collectors.ledger_name',
                    'rms_ledger_collectors.collector_id',
                    'rms_collectors.name as collector_name'
                )
                ->get()
                ->keyBy('ledger_name');

            return view(
                'collector.tally.ledgers-assign',
                compact('company', 'ledgers', 'assignedLedgers')
            );

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


    public function companyLedgers($company)
    {
        try {

            $company = urldecode($company);
            $collector = Auth::guard('collector')->user();
            $assignedLedgers = LedgerCollector::where('company', $company)
                ->where('collector_id', $collector->id)
                ->pluck('ledger_name')
                ->toArray();

            $xml = $this->tally->getLedgers($company);

            // Remove invalid XML entities like &#4;
            $xml = preg_replace('/&#x?0*4;?/i', '', $xml);
            $xml = preg_replace('/&#[0-8];|&#1[0-9];|&#2[0-9];|&#3[0-1];/', '', $xml);

            // Remove control characters
            $xml = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $xml);

            libxml_use_internal_errors(true);
            $xmlObj = simplexml_load_string($xml);
            if ($xmlObj === false) {
                foreach (libxml_get_errors() as $error) {
                    dump($error->message);
                }
            }

            $xmlObj = simplexml_load_string($xml);
            $ledgers = [];
            if ($xmlObj) {
                $nodes = $xmlObj->xpath("//*[local-name()='LEDGER']");
                if ($nodes) {
                    foreach ($nodes as $ledger) {
                        $name = (string) ($ledger['NAME'] ?? '');
                        $under = (string) ($ledger->PARENT ?? '');
                        // Sirf Sundry Debtors aur Sundry Creditors
                        if (
                            in_array(trim($under), ['Sundry Debtors', 'Sundry Creditors']) &&
                            in_array($name, $assignedLedgers)
                        ) {
                            $ledgers[] = [
                                'name'  => $name,
                                'under' => $under,
                            ];
                        }
                    }
                }
            }

            return view(
                'collector.tally.ledgers',
                compact('company', 'ledgers')
            );

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


    public function ledgerVouchers(Request $request, $company, $ledger, $under = null)
    {
        $under = urldecode($under);
        try {
            $voucherMappings = VoucherMapping::where('company', $company)
                                ->pluck('mapped_to', 'voucher_type')
                                ->toArray();

            $company = urldecode($company);
            $ledger  = urldecode($ledger);

            $xml = $this->tally->getLedgerVouchers($company, $ledger);

            $xml = preg_replace('/&#(?:0*4);?/i', '', $xml);
            $xml = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $xml);

            libxml_use_internal_errors(true);
            $xmlObj = simplexml_load_string($xml);

            if ($xmlObj === false) {
                $errors = [];
                foreach (libxml_get_errors() as $error) {
                    $errors[] = trim($error->message);
                }
                return back()->with('error', 'XML Parse Error: ' . implode(', ', $errors));
            }

            $vouchers = [];
            $nodes = $xmlObj->xpath("//*[local-name()='VOUCHER']");

            if ($nodes) {
                foreach ($nodes as $voucher) {
                    $belongsToLedger = false;
                    $particulars = [];
                    $ledgerAmount = 0;

                    if (isset($voucher->{'ALLLEDGERENTRIES.LIST'})) {
                        foreach ($voucher->{'ALLLEDGERENTRIES.LIST'} as $entry) {
                            $entryLedger = trim((string)($entry->LEDGERNAME ?? ''));
                            $amount = (float)($entry->AMOUNT ?? 0);
                            if (strcasecmp($entryLedger, $ledger) === 0) {
                                $belongsToLedger = true;
                                $ledgerAmount += $amount;
                            } else {
                                if (!empty($entryLedger)) {
                                    $particulars[] = $entryLedger;
                                }
                            }
                        }
                    }

                    if (!$belongsToLedger) {
                        continue;
                    }

                    $debit = 0;
                    $credit = 0;

                    if ($ledgerAmount < 0) {
                        $debit = abs($ledgerAmount);
                    } elseif ($ledgerAmount > 0) {
                        $credit = abs($ledgerAmount);
                    }

                    $voucherType = trim((string)($voucher->VOUCHERTYPENAME ?? ''));
                    $voucherDate = (string)($voucher->DATE ?? '');

                    $vouchers[] = [
                        'date'           => $voucherDate,
                        'particulars'    => !empty($particulars)
                            ? implode(', ', array_unique($particulars))
                            : (string)($voucher->NARRATION ?? ''),
                        'voucher_type'   => $voucherType,
                        'mapped_type'    => trim($voucherMappings[$voucherType] ?? 'Others'),
                        'voucher_number' => (string)($voucher->VOUCHERNUMBER ?? ''),
                        'debit'          => $debit,
                        'credit'         => $credit,
                        'master_id'      => (string)($voucher->MASTERID ?? ''),
                    ];
                }
            }

            // Opening / Closing Balance (all-time, as returned by Tally)
            $balanceXml = $this->tally->getLedgerDetails($company, $ledger);
            $balanceObj = simplexml_load_string($balanceXml);

            $openingBalanceAllTime = 0;
            $closingBalanceAllTime = 0;

            if ($balanceObj !== false && isset($balanceObj->BODY->DATA->COLLECTION->LEDGER)) {
                $ledgerData = $balanceObj->BODY->DATA->COLLECTION->LEDGER;
                $openingBalanceAllTime = (float) ($ledgerData->OPENINGBALANCE ?? 0);
                $closingBalanceAllTime = (float) ($ledgerData->CLOSINGBALANCE ?? 0);
            }

            // FY (Financial Year) resolution — Indian FY: 1 Apr - 31 Mar
            $today = \Carbon\Carbon::now();
            $currentFyStartYear = $today->month >= 4 ? (int)$today->year : (int)$today->year - 1;

            $fyParam = $request->get('fy');
            $selectedFyStartYear = $currentFyStartYear;

            if ($fyParam && preg_match('/^(\d{4})-(\d{4})$/', $fyParam, $m)) {
                if ((int)$m[2] === (int)$m[1] + 1) {
                    $selectedFyStartYear = (int)$m[1];
                }
            }

            $selectedFyLabel = $selectedFyStartYear . '-' . ($selectedFyStartYear + 1);
            $previousFyStartYear = $selectedFyStartYear - 1;
            $previousFyLabel = $previousFyStartYear . '-' . ($previousFyStartYear + 1);

            $selectedFyStart = \Carbon\Carbon::create($selectedFyStartYear, 4, 1)->startOfDay();
            $selectedFyEnd   = \Carbon\Carbon::create($selectedFyStartYear + 1, 3, 31)->endOfDay();

            $previousFyStart = \Carbon\Carbon::create($previousFyStartYear, 4, 1)->startOfDay();
            $previousFyEnd   = \Carbon\Carbon::create($previousFyStartYear + 1, 3, 31)->endOfDay();

            $fyOptions = [];
            for ($i = 0; $i < 5; $i++) {
                $y = $currentFyStartYear - $i;
                $fyOptions[] = $y . '-' . ($y + 1);
            }

            $parseDate = function ($d) {
                if (empty($d)) return null;
                try {
                    return \Carbon\Carbon::createFromFormat('Ymd', $d)->startOfDay();
                } catch (\Throwable $e) {
                    try {
                        return \Carbon\Carbon::parse($d)->startOfDay();
                    } catch (\Throwable $e2) {
                        return null;
                    }
                }
            };

            $vouchersAsc = $vouchers;
            usort($vouchersAsc, function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            
            $netBeforeSelectedFy = 0;
            $netWithinSelectedFy = 0;


            foreach ($vouchersAsc as $v) {
                $d = $parseDate($v['date']);
                if (!$d) continue;
                $net = $v['debit'] - $v['credit'];

                if ($d->lt($selectedFyStart)) {
                    $netBeforeSelectedFy += $net;
                } elseif ($d->between($selectedFyStart, $selectedFyEnd)) {
                    $netWithinSelectedFy += $net;
                }
            }

            $openingBalance = $openingBalanceAllTime + $netBeforeSelectedFy;
            $closingBalance = $openingBalance + $netWithinSelectedFy;

            $currentFyVouchers = array_values(array_filter($vouchers, function ($v) use ($parseDate, $selectedFyStart, $selectedFyEnd) {
                $d = $parseDate($v['date']);
                return $d && $d->between($selectedFyStart, $selectedFyEnd);
            }));

            $previousFyVouchers = array_values(array_filter($vouchers, function ($v) use ($parseDate, $previousFyStart, $previousFyEnd) {
                $d = $parseDate($v['date']);
                return $d && $d->between($previousFyStart, $previousFyEnd);
            }));

            $salesVouchers = collect($previousFyVouchers)
                ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'sales')
                ->sortByDesc('date')->values()->toArray();

            $receiptVouchers = collect($previousFyVouchers)
                ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'receipt')
                ->sortByDesc('date')->values()->toArray();

            $buildBuckets = function (array $voucherSet, ?string $under) {
                if ($under === "Sundry Debtors") {

                    $primaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'sales' && ($item['debit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $secondaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => ($item['credit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $journalVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) !== 'sales' && ($item['debit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $totalSales  = collect($primaryVouchers)->sum('debit');
                    $totalOthers = collect($journalVouchers)->sum('debit');
                    $totalCredit = collect($secondaryVouchers)->sum('credit');

                    $others = collect($journalVouchers)->sortBy('date')->values()
                        ->map(function ($v) { $v['pending'] = $v['debit']; return $v; })->toArray();

                    $sales = collect($primaryVouchers)->sortBy('date')->values()
                        ->map(function ($v) { $v['pending'] = $v['debit']; return $v; })->toArray();

                    $receipts = collect($secondaryVouchers)->sortBy('date')->values()->toArray();

                    foreach ($receipts as $receipt) {
                        $amount = $receipt['credit'];
                        foreach ($others as $index => $other) {
                            if ($amount <= 0) break;
                            if ($other['pending'] <= 0) continue;
                            $adjust = min($amount, $other['pending']);
                            $others[$index]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                        
                        foreach ($sales as $index => $sale) {
                            if ($amount <= 0) break;
                            if ($sale['pending'] <= 0) continue;
                            $adjust = min($amount, $sale['pending']);
                            $sales[$index]['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                    }


                    $pendingAmount = array_sum(array_column($others, 'pending'))
                        + array_sum(array_column($sales, 'pending'));

                    $pendingVouchers = collect(array_merge($sales, $others))
                        ->filter(fn($v) => ($v['pending'] ?? 0) > 0.01)
                        ->sortBy('date')
                        ->values()
                        ->toArray();

                    if ($totalCredit >= ($totalSales + $totalOthers)) {
                        $pendingAmount = 0;
                        $pendingVouchers = [];
                    }

                    $totalDebit = $totalSales + $totalOthers;

                    return [
                        'primaryVouchers'   => $sales,
                        'secondaryVouchers' => $secondaryVouchers,
                        'journalVouchers'   => $others,
                        'pendingVouchers'   => $pendingVouchers,
                        'primaryLabel'      => 'Total Debit',
                        'secondaryLabel'    => 'Total Credit',
                        'summary' => [
                            'sale'     => $totalDebit,
                            'receipts' => $totalCredit,
                            'pending'  => $pendingAmount,
                        ],
                    ];

                } elseif ($under === "Sundry Creditors") {

                    $primaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) === 'purchase' && ($item['credit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $secondaryVouchers = collect($voucherSet)
                        ->filter(fn($item) => ($item['debit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $journalVouchers = collect($voucherSet)
                        ->filter(fn($item) => strtolower(trim($item['mapped_type'])) !== 'purchase' && ($item['credit'] ?? 0) > 0)
                        ->sortByDesc('date')->values()->toArray();

                    $totalPurchase = collect($primaryVouchers)->sum('credit');
                    $totalOthers   = collect($journalVouchers)->sum('credit');
                    $totalDebit    = collect($secondaryVouchers)->sum('debit');

                    $others = collect($journalVouchers)->sortBy('date')->values()
                        ->map(function ($v) { $v['pending'] = $v['credit']; return $v; });

                    $purchases = collect($primaryVouchers)->sortBy('date')->values()
                        ->map(function ($v) { $v['pending'] = $v['credit']; return $v; });

                    $payments = collect($secondaryVouchers)->sortBy('date')->values();

                    foreach ($payments as $payment) {
                        $amount = $payment['debit'];
                        foreach ($others as &$other) {
                            if ($amount <= 0) break;
                            if ($other['pending'] <= 0) continue;
                            $adjust = min($amount, $other['pending']);
                            $other['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                        foreach ($purchases as &$purchase) {
                            if ($amount <= 0) break;
                            if ($purchase['pending'] <= 0) continue;
                            $adjust = min($amount, $purchase['pending']);
                            $purchase['pending'] -= $adjust;
                            $amount -= $adjust;
                        }
                    }

                    $pendingAmount = collect($others)->sum('pending') + collect($purchases)->sum('pending');

                    $pendingVouchers = collect(array_merge($purchases->toArray(), $others->toArray()))
                        ->filter(fn($v) => ($v['pending'] ?? 0) > 0.01)
                        ->sortBy('date')
                        ->values()
                        ->toArray();

                    if ($totalDebit >= ($totalPurchase + $totalOthers)) {
                        $pendingAmount = 0;
                        $pendingVouchers = [];
                    }

                    $totalCredit = $totalPurchase + $totalOthers;

                    return [
                        'primaryVouchers'   => $purchases->toArray(),
                        'secondaryVouchers' => $secondaryVouchers,
                        'journalVouchers'   => $others->toArray(),
                        'pendingVouchers'   => $pendingVouchers,
                        'primaryLabel'      => 'Total Credit',
                        'secondaryLabel'    => 'Total Debit',
                        'summary' => [
                            'sale'     => $totalCredit,
                            'receipts' => $totalDebit,
                            'pending'  => $pendingAmount,
                        ],
                    ];
                }

                return [
                    'primaryVouchers'   => [],
                    'secondaryVouchers' => [],
                    'journalVouchers'   => [],
                    'pendingVouchers'   => [],
                    'primaryLabel'      => 'Primary',
                    'secondaryLabel'    => 'Secondary',
                    'summary' => ['sale' => 0, 'receipts' => 0, 'pending' => 0],
                ];
            };

            // Cumulative: books ki shuruaat se lekar selected FY ke end tak — saare vouchers
            $cumulativeVouchersTillSelectedFy = array_values(array_filter($vouchersAsc, function ($v) use ($parseDate, $selectedFyEnd) {
                $d = $parseDate($v['date']);
                return $d && $d->lte($selectedFyEnd);
            }));

            // Cumulative: books ki shuruaat se lekar previous FY ke end tak — saare vouchers
            $cumulativeVouchersTillPreviousFy = array_values(array_filter($vouchersAsc, function ($v) use ($parseDate, $previousFyEnd) {
                $d = $parseDate($v['date']);
                return $d && $d->lte($previousFyEnd);
            }));

            $currentFyBuckets   = $buildBuckets($cumulativeVouchersTillSelectedFy, $under);
            $previousFyBuckets  = $buildBuckets($cumulativeVouchersTillPreviousFy, $under);

            $currentFyOnlyBuckets  = $buildBuckets($currentFyVouchers, $under);   
            $previousFyOnlyBuckets = $buildBuckets($previousFyVouchers, $under); 
            
            $summary           = $currentFyOnlyBuckets['summary'];  
            $primaryVouchers   = $previousFyOnlyBuckets['primaryVouchers'];

            $secondaryVouchers = $previousFyOnlyBuckets['secondaryVouchers'];
            $journalVouchers   = $previousFyOnlyBuckets['journalVouchers'];
            
            $primaryLabel      = $previousFyOnlyBuckets['primaryLabel'];
            $secondaryLabel    = $previousFyOnlyBuckets['secondaryLabel'];

            $summary['pending'] = $previousFyBuckets['summary']['pending'];   
            $pendingVouchers    = $previousFyBuckets['pendingVouchers'];      

            $closingBalanceVouchers = $currentFyBuckets['pendingVouchers'];  
            
            return view('collector.tally.ledger-vouchers', compact(
                'company',
                'ledger',
                'vouchers',
                'summary',
                'openingBalance',
                'closingBalance',
                'salesVouchers',
                'receiptVouchers',
                'journalVouchers',
                'under',
                'primaryVouchers',
                'secondaryVouchers',
                'primaryLabel',
                'secondaryLabel',
                'fyOptions',
                'selectedFyLabel',
                'previousFyLabel',
                'pendingVouchers',
                'closingBalanceVouchers'
            ));

        } catch (\Throwable $e) {
            Log::error('Ledger Voucher Error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }



    public function manualDashboard()
    {
        return view('collector.manual.dashboard');
    }

}

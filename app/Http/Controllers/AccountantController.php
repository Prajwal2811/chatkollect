<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Owner;
use App\Models\Accountant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Services\TallyService;
use App\Models\LedgerCollector;
use App\Models\VoucherMapping;


class AccountantController extends Controller
{
    public function subscription()
    {
        return view('accountant.subscription');
    }

    public function subscribe(Request $request)
    {
        $accountant = auth('accountant')->user();

        $accountant->update([
            'is_subscribed' => "true"
        ]);

        return redirect()->route('accountant.tally.dashboard')->with('success', 'Subscription activated successfully.');
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

        if (Auth::guard('accountant')->attempt($credentials, $request->boolean('remember'))) {
            $accountant = Auth::guard('accountant')->user();

            // Match model's actual column: business_type
            if ($accountant->business_type !== $request->account_type) {
                Auth::guard('accountant')->logout();

                Log::warning('Accountant login type mismatch', [
                    'email'         => $accountant->email,
                    'selected_type' => $request->account_type,
                    'actual_type'   => $accountant->business_type,
                    'time'          => now(),
                ]);

                session()->flash('error', 'This account is not registered under the selected type.');

                return back()->withInput($request->only('email', 'account_type'));
            }

            $request->session()->regenerate();

            Log::info('Accountant login successful', [
                'email' => $accountant->email,
                'name'  => $accountant->name,
                'type'  => $accountant->business_type,
                'time'  => now(),
            ]);

            // Redirect based on account type
            if ($accountant->business_type === 'manual') {
                return redirect()->route('accountant.manual.dashboard');
            }

            // Redirect to Tally dashboard if business type is tally
            if ($accountant->business_type === 'tally') {
                return redirect()->route('accountant.tally.dashboard');
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
        // Capture accountant info before logout
        $accountant = Auth::guard('accountant')->user();

        if ($accountant) {
            Log::info('Owner logged out', [
                'email' => $accountant->email,
                'name' => $accountant->accountant_name,
                'time' => now()
            ]);
        }

        Auth::guard('accountant')->logout(); // Logs out the accountant user
        session()->flash('success', 'You have been logged out successfully.');

        return redirect()->route('accountant.login'); // Redirect to named route
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

            return view('accountant.tally.index', compact(
                'companies',
                'xml',
                'tallyConnected'
            ));

        } catch (\Exception $e) {

            return view('accountant.tally.index', [
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
                'accountant.tally.ledgers-assign',
                compact('company', 'ledgers', 'assignedLedgers')
            );

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


    public function assignLedgers(Request $request)
    {
        $request->validate([
            'collector_id' => 'required',
            'ledgers' => 'required|array'
        ]);
        foreach ($request->ledgers as $item) {
            $ledger = json_decode($item, true);

            LedgerCollector::updateOrCreate(
                [
                    'company' => $request->company,
                    'ledger_name' => $ledger['name'],
                ],
                [
                    'ledger_under' => $ledger['under'],
                    'collector_id' => $request->collector_id,
                ]
            );
        }
        return back()->with('success', 'Collector assigned successfully.');
    }

    public function companyLedgers($company)
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

            return view(
                'accountant.tally.ledgers',
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

            $currentFyBuckets  = $buildBuckets($cumulativeVouchersTillSelectedFy, $under);
            $previousFyBuckets = $buildBuckets($cumulativeVouchersTillPreviousFy, $under);

            $currentFyOnlyBuckets  = $buildBuckets($currentFyVouchers, $under);   
            $previousFyOnlyBuckets = $buildBuckets($previousFyVouchers, $under);  // sirf FY ke andar ke totals ke liye
            
            $summary           = $currentFyOnlyBuckets['summary'];   // Total Sale/Purchase, Total Receipt/Payment cards — sirf selected FY
            $primaryVouchers   = $previousFyOnlyBuckets['primaryVouchers'];
            $secondaryVouchers = $previousFyOnlyBuckets['secondaryVouchers'];
            $journalVouchers   = $previousFyOnlyBuckets['journalVouchers'];
            $primaryLabel      = $previousFyOnlyBuckets['primaryLabel'];
            $secondaryLabel    = $previousFyOnlyBuckets['secondaryLabel'];

            $summary['pending'] = $previousFyBuckets['summary']['pending'];   // cumulative pending (all history till previous FY end)
            $pendingVouchers    = $previousFyBuckets['pendingVouchers'];      // cumulative pending vouchers list

            $closingBalanceVouchers = $currentFyBuckets['pendingVouchers'];  
            
            return view('accountant.tally.ledger-vouchers', compact(
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
 
    public function ledgerFollowUp(Request $request, $company, $ledger, $under = null){

        $vouchers = $this->ledgerVouchers($request, $company, $ledger, $under);
        return view('accountant.tally.ledger-follow-up', compact('company', 'ledger', 'vouchers'));
    }



    public function followUpsHub(Request $request, $company)
    {
        $company = urldecode($company);

        $type   = $request->query('type') ?: ''; // agar khaali ho to sab types dikhenge
        $ledger = urldecode($request->query('ledger', ''));
        $under  = urldecode($request->query('under', ''));

        $allLedgers = [
            [
                'name' => 'Dummy Ledger 1', 'under' => 'Sundry Debtors', 'mobile' => '9876543210',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 54321.10, 'target' => 50000,
                'status' => 'Pending', 'action' => 'call', 'frequency' => 'daily',
            ],
            [
                'name' => 'Dummy Ledger 2', 'under' => 'Sundry Debtors', 'mobile' => '9123456780',
                'type' => 'Follow Up-Balances/Targets(months)',
                'balance' => 72500.00, 'target' => 80000,
                'target_month' => now()->subMonths(1)->format('Y-m'),
                'status' => 'Responded', 'action' => 'whatsapp', 'frequency' => 'weekly',
                'allocation_date' => now()->subDays(10)->format('Y-m-d'),
                'response_date'   => now()->subDays(3)->format('Y-m-d'),
                'response' => 'promised_to_pay',
                'solution' => 'Payment committed by next week',
            ],
            [
                'name' => 'Dummy Ledger 3', 'under' => 'Sundry Debtors', 'mobile' => '9988776655',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 18200.50, 'age_bucket' => '31-60 Days', 'days' => 45,
                'status' => 'Pending', 'action' => 'escalation', 'frequency' => 'monthly',
            ],
            [
                'name' => 'Dummy Ledger 4', 'under' => 'Sundry Creditors', 'mobile' => '9871234567',
                'type' => 'Follow Up-Balances/Days(10-20-30)',
                'balance' => 9800.00, 'days' => 20,
                'date' => now()->subDays(20)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2204',
                'interest_due' => 150.00,
                'status' => 'Responded', 'action' => 'allocate_tele_call', 'frequency' => 'daily',
                'allocation_date' => now()->subDays(6)->format('Y-m-d'),
                'response_date'   => now()->subDays(1)->format('Y-m-d'),
                'response' => 'paid',
                'solution' => 'Full amount received via NEFT',
            ],
            [
                'name' => 'Dummy Ledger 5', 'under' => 'Sundry Creditors', 'mobile' => '9012345678',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(21)->format('Y-m-d'),
                'due_date' => now()->addDays(21)->format('Y-m-d'),
                'inv_no' => 'INV-2201', 'days' => 12,
                'due' => 32000.00, 'interest_due' => 480.00,
                'status' => 'Pending', 'action' => 'physical_visit', 'frequency' => 'weekly',
            ],
            [
                'name' => 'Dummy Ledger 6', 'under' => 'Sundry Debtors', 'mobile' => '9090909090',
                'type' => 'Follow Up-Not Due',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'due_date' => now()->addDays(35)->format('Y-m-d'),
                'inv_no' => 'INV-2202', 'days_pending' => 5,
                'not_due' => 27500.00,
                'status' => 'Responded', 'action' => 'no_action', 'frequency' => 'monthly',
                'allocation_date' => now()->subDays(15)->format('Y-m-d'),
                'response_date'   => now()->subDays(9)->format('Y-m-d'),
                'response' => 'no_response',
                'solution' => 'Not reachable, will retry next cycle',
            ],
            [
                'name' => 'Dummy Ledger 7', 'under' => 'Sundry Creditors', 'mobile' => '9191919191',
                'type' => 'Follow Up Overlimits',
                'balance' => 12500.00,
                'status' => 'Pending', 'action' => 'escalation', 'frequency' => 'daily',
            ],
            [
                'name' => 'Dummy Ledger 8', 'under' => 'Sundry Debtors', 'mobile' => '9898989898',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 61230.00, 'target' => 55000,
                'status' => 'Responded', 'action' => 'call', 'frequency' => 'weekly',
                'allocation_date' => now()->subDays(8)->format('Y-m-d'),
                'response_date'   => now()->subDays(2)->format('Y-m-d'),
                'response' => 'disputed',
                'solution' => 'Customer disputes 5k, verifying invoice',
            ],
            [
                'name' => 'Dummy Ledger 9', 'under' => 'Sundry Creditors', 'mobile' => '9797979797',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(10)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2203', 'days' => 18,
                'due' => 41500.00, 'interest_due' => 620.00,
                'status' => 'Pending', 'action' => 'call', 'frequency' => 'daily',
            ],
            [
                'name' => 'Dummy Ledger 10', 'under' => 'Sundry Debtors', 'mobile' => '9696969696',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 9450.75, 'age_bucket' => '0-30 Days', 'days' => 15,
                'status' => 'Responded', 'action' => 'whatsapp', 'frequency' => 'weekly',
                'allocation_date' => now()->subDays(12)->format('Y-m-d'),
                'response_date'   => now()->subDays(4)->format('Y-m-d'),
                'response' => 'paid',
                'solution' => 'Cleared full outstanding',
            ],
            [
                'name' => 'Dummy Ledger 11', 'under' => 'Sundry Debtors', 'mobile' => '9595959595',
                'type' => 'Follow Up-Balances',
                'balance' => 15750.25,
                'status' => 'Pending', 'action' => 'physical_visit', 'frequency' => 'monthly',
            ],
        ];

        if (!empty($ledger)) {
            $allLedgers = collect($allLedgers)
                ->filter(fn($l) => $l['name'] === $ledger)
                ->values()
                ->all();
        } elseif (!empty($under)) {
            $allLedgers = collect($allLedgers)
                ->filter(fn($l) => ($l['under'] ?? null) === $under)
                ->values()
                ->all();
        }

        $followUps = collect($allLedgers)->values()->map(function ($l, $index) {
            $balance   = $l['balance'] ?? 0;
            $target    = $l['target'] ?? 0;
            $targetPct = $target > 0 ? round((abs($balance) / $target) * 100, 2) : 0;

            return [
                'id'              => $index + 1,
                'name'            => $l['name'] ?? '-',
                'mobile'          => $l['mobile'] ?? '-',
                'under'           => $l['under'] ?? '-',
                'type'            => $l['type'] ?? '-',
                'balance'         => $balance,
                'target'          => $target,
                'target_pct'      => $targetPct,
                'target_month'    => $l['target_month'] ?? null,
                'age_bucket'      => $l['age_bucket'] ?? null,
                'days'            => $l['days'] ?? null,
                'days_pending'    => $l['days_pending'] ?? null,
                'date'            => $l['date'] ?? null,
                'due_date'        => $l['due_date'] ?? null,
                'inv_no'          => $l['inv_no'] ?? null,
                'due'             => $l['due'] ?? null,
                'not_due'         => $l['not_due'] ?? null,
                'interest_due'    => $l['interest_due'] ?? null,
                'status'          => $l['status'] ?? 'Pending',
                'action'          => $l['action'] ?? null,
                'frequency'       => $l['frequency'] ?? null,
                'allocation_date' => $l['allocation_date'] ?? null,
                'response_date'   => $l['response_date'] ?? null,
                'response'        => $l['response'] ?? null,
                'solution'        => $l['solution'] ?? null,
            ];
        });

        if (!empty($type) && $type !== 'Follow Up-Quick /Smart - Follow -up') {
            $followUps = $followUps->filter(fn($row) => $row['type'] === $type)->values();
        }

        return view('accountant.tally.followup-hub', [
            'company'        => $company,
            'type'           => $type,
            'selectedLedger' => $ledger,
            'selectedUnder'  => $under,
            'followUps'      => $followUps,
        ]);
    }

    public function telecaller()
    {
        return view('accountant.tally.followup-action.telecaller');
    }

    public function call()
    {
        return view('accountant.tally.followup-action.call');
    }

    public function whatsappMessage()
    {
        return view('accountant.tally.followup-action.whatsapp-message');
    }

    public function physicalVisit()
    {
        return view('accountant.tally.followup-action.physical-visit');
    }

    public function escalation()
    {
        return view('accountant.tally.followup-action.escalation');
    }


  
  
    public function followupHistory()
    {
        $typeLabels = [
            'balances'      => 'Follow Up-Balances',
            'pct_targets'    => 'Follow Up-Balances/%Targets',
            'targets_months' => 'Follow Up-Balances/Targets(months)',
            'days_agewise'   => 'Follow Up-Balances/Days(agewise)',
            'days_10_20_30'  => 'Follow Up-Balances/Days(10-20-30)',
            'due'            => 'Follow Up-Due',
            'not_due'        => 'Follow Up-Not Due',
            'overlimits'     => 'Follow Up Overlimits',
        ];
    
        $actionLabels = [
            'allocate_tele_call' => 'Allocate to tele call',
            'call'                => 'Call',
            'whatsapp'            => 'WhatsApp',
            'physical_visit'      => 'Physical visit',
            'escalation'          => 'Escalation',
            'no_action'           => 'No action',
        ];
    
         
        $history = [
            [
                'id' => 1, 'ledger_id' => 101, 'ledger_name' => 'ABC Traders', 'mobile' => '9000000001',
                'type' => 'pct_targets', 'type_label' => $typeLabels['pct_targets'],
                'balance' => 54321.10, 'target' => 50000, 'target_pct' => 108.64,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01', 'action' => 'call', 'action_label' => $actionLabels['call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th', 'solution' => 'Payment plan agreed', 'admin_solution' => null,
            ],
            [
                'id' => 2, 'ledger_id' => 101, 'ledger_name' => 'ABC Traders', 'mobile' => '9000000001',
                'type' => 'due', 'type_label' => $typeLabels['due'],
                'balance' => 32000, 'target' => null, 'target_pct' => null,
                'date' => '2026-07-20', 'due_date' => '2026-08-10', 'inv_no' => 'INV-2201', 'days' => 12, 'interest_due' => 480,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-02', 'action' => 'whatsapp', 'action_label' => $actionLabels['whatsapp'],
                'frequency' => 'daily', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 3, 'ledger_id' => 101, 'ledger_name' => 'ABC Traders', 'mobile' => '9000000001',
                'type' => 'overlimits', 'type_label' => $typeLabels['overlimits'],
                'balance' => 12500, 'target' => null, 'target_pct' => null,
                'assigned_to' => 'Priya', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-28', 'action' => 'escalation', 'action_label' => $actionLabels['escalation'],
                'frequency' => 'weekly', 'response_date' => '2026-07-30',
                'response' => 'Escalated to regional manager', 'solution' => 'Limit revised', 'admin_solution' => null,
            ],
            [
                'id' => 4, 'ledger_id' => 102, 'ledger_name' => 'XYZ Enterprises', 'mobile' => '9000000002',
                'type' => 'targets_months', 'type_label' => $typeLabels['targets_months'],
                'balance' => 78900, 'target' => 80000, 'target_pct' => 98.63,
                'due_date' => '2026-07-31', // used as Target Month for this type
                'assigned_to' => 'Aman', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-15', 'action' => 'physical_visit', 'action_label' => $actionLabels['physical_visit'],
                'frequency' => 'monthly', 'response_date' => '2026-07-20',
                'response' => 'Visited site, cheque collected', 'solution' => 'Cleared 50% balance', 'admin_solution' => null,
            ],
            [
                'id' => 5, 'ledger_id' => 102, 'ledger_name' => 'XYZ Enterprises', 'mobile' => '9000000002',
                'type' => 'days_10_20_30', 'type_label' => $typeLabels['days_10_20_30'],
                'balance' => 15600, 'target' => null, 'target_pct' => null, 'days' => 20,
                'assigned_to' => 'Aman', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-05', 'action' => 'no_action', 'action_label' => $actionLabels['no_action'],
                'frequency' => 'daily', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 6, 'ledger_id' => 103, 'ledger_name' => 'Global Textiles', 'mobile' => '9000000003',
                'type' => 'not_due', 'type_label' => $typeLabels['not_due'],
                'balance' => 43200, 'target' => null, 'target_pct' => null,
                'date' => '2026-07-25', 'due_date' => '2026-08-25', 'inv_no' => 'INV-3105', 'days_pending' => 30,
                'assigned_to' => 'Priya', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-06', 'action' => 'allocate_tele_call', 'action_label' => $actionLabels['allocate_tele_call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-07',
                'response' => 'Confirmed, will pay on due date', 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 7, 'ledger_id' => 104, 'ledger_name' => 'Sunrise Distributors', 'mobile' => '9000000004',
                'type' => 'days_agewise', 'type_label' => $typeLabels['days_agewise'],
                'balance' => 99500, 'target' => null, 'target_pct' => null, 'days' => 46,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-10', 'action' => 'escalation', 'action_label' => $actionLabels['escalation'],
                'frequency' => 'monthly', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 8, 'ledger_id' => 105, 'ledger_name' => 'ABCD Traders', 'mobile' => '9000000001',
                'type' => 'balances', 'type_label' => $typeLabels['balances'],
                'balance' => 54321.10, 'target' => 50000, 'target_pct' => 108.64,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01', 'action' => 'call', 'action_label' => $actionLabels['call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th', 'solution' => 'Payment plan agreed', 'admin_solution' => null,
            ],
        ];
    
        return view('accountant.tally.followup-history', compact('history'));
    }




     // Manual controller functions 
    public function manualDashboard()
    {
        return view('accountant.manual.dashboard');

    }


    public function manualFollowUpsHub(Request $request)
    {
        $type   = $request->query('type') ?: ''; 
        $allLedgers = [
            [
                'name' => 'Rahul Sharma', 'mobile' => '9876543210',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 54321.10, 'target' => 50000,
            ],
            [
                'name' => 'Priya Verma', 'mobile' => '9123456780',
                'type' => 'Follow Up-Balances/Targets(months)',
                'balance' => 72500.00, 'target' => 80000,
                'target_month' => now()->subMonths(1)->format('Y-m'),
            ],
            [
                'name' => 'Aman Gupta', 'mobile' => '9988776655',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 18200.50, 'age_bucket' => '31-60 Days', 'days' => 45,
            ],
            [
                'name' => 'Sneha Patel', 'mobile' => '9871234567',
                'type' => 'Follow Up-Balances/Days(10-20-30)',
                'balance' => 9800.00, 'days' => 20,
                'date' => now()->subDays(20)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2204',
                'interest_due' => 150.00,
            ],
            [
                'name' => 'Vikram Singh', 'mobile' => '9012345678',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(21)->format('Y-m-d'),
                'due_date' => now()->addDays(21)->format('Y-m-d'),
                'inv_no' => 'INV-2201', 'days' => 12,
                'due' => 32000.00, 'interest_due' => 480.00,
            ],
            [
                'name' => 'Anjali Mehta', 'mobile' => '9090909090',
                'type' => 'Follow Up-Not Due',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'due_date' => now()->addDays(35)->format('Y-m-d'),
                'inv_no' => 'INV-2202', 'days_pending' => 5,
                'not_due' => 27500.00,
            ],
            [
                'name' => 'Karan Malhotra', 'mobile' => '9191919191',
                'type' => 'Follow Up Overlimits',
                'balance' => 12500.00,
            ],
            [
                'name' => 'Neha Joshi', 'mobile' => '9898989898',
                'type' => 'Follow Up-Balances/%Targets',
                'balance' => 61230.00, 'target' => 55000,
            ],
            [
                'name' => 'Rohan Desai', 'mobile' => '9797979797',
                'type' => 'Follow Up-Due',
                'date' => now()->subDays(10)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'inv_no' => 'INV-2203', 'days' => 18,
                'due' => 41500.00, 'interest_due' => 620.00,
            ],
            [
                'name' => 'Ishita Rao', 'mobile' => '9696969696',
                'type' => 'Follow Up-Balances/Days(agewise)',
                'balance' => 9450.75, 'age_bucket' => '0-30 Days', 'days' => 15,
            ],
            [
                'name' => 'Aditya Kumar', 'mobile' => '9595959595',
                'type' => 'Follow Up-Balances',
                'balance' => 15750.25,
            ],
        ];

        $followUps = collect($allLedgers)->values()->map(function ($l, $index) {
            $balance   = $l['balance'] ?? 0;
            $target    = $l['target'] ?? 0;
            $targetPct = $target > 0 ? round((abs($balance) / $target) * 100, 2) : 0;

            return [
                'id'                => $index + 1,
                'name'              => $l['name'] ?? '-',
                'mobile'            => $l['mobile'] ?? '-',
                'under'             => $l['under'] ?? '-',
                'type'              => $l['type'] ?? '-',
                'balance'           => $balance,
                'target'            => $target,
                'target_pct'        => $targetPct,
                'target_month'      => $l['target_month'] ?? null,
                'age_bucket'        => $l['age_bucket'] ?? null,
                'days'              => $l['days'] ?? null,
                'days_pending'      => $l['days_pending'] ?? null,
                'date'              => $l['date'] ?? null,
                'due_date'          => $l['due_date'] ?? null,
                'inv_no'            => $l['inv_no'] ?? null,
                'due'               => $l['due'] ?? null,
                'not_due'           => $l['not_due'] ?? null,
                'interest_due'      => $l['interest_due'] ?? null,
                'accountant_status' => rand(0, 1) ? 'Pending' : 'Responded',
            ];
        });

        if (!empty($type) && $type !== 'Follow Up-Quick /Smart - Follow -up') {
            $followUps = $followUps->filter(fn($row) => $row['type'] === $type)->values();
        }

        return view('accountant.manual.followup-hub', [
            'followUps'      => $followUps,
            'type'           => $type,
        ]);
    }


    public function manualTelecaller()
    {
        return view('accountant.manual.followup-action.telecaller');
    }

    public function manualCall()
    {
        return view('accountant.manual.followup-action.call');
    }

    public function manualWhatsappMessage()
    {
        return view('accountant.manual.followup-action.whatsapp-message');
    }

    public function manualPhysicalVisit()
    {
        return view('accountant.manual.followup-action.physical-visit');
    }

    public function manualEscalation()
    {
        return view('accountant.manual.followup-action.escalation');
    }

    public function manualFollowupHistory()
    {
        $typeLabels = [
            'balances'      => 'Follow Up-Balances',
            'pct_targets'    => 'Follow Up-Balances/%Targets',
            'targets_months' => 'Follow Up-Balances/Targets(months)',
            'days_agewise'   => 'Follow Up-Balances/Days(agewise)',
            'days_10_20_30'  => 'Follow Up-Balances/Days(10-20-30)',
            'due'            => 'Follow Up-Due',
            'not_due'        => 'Follow Up-Not Due',
            'overlimits'     => 'Follow Up Overlimits',
        ];
    
        $actionLabels = [
            'allocate_tele_call' => 'Allocate to tele call',
            'call'                => 'Call',
            'whatsapp'            => 'WhatsApp',
            'physical_visit'      => 'Physical visit',
            'escalation'          => 'Escalation',
            'no_action'           => 'No action',
        ];

        $history = [
            [
                'id' => 1, 'ledger_id' => 101, 'student_name' => 'Rahul Mehta', 'mobile' => '9000000001',
                'type' => 'pct_targets', 'type_label' => $typeLabels['pct_targets'],
                'balance' => 54321.10, 'target' => 50000, 'target_pct' => 108.64,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01', 'action' => 'call', 'action_label' => $actionLabels['call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th', 'solution' => 'Payment plan agreed', 'admin_solution' => null,
            ],
            [
                'id' => 2, 'ledger_id' => 101, 'student_name' => 'Rahul Mehta', 'mobile' => '9000000001',
                'type' => 'due', 'type_label' => $typeLabels['due'],
                'balance' => 32000, 'target' => null, 'target_pct' => null,
                'date' => '2026-07-20', 'due_date' => '2026-08-10', 'inv_no' => 'INV-2201', 'days' => 12, 'interest_due' => 480,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-02', 'action' => 'whatsapp', 'action_label' => $actionLabels['whatsapp'],
                'frequency' => 'daily', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 3, 'ledger_id' => 101, 'student_name' => 'Rahul Mehta', 'mobile' => '9000000001',
                'type' => 'overlimits', 'type_label' => $typeLabels['overlimits'],
                'balance' => 12500, 'target' => null, 'target_pct' => null,
                'assigned_to' => 'Priya', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-28', 'action' => 'escalation', 'action_label' => $actionLabels['escalation'],
                'frequency' => 'weekly', 'response_date' => '2026-07-30',
                'response' => 'Escalated to regional manager', 'solution' => 'Limit revised', 'admin_solution' => null,
            ],
            [
                'id' => 4, 'ledger_id' => 102, 'student_name' => 'Sneha Kapoor', 'mobile' => '9000000002',
                'type' => 'targets_months', 'type_label' => $typeLabels['targets_months'],
                'balance' => 78900, 'target' => 80000, 'target_pct' => 98.63,
                'due_date' => '2026-07-31', // used as Target Month for this type
                'assigned_to' => 'Aman', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-15', 'action' => 'physical_visit', 'action_label' => $actionLabels['physical_visit'],
                'frequency' => 'monthly', 'response_date' => '2026-07-20',
                'response' => 'Visited site, cheque collected', 'solution' => 'Cleared 50% balance', 'admin_solution' => null,
            ],
            [
                'id' => 5, 'ledger_id' => 102, 'student_name' => 'Sneha Kapoor', 'mobile' => '9000000002',
                'type' => 'days_10_20_30', 'type_label' => $typeLabels['days_10_20_30'],
                'balance' => 15600, 'target' => null, 'target_pct' => null, 'days' => 20,
                'assigned_to' => 'Aman', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-05', 'action' => 'no_action', 'action_label' => $actionLabels['no_action'],
                'frequency' => 'daily', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 6, 'ledger_id' => 103, 'student_name' => 'Amit Deshmukh', 'mobile' => '9000000003',
                'type' => 'not_due', 'type_label' => $typeLabels['not_due'],
                'balance' => 43200, 'target' => null, 'target_pct' => null,
                'date' => '2026-07-25', 'due_date' => '2026-08-25', 'inv_no' => 'INV-3105', 'days_pending' => 30,
                'assigned_to' => 'Priya', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-06', 'action' => 'allocate_tele_call', 'action_label' => $actionLabels['allocate_tele_call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-07',
                'response' => 'Confirmed, will pay on due date', 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 7, 'ledger_id' => 104, 'student_name' => 'Karan Malhotra', 'mobile' => '9000000004',
                'type' => 'days_agewise', 'type_label' => $typeLabels['days_agewise'],
                'balance' => 99500, 'target' => null, 'target_pct' => null, 'days' => 46,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-07-10', 'action' => 'escalation', 'action_label' => $actionLabels['escalation'],
                'frequency' => 'monthly', 'response_date' => null,
                'response' => null, 'solution' => null, 'admin_solution' => null,
            ],
            [
                'id' => 8, 'ledger_id' => 105, 'student_name' => 'Divya Nair', 'mobile' => '9000000001',
                'type' => 'balances', 'type_label' => $typeLabels['balances'],
                'balance' => 54321.10, 'target' => 50000, 'target_pct' => 108.64,
                'assigned_to' => 'Rahul', 'assigned_by' => 'Owner',
                'allocation_date' => '2026-08-01', 'action' => 'call', 'action_label' => $actionLabels['call'],
                'frequency' => 'weekly', 'response_date' => '2026-08-03',
                'response' => 'Customer will pay by 10th', 'solution' => 'Payment plan agreed', 'admin_solution' => null,
            ],
        ];
        
        // print_r($history); die;
        return view('accountant.manual.followup-history', compact('history'));
    }
}
 
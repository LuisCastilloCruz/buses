<?php
namespace Modules\Account\Http\Controllers;

use Modules\Account\Exports\ReportAccountingConcarExport;
use Modules\Account\Exports\ReportAccountingFoxcontExport;
use Modules\Account\Exports\ReportAccountingContasisExport;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Item;
use App\Models\Tenant\Note;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Configuration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Account\Models\CompanyAccount;
use DateTime;

class TributoController extends Controller
{
    public function index()
    {
        return view('account::account.tributo');
    }

    public function table(Request $request)
    {
        $year   = $request->input('year');
        $year   = date('Y-m-d');
        $records = $this->getDocuments($year);

        //dd($records);
        return [
            'tabla' => $records
        ];

    }

    private function getDocuments($year)
    {

            return Document::query()
                ->whereYear('date_of_issue', $year)
                ->whereIn('document_type_id', ['01', '03','07','08'])
                ->whereIn('currency_type_id', ['PEN','USD'])
                ->take(4)
                ->get();

        
    }

}

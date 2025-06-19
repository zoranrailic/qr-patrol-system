<?php

namespace App\Http\Controllers\Backend\Attendance;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Attendance;
use Auth;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Summary of index
     * @param \Yajra\Datatables\Datatables $datatables
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Datatables $datatables, Request $request)
    {
        $columns = [
            'name' => ['name' => 'history.name'],
            'date',
            'in_time',
            'out_time',
            'work_hour',
            'over_time',
            'late_time',
            'early_out_time',
            'in_location',
            'out_location'
        ];

        $from = null;
        $to = null;

        if ($request->dateFrom && $request->dateTo) {
            try {
                $from = new \DateTimeImmutable($request->dateFrom);
                $to = new \DateTimeImmutable($request->dateTo);
            } catch (\Exception $e) {
                // Handle date parsing error
                Log::error("Filter by date failed: " . $e->getMessage());
            }
        }

        if ($datatables->getRequest()->ajax()) {
            $query = Attendance::with('history')
                ->select('attendances.*');

            if ($from && $to) {
                $query = $query->whereBetween('date', [$from, $to]);
            }

            // worker
            if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
                $getUserInfo = History::where('name', Auth::User()->name)->first();
                if ($getUserInfo) {
                    // There is any worker
                    $query = $query->where('worker_id', $getUserInfo->id);
                } else {
                    // If there is no data attendance for this worker we add 0 id, mean data not found
                    $query = $query->where('worker_id', 0);
                }
            }

            return $datatables->of($query)
                ->addColumn('name', function (Attendance $data) {
                    return $data->history->name;
                })
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6,7,8,9];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc'], [2,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.attendances.index', compact('html'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        return [
            [
                'pageLength'
            ],
            [
                'extend' => 'csvHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'pdfHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'excelHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'print',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
        ];
    }

    /**
     * Summary of scriptMinifiedJs
     * @return string
     */
    public function scriptMinifiedJs()
    {
        // Script to minified the ajax
        return <<<CDATA
            var formData = $("#date_filter").find("input").serializeArray();
            $.each(formData, function(i, obj){
                data[obj.name] = obj.value;
            });
        CDATA;
    }
}

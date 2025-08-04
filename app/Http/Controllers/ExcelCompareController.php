<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class ExcelCompareController extends Controller
{
    public function index()
    {
        return view('upload');
    }

 public function compare(Request $request)
{
       $request->validate([
        'excel1' => 'required|file|mimes:xlsx,xls',
        'excel2' => 'required|file|mimes:xlsx,xls',
        'project_name' => 'nullable|string|max:255',
    ]);

    $projectName = $request->input('project_name', 'Untitled Project');

    $file1 = $request->file('excel1')->getRealPath();
    $file2 = $request->file('excel2')->getRealPath();

    $spread1 = IOFactory::load($file1);
    $spread2 = IOFactory::load($file2);

    $sheet1 = $spread1->getActiveSheet()->toArray();
    $sheet2 = $spread2->getActiveSheet()->toArray();

    if (empty($sheet1) || empty($sheet2)) {
        return back()->withErrors(['One of the files is empty.']);
    }

   $header1 = array_map(fn($v) => strtoupper(str_replace(' ', '', trim($v))), $sheet1[0]);
$header2 = array_map(fn($v) => strtoupper(str_replace(' ', '', trim($v))), $sheet2[0]);

    $idx1 = array_search('COMPANYPARTNO', $header1);
    $idx2 = array_search('COMPANYPARTNO', $header2);

    if ($idx1 === false || $idx2 === false) {
        return back()->withErrors(['COMPANYPARTNO column not found in both files.']);
    }

    $data1 = [];
    foreach (array_slice($sheet1, 1) as $row) {
        $key = $row[$idx1] ?? null;
        
        if ($key) {
            $data1[$key] = $row;
        }
    }

    $data2 = [];
    foreach (array_slice($sheet2, 1) as $row) {
        $key = $row[$idx2] ?? null;
        if ($key) {
            $data2[$key] = $row;
        }
    }

    $spread3 = new Spreadsheet();
    $sheet3 = $spread3->getActiveSheet();

    $maxCols = max(count($header1), count($header2));
    $mergeEndCol = Coordinate::stringFromColumnIndex($maxCols * 2);

    // ✅ Project name at top
    $sheet3->setCellValue('A1', strtoupper($projectName));
    $sheet3->mergeCells("A1:{$mergeEndCol}1");
    $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet3->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // ✅ Date below project name
    $sheet3->setCellValue('A2', 'Date: ' . date('Y-m-d'));

    // ✅ Headers at row 4
    for ($i = 0; $i < $maxCols; $i++) {
        $sheet3->setCellValueByColumnAndRow($i + 1, 4, $header1[$i] ?? '');
        $sheet3->setCellValueByColumnAndRow($i + 1 + $maxCols, 4, $header2[$i] ?? '');
    }

    // ✅ Data from row 5
    $rowNo = 5;
    foreach ($data1 as $key => $rowA) {
        $rowB = $data2[$key] ?? array_fill(0, $maxCols, '');

        for ($col = 0; $col < $maxCols; $col++) {
            $sheet3->setCellValueByColumnAndRow($col + 1, $rowNo, $rowA[$col] ?? '');
            $val2 = $rowB[$col] ?? '';
            $colB = $col + 1 + $maxCols;
            $sheet3->setCellValueByColumnAndRow($colB, $rowNo, $val2);

            if (($rowA[$col] ?? '') !== $val2) {
                $sheet3->getStyleByColumnAndRow($colB, $rowNo)
                    ->getFont()->getColor()->setARGB('FFFF0000');
            }
        }

        $rowNo++;
    }

    // ✅ Auto-size all columns
    $highestColumn = $sheet3->getHighestColumn();
    $highestColIndex = Coordinate::columnIndexFromString($highestColumn);

    for ($col = 1; $col <= $highestColIndex; $col++) {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        $sheet3->getColumnDimension($colLetter)->setAutoSize(true);
    }

    // ✅ Save final file
    $out = storage_path('app/public/excel3.xlsx');
    (new Xlsx($spread3))->save($out);

    return redirect()->route('download.page');
}

    public function downloadPage()
    {
        return view('download');
    }

    public function download()
    {
        $path = storage_path('app/public/excel3.xlsx');
        if (!file_exists($path)) {
            return redirect('/')->withErrors(['File not found, please upload and compare first.']);
        }
        return response()->download($path, 'excel3.xlsx');
    }
}
?>

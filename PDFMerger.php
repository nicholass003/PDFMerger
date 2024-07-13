<?php

require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

const FOLDER_PATH = __DIR__ . '/files_to_merge/';
const OUTPUT_PATH = __DIR__ . '/merged_files/';

const PDF_FORMAT = '.pdf';

function merge_pdfs($pdf_files, $output_file) {
    $pdf = new FPDI();
    $total_files = count($pdf_files);
    $processed_files = 0;

    foreach($pdf_files as $file){
        $pageCount = $pdf->setSourceFile($file);
        for($pageNo = 1; $pageNo <= $pageCount; $pageNo++){
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }
        $processed_files++;
        $progress = ($processed_files / $total_files) * 100;
        echo "\rProcessing... " . (int) $progress . "%";
    }

    $pdf->Output('F', OUTPUT_PATH . $output_file);
}

$all_pdf_files = glob(FOLDER_PATH . '*' . PDF_FORMAT);

if(count($argv) < 2){
    echo "Please enter the output file name.";
    return;
}

if(!$all_pdf_files){
    echo "Failed to load pdf files.";
    return;
}elseif(count($all_pdf_files) === 0){
    echo "Please enter files, at least 2 files or more.";
    return;
}

$pdf_files = [];

foreach($all_pdf_files as $pdf_file){
    $file_page = (int) preg_replace('/[^0-9]/', '', $pdf_file);
    $pdf_files[$file_page] = $pdf_file;
}

ksort($pdf_files);

$output_files = glob(OUTPUT_PATH . '*' . PDF_FORMAT);

$output_name = (string) $argv[1];
if(!str_contains($output_name, PDF_FORMAT)){
    $output_name .= PDF_FORMAT;
}

if(file_exists(OUTPUT_PATH . $output_name)){
    $file_info = pathinfo($output_name);
    $output_name = str_replace(" ", "_", $file_info['filename'] . " (" . count($output_files) . ")" . PDF_FORMAT);
}

$start = microtime(true);

merge_pdfs($pdf_files, $output_name);

echo PHP_EOL . "Done in " . round(microtime(true) - $start, 3) . "s" . PHP_EOL;
echo "Successfully merged PDFs into $output_name" . PHP_EOL;

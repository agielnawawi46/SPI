<?php

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

$docs = Document::whereHas('documentType', function($q) { 
    $q->where('slug', 'monitoring-tl'); 
})->whereIn('status', ['pending_kspi', 'approved'])
  ->whereNull('qr_code_path_auditor')
  ->get();

foreach($docs as $d) { 
    $qrUrl = route('document.verify', $d->uuid); 
    $qrContent = QrCode::format('svg')->size(150)->generate($qrUrl); 
    $qrPath = 'qrcodes_auditor/' . $d->uuid . '.svg'; 
    Storage::disk('public')->put($qrPath, $qrContent); 
    $d->qr_code_path_auditor = $qrPath; 
    $d->save(); 
    echo 'Fixed ' . $d->id . PHP_EOL; 
}
echo "Done\n";

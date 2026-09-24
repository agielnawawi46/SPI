<?php

use App\Models\Document;
use App\Models\Approval;

$docs = Document::whereHas('documentType', function($q) { 
    $q->where('slug', 'monitoring-tl'); 
})->whereIn('status', ['pending_kspi', 'approved'])->get();

foreach($docs as $d) { 
    if(!Approval::where('approvable_id', $d->id)->where('approval_role', 'auditor')->exists()) { 
        Approval::create([
            'approvable_type' => Document::class, 
            'approvable_id' => $d->id, 
            'approver_id' => $d->created_by, 
            'approval_level' => 0, 
            'approval_role' => 'auditor', 
            'action' => 'approve', 
            'comments' => 'Dokumen disusun dan disubmit oleh Auditor.', 
            'acted_at' => $d->created_at
        ]); 
        echo 'Added for ' . $d->id . PHP_EOL; 
    } 
}
echo "Done\n";

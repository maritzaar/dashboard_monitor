<?php
$file = 'e:\TPA\Dashboard\dashboard_monitor\resources\views\monitoring\fuel.blade.php';
$content = file_get_contents($file);

// Extract the Aset (Unit) block
$aset_pattern = '/(<div>\s*<label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Aset \(Unit\)<\/label>.*?<\/div>\s*)/s';
if (preg_match($aset_pattern, $content, $matches)) {
    $aset_block = $matches[0];
    
    // Remove it from its current position
    $content = preg_replace($aset_pattern, '', $content, 1);
    
    // Find the PT block and insert the Aset block after it
    $pt_pattern = '/(<div>\s*<label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">PT<\/label>.*?<\/div>\s*)/s';
    
    $content = preg_replace($pt_pattern, "$1" . $aset_block, $content, 1);
    
    file_put_contents($file, $content);
    echo "Reordered successfully.";
} else {
    echo "Aset block not found.";
}

<?php
$content = file_get_contents('C:\Users\FARREL REVAYA\.gemini\antigravity\brain\466d0a0d-8230-4c48-93c2-7d49f8912ee3\walkthrough.md');
$content .= "\n\n## Perhitungan & Highlight Rasio Solar\n";
$content .= "1. **Menghubungkan Data (Join):** Memperbarui `MonitoringController@fuel` agar menggabungkan data `fuel_transactions` (liter solar) dengan agregasi data harian dari `data_alat` (total jam kerja) berdasarkan Unit, Bulan, dan Tahun.\n";
$content .= "2. **Logika Visual UI:** Mengganti kolom tabel yang sebelumnya strip (`-`) dengan hasil nyata untuk KM/HM dan Rasio. Jika rasionya **> 15 L/Jam**, sistem kini secara otomatis memberikan label warna TPA Orange dengan ikon peringatan agar gampang dideteksi.\n";
file_put_contents('C:\Users\FARREL REVAYA\.gemini\antigravity\brain\466d0a0d-8230-4c48-93c2-7d49f8912ee3\walkthrough.md', $content);

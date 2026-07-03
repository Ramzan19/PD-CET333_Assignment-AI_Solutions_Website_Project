<?php
// Temporary maintenance: check + repair crashed Aria system tables.
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=mysql', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('SET SESSION aria_sort_buffer_size = 268435456');
$pdo->exec('SET SESSION aria_repair_threads = 1');

$tables = ['columns_priv','db','tables_priv','procs_priv','proxies_priv','global_priv','roles_mapping','proc','func','servers','plugin','help_topic','help_category','help_keyword','help_relation','time_zone','time_zone_name','time_zone_transition','time_zone_transition_type','time_zone_leap_second','column_stats','index_stats','table_stats','event'];
$bad = 0;
foreach ($tables as $t) {
    $check = $pdo->query("CHECK TABLE `$t` QUICK")->fetchAll(PDO::FETCH_ASSOC);
    $status = end($check)['Msg_text'] ?? '';
    if (stripos($status, 'OK') !== false) {
        continue; // already healthy
    }
    echo "REPAIRING $t (was: $status)\n";
    $rep = $pdo->query("REPAIR TABLE `$t`")->fetchAll(PDO::FETCH_ASSOC);
    $final = end($rep)['Msg_text'] ?? '';
    echo "  -> $final\n";
    if (stripos($final, 'OK') === false) {
        $bad++;
    }
}
echo $bad === 0 ? "RESULT: all system tables OK\n" : "RESULT: $bad table(s) still need attention\n";

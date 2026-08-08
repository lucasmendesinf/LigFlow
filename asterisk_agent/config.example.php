<?php
return [
    'shared_secret' => 'REPLACE_WITH_64_RANDOM_HEX_CHARS',
    'allowed_ips' => ['127.0.0.1'],
    'asterisk_server_id' => 1,
    'extension_start' => 1000,
    'extension_end' => 1999,
    'state_dir' => '/var/lib/ligflow-asterisk-agent',
    'asterisk_bin' => '/usr/sbin/asterisk',
    'pjsip_conf' => '/etc/asterisk/pjsip.conf',
    'root_include' => '#include "pjsip.d/ligflow.conf"',
    'master_file' => '/etc/asterisk/pjsip.d/ligflow.conf',
    'managed_dir' => '/etc/asterisk/pjsip.d/ligflow',
    // Define este template com os parâmetros PJSIP homologados da sua VPS.
    'endpoint_template' => '',
];

<?php
ips = '103.117.56.0 - 103.117.56.255|103.117.47.17|103.117.57.6-103.117.57.200|103.117.58.12-|-103.117.52.11';
$ips = preg_replace("/\s+/", "", $ips);

$explode = explode('|', $ips);

$ipLists = [];
foreach ($explode as $item) {
    if (strpos($item, '-') !== false) {
        $ipLists[] = explode('-', $item);
    } else {
        $ipLists[] = $item;
    }
}

/**
 * Test
 */
$ipString  = '103.117.52.10';
$currentIp = ip2long($ipString);
$isBlocked = false;
foreach ($ipLists as $ipList) {
    if (! is_array($ipList) && ip2long($ipList) == $currentIp) {
        $isBlocked = true;
        break;
    }

    if (is_array($ipList)) {
        $ipLow  = $ipList[0] !== '' ? ip2long($ipList[0]) : 0;
        $ipHigh = $ipList[1] !== '' ? ip2long($ipList[1]) : 0;

        if (
            ($ipLow && $ipHigh && $currentIp >= $ipLow && $currentIp <= $ipHigh)
            || ($ipLow && $ipHigh == 0 && $ipLow == $currentIp)
            || ($ipLow == 0 && $ipHigh && $ipHigh == $currentIp)
        ) {
            $isBlocked = true;
            break;
        }
    }
}

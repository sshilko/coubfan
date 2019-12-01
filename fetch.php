<?php
/**
 * Coub 'mylikes' downloader
 * @see http://coub.com/dev/docs/Coub+API/Overview
 * @see https://coub.com/dev/docs/Coub+API/Authentication
 * @see https://coub.com/dev/docs/Coub+API/Timelines
 *
 * 1. First register your application under
 * http://coub.com/dev/applications/
 *
 * 2. Obtain temporary access-token (CODE1) by doing GET request like this
 * curl -v http://coub.com/oauth/authorize/?client_id=APP_ID&response_type=code&redirect_uri=http://randomhost.com
 *
 * 3. Obtain actual access-token (CODE2) by doing POST request like this
 * curl -d "grant_type=authorization_code&client_id=APP_ID&redirect_uri=http://randomhost.com&client_secret=APP_SEC&code=CODE1"
 * http://coub.com/oauth/token
 *
 * Example response from Coub API
 * {"access_token":"CODE2HERE","token_type":"bearer","expires_in":31104000,"scope":"logged_in","created_at":1499120437}
 *
 * 4. use CODE2 for further API requests
 */

$requestExit = 0;
$delaySignal = false;
if (function_exists('pcntl_signal')) {
    $signalHandler = function ($n) use (&$requestExit) {
        $requestExit = $n;
    };

    pcntl_signal(SIGTERM, $signalHandler);
    pcntl_signal(SIGINT,  $signalHandler);
    pcntl_signal(SIGHUP,  $signalHandler);
    if (function_exists('pcntl_async_signals')) {
        /**
         * Asynchronously process triggers w/o manual check
         */
        pcntl_async_signals(true);
    } else {
        /**
         * Manually process/check delayed triggers
         */
        $delaySignal = true;
    }
}

if (empty($argv[1]) || empty($argv[2])) {
    echo 'Usage ' . __FILE__ . ' $CHANNEL_ID $ACCESS_TOKEN' . "\n";
    exit;
}


$channelId   = $argv[1];
$accessToken = $argv[2];

$url = 'http://coub.com/api/v2/timeline/channel/' .
        $channelId .
        '?access_token=' . $accessToken .
        '&page=#PAGE&per_page=25';
for ($i=1; $i < PHP_INT_MAX; $i++) {
    if ($requestExit) {
        echo 'Finishing after processing ' . $i . ' pages' . "\n";
        break;
    }

    $rows = [];
    $xurl  = str_replace('#PAGE', $i, $url);

    $data = file_get_contents($xurl);
    $raw  = (array) json_decode($data);

    foreach ($raw['coubs'] as $c) {
        $title = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $c->title);
        $title = preg_replace('/[^\w]/u', '', $c->title);
        if (empty($title)) {
            $title = uniqid('coub-');
        }
        if ($c && !empty($c->file_versions) && !empty($c->file_versions->share) && !empty($c->file_versions->share->default)) {
            $c->id = $c->recoub_to->permalink;
            $rows[$c->id . '_' . str_replace(' ', '_', strtolower($title))] = $c->file_versions->share->default;
        } else {
            echo 'No share for coub.com/view/' . $c->id . "\n";
        }
    }

    foreach ($rows as $rid => $rurl) {
        if ($requestExit) {
            break;
        }

        $file = getcwd() . '/downloads/' . $rid . '.mp4';
        if (!is_readable($file)) {
            $data = file_get_contents($rurl);
            file_put_contents($file, $data);
            echo "Saved " . $file . " from " . basename($rurl) . "\n";
        } else {
            echo "Skipping " . $file . "\n";
        }

        if ($delaySignal) {
            pcntl_signal_dispatch();
        }
    }

    if ($raw['total_pages'] == $i) {
        echo 'Finished on ' . $i . ' page';
        break;
    }
}



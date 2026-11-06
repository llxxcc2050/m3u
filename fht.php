<?php
error_reporting(0);
date_default_timezone_set("PRC");

$id = $_GET['id'] ?? 'fhzw';
$from = $_GET['from'] ?? 'web';

if (preg_match('/(.*?)(?:\?.*|\$.*)/', $id, $matches_id)) {
    $id = $matches_id[1];
}

$n = [
    'fhzw' => 'f7f48462-9b13-485b-8101-7b54716411ec', // 凤凰中文
    'fhzx' => '7c96b084-60e1-40a9-89c5-682b994fb680', // 凤凰资讯
    'fhhk' => '15e02d92-1698-416c-af2f-3e9a872b4d78', // 凤凰香港
];

if (!isset($n[$id])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

$urlp = ($from === 'app') 
    ? "https://m.fengshows.com/api/v3/hub/live/auth-url?live_id={$n[$id]}&live_qa=" 
    : "https://api.fengshows.cn/hub/live/auth-url?live_id={$n[$id]}&live_qa=";

$playseek = $_GET['playseek'] ?? '';
$urle = '';

if ($playseek) {
    $t_arr = explode('-', $playseek);
    $playbackbegin = strtotime($t_arr[0]) * 1000;
    $playbackend = strtotime($t_arr[1]) * 1000;
    $now = time() * 1000;
    
    if ($playbackend > $now) {
        $playbackend = $now;
    }
    
    $ps_time = dechex($playbackbegin);
    $pe_time = dechex($playbackend);
    $urle = "&play_type=replay&ps_time=$ps_time&pe_time=$pe_time";
}

$url = $urlp . 'FHD' . $urle;

#// 注：APP端token每次重新打开APP就失效（故抓包获取token后，token失效前请不要再次打开APP，最长30天失效），web端token固定30天失效
$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI2MjgyYTlmMC1hNTAzLTExZWYtOGQyZi0xZmE4Mjc3N2Q0MjQiLCJuYW1lIjoi5Lil5qC855qE55SY6JSXMDQ3IiwidmlwIjowLCJqdGkiOiJCd2QyZ1ltZTgiLCJpYXQiOjE3OTEzNzQ0OTUsImV4cCI6MTc5Mzk2NjQ5NX0.EkuVnCUrQ7q8HTpezBdCOvQYwSN9ojM3rlHQZXXADgE"; // 请替换为有效的token

$header = [
    'User-Agent: okhttp/3.14.9',
    'token: ' . $token,
];

$data = get($url, $header);

if (strpos($data, 'http') === false) {
    $url = $urlp . 'HD' . $urle;
    $data = file_get_contents($url);
}

$live = json_decode($data)->data->live_url;
header('Location:' . $live);
exit();

function get($url, $header) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
?>





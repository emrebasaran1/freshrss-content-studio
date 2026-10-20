<?php
require __DIR__.'/../xExtension-ContentStudio/Reader.php';
function check(bool $v,string $m):void{if(!$v)throw new RuntimeException($m);}
// The integrated suite can load the real FreshRSS sanitizer; the standalone
// suite tests our own extraction layer before that second sanitizer runs.
if(isset($argv[1]))require $argv[1].'/cli/_cli.php';
else {class FreshRSS_SimplePieCustom{public static function sanitizeHTML($html,$url){return $html;}}}
foreach(['127.0.0.1','10.0.0.1','192.168.1.1','169.254.169.254','::1','fc00::1','::ffff:127.0.0.1','2001:db8::1','100.64.0.1','198.18.0.1'] as $ip)check(!StudioReader::publicIp($ip),'private IP accepted');
check(StudioReader::publicIp('1.1.1.1'),'public IP rejected');
foreach(['http://example.org/a','https://127.0.0.1/a','https://u:p@example.org/a','https://example.org:8443/a','file:///etc/passwd'] as $url){try{StudioReader::validate($url,['example.org']);throw new LogicException('unsafe URL accepted');}catch(RuntimeException $e){check($e->getMessage()==='unsafe_url','unexpected rejection');}}
try{StudioReader::validate('https://untrusted.example/a',['example.org']);throw new LogicException('host accepted');}catch(RuntimeException $e){check($e->getMessage()==='host_not_allowed','allowlist rejected incorrectly');}
check(StudioReader::absolute('https://example.org/a/b','../c')==='https://example.org/c','relative URL');
$html='<html><head><meta property="og:image" content="/cover.jpg"></head><body><article><h1>Heading</h1><p>'.str_repeat('Public article content. ',8).'</p><div class="advertisement">ADVERTISEMENT</div><script>alert(1)</script><img src="/figure.jpg" onerror="alert(1)"><iframe src="https://www.youtube.com/embed/M7lc1UVf-VE"></iframe><iframe src="https://evil.example/embed"></iframe></article></body></html>';
$r=StudioReader::extract($html,'https://example.org/a');check(!str_contains($r['html'],'<script'),'script retained');check(!str_contains($r['html'],'onerror'),'event retained');check(!str_contains($r['html'],'ADVERTISEMENT'),'ad retained');check(str_contains($r['html'],'https://example.org/figure.jpg'),'image lost');check(!str_contains($r['html'],'evil.example'),'untrusted iframe retained');check(str_contains($r['html'],'youtube.com/embed/M7lc1UVf-VE'),'article video lost');
try{StudioReader::extract('<script type="application/ld+json">{"isAccessibleForFree":false}</script>'.$html,'https://example.org/a');throw new LogicException('restricted content accepted');}catch(RuntimeException $e){check($e->getMessage()==='restricted','restricted classification');}
echo "Reader boundary and content checks passed\n";

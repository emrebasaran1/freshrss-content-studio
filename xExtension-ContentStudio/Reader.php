<?php
// SPDX-License-Identifier: GPL-3.0-or-later
declare(strict_types=1);
final class StudioReader {
 public const LIMIT=3145728;
 public static function publicIp(string $ip): bool {
  if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return false;
  if(!str_contains($ip,':')){
   $n=ip2long($ip);foreach([['100.64.0.0',10],['192.0.0.0',24],['192.0.2.0',24],['198.18.0.0',15],['198.51.100.0',24],['203.0.113.0',24]] as [$network,$bits]){if(($n & (-1 << (32-$bits)))===(ip2long($network) & (-1 << (32-$bits))))return false;}
  }
  if(str_contains($ip,':')){ // Accept only global unicast, excluding transition/documentation ranges.
   $packed=inet_pton($ip);if($packed===false||(ord($packed[0])&0xe0)!==0x20)return false;
   $hex=bin2hex($packed);if(str_starts_with($hex,'20010db8')||str_starts_with($hex,'20010000')||str_starts_with($hex,'2002'))return false;
  }
  return true;
 }
 public static function validate(string $url,array $hosts): array {
  $u=parse_url($url);$host=strtolower($u['host']??'');
  if(($u['scheme']??'')!=='https'||$host===''||isset($u['user'])||isset($u['pass'])||isset($u['port'])&&$u['port']!==443||str_contains($url,"\r")||str_contains($url,"\n")||filter_var(trim($host,'[]'),FILTER_VALIDATE_IP))throw new RuntimeException('unsafe_url');
  $hosts=array_map('strtolower',$hosts);if(!in_array($host,$hosts,true))throw new RuntimeException('host_not_allowed');
  $records=dns_get_record($host,DNS_A|DNS_AAAA);$ips=[];
  foreach($records?:[] as $record){$ip=$record['ip']??$record['ipv6']??'';if($ip!==''){if(!self::publicIp($ip))throw new RuntimeException('unsafe_url');$ips[]=$ip;}}
  if(!$ips)throw new RuntimeException('fetch_failed');return [$host,$ips[0]];
 }
 public static function absolute(string $base,string $ref): string {
  $ref=trim(html_entity_decode($ref,ENT_QUOTES,'UTF-8'));if($ref===''||str_starts_with($ref,'#'))return $ref;
  if(preg_match('~^[a-z][a-z0-9+.-]*:~i',$ref))return $ref;
  $u=parse_url($base);$origin=($u['scheme']??'https').'://'.($u['host']??'');
  if(str_starts_with($ref,'//'))return ($u['scheme']??'https').':'.$ref;
  if(str_starts_with($ref,'?'))return $origin.($u['path']??'/').$ref;
  $path=str_starts_with($ref,'/')?$ref:dirname($u['path']??'/').'/'.$ref;
  $parts=[];foreach(explode('/',$path) as $part){if($part==='..')array_pop($parts);elseif($part!==''&&$part!=='.')$parts[]=$part;}
  return $origin.'/'.implode('/',$parts);
 }
 public static function fetch(string $url,array $hosts): array {
  $deadline=microtime(true)+16;
  for($hop=0;$hop<4;$hop++){
   [$host,$ip]=self::validate($url,$hosts);$body='';$location='';$oversize=false;
   $remaining=(int)ceil($deadline-microtime(true));if($remaining<1)throw new RuntimeException('fetch_failed');
   $curl=curl_init($url);$resolveIp=str_contains($ip,':')?'['.$ip.']':$ip;
   curl_setopt_array($curl,[CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROXY=>'',CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_CONNECTTIMEOUT=>min(5,$remaining),CURLOPT_TIMEOUT=>$remaining,CURLOPT_RESOLVE=>[$host.':443:'.$resolveIp],CURLOPT_USERAGENT=>'ContentStudio/0.1 public-reader',CURLOPT_ENCODING=>'',CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
    CURLOPT_HEADERFUNCTION=>static function($ch,$line)use(&$location){if(stripos($line,'Location:')===0)$location=trim(substr($line,9));return strlen($line);},
    CURLOPT_WRITEFUNCTION=>static function($ch,$chunk)use(&$body,&$oversize){if(strlen($body)+strlen($chunk)>self::LIMIT){$oversize=true;return 0;}$body.=$chunk;return strlen($chunk);}]);
   $ok=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);$type=(string)curl_getinfo($curl,CURLINFO_CONTENT_TYPE);curl_close($curl);
   if($oversize)throw new RuntimeException('too_large');if($ok===false)throw new RuntimeException('fetch_failed');
   if($status>=300&&$status<400&&$location!==''){$url=self::absolute($url,$location);continue;}
   if($status!==200||!preg_match('~^(?:text/html|application/xhtml\+xml)\b~i',$type))throw new RuntimeException('fetch_failed');
   return ['html'=>$body,'url'=>$url];
  }throw new RuntimeException('fetch_failed');
 }
 public static function extract(string $html,string $url,string $xpath='//article | //main'): array {
  $doc=new DOMDocument();@$doc->loadHTML("\xEF\xBB\xBF".$html,LIBXML_NONET|LIBXML_NOERROR|LIBXML_NOWARNING);$x=new DOMXPath($doc);
  foreach($x->query('//script[@type="application/ld+json"]')?:[] as $script){if(preg_match('/"isAccessibleForFree"\s*:\s*(?:false|"false")/i',$script->textContent))throw new RuntimeException('restricted');}
  if($x->query('//*[contains(concat(" ",normalize-space(@class)," ")," paywall ") or contains(concat(" ",normalize-space(@class)," ")," subscriber-only ")]')->length)throw new RuntimeException('restricted');
  $image='';foreach($x->query('//meta[@property="og:image" or @name="twitter:image"]')?:[] as $node){$image=self::absolute($url,$node->getAttribute('content'));break;}
  $nodes=@$x->query($xpath);if($nodes===false)throw new RuntimeException('no_content');$best=null;$length=0;
  foreach($nodes as $node){$size=mb_strlen(trim($node->textContent));if($size>$length){$length=$size;$best=$node;}}
  if(!$best||$length<80)throw new RuntimeException('no_content');
  $outDoc=new DOMDocument();$outDoc->appendChild($outDoc->importNode($best,true));$clean=new DOMXPath($outDoc);
  foreach($clean->query('//script|//style|//noscript|//form|//input|//button|//nav|//header|//footer|//audio|//track|//object|//embed|//svg|//h1|//comment()')?:[] as $node)$node->parentNode?->removeChild($node);
  $seen=[];
  foreach(iterator_to_array($clean->query('//*')) as $node){
   if(!$node instanceof DOMElement)continue;
   $class=strtolower($node->getAttribute('class').' '.$node->getAttribute('id'));
   if(preg_match('/(?:^|[\s_-])(advertisement|advert|promo|newsletter|subscribe|related|social-share)(?:$|[\s_-])/',$class)||preg_match('/^(?:REKLAM|ADVERTISEMENT)$/iu',trim($node->textContent))){$node->parentNode?->removeChild($node);continue;}
   foreach(iterator_to_array($node->attributes) as $a)if(str_starts_with(strtolower($a->name),'on')||in_array(strtolower($a->name),['style','srcdoc','formaction'],true))$node->removeAttribute($a->name);
   if($node->tagName==='img'){
    $src=$node->getAttribute('data-src')?:$node->getAttribute('data-lazy-src')?:$node->getAttribute('src');
    if($src===''||str_starts_with($src,'data:')){
     $set=$node->getAttribute('data-srcset')?:$node->getAttribute('srcset');$candidates=explode(',',$set);$last=trim((string)end($candidates));$src=preg_split('/\s+/',$last)[0]??'';
    }
    $src=self::absolute($url,$src);$key=preg_replace('/[?].*/','',$src);
    if($src===''||isset($seen[$key])){$node->parentNode?->removeChild($node);continue;}$seen[$key]=true;$node->setAttribute('src',$src);$node->removeAttribute('srcset');$node->setAttribute('loading','lazy');
   }
   foreach(['src','href','poster'] as $attr)if($node->hasAttribute($attr)){
    $value=self::absolute($url,$node->getAttribute($attr));if(!preg_match('~^https?://~i',$value))$node->removeAttribute($attr);else $node->setAttribute($attr,$value);
   }
   if($node->tagName==='iframe'){
    $src=$node->getAttribute('src')?:self::absolute($url,$node->getAttribute('data-src'));
    if(!preg_match('~^https://(?:www\.)?youtube(?:-nocookie)?\.com/embed/[A-Za-z0-9_-]{11}(?:[?].*)?$|^https://player\.vimeo\.com/video/[0-9]+(?:[?].*)?$~',$src)){$node->parentNode?->removeChild($node);continue;}
    $node->setAttribute('src',$src);$node->setAttribute('referrerpolicy','strict-origin-when-cross-origin');$node->setAttribute('allowfullscreen','');
   }
  }
  // FreshRSS' sanitizer provides the final element/attribute allowlist.
  $body=FreshRSS_SimplePieCustom::sanitizeHTML($outDoc->saveHTML()?:'',$url);
  return ['html'=>$body,'image'=>preg_match('~^https://~',$image)?$image:'','notice'=>''];
 }
}

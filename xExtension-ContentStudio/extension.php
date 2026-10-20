<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// Standalone distribution, 2026-10-20. No dependency on a personal host.
declare(strict_types=1);
require_once __DIR__ . '/Reader.php';
final class ContentStudioExtension extends Minz_Extension {
 public static ?self $instance = null;
 public function init(): void {
  parent::init(); self::$instance=$this;
  $this->registerController('contentStudio');
  foreach (['themes.css','studio.css'] as $file) Minz_View::appendStyle($this->getFileUrl($file).'&v='.filemtime(__DIR__.'/static/'.$file));
  foreach (['core.js','studio.js'] as $file) Minz_View::appendScript($this->getFileUrl($file).'&v='.filemtime(__DIR__.'/static/'.$file));
  $this->registerHook('js_vars',[$this,'variables']);
  $this->registerHook('entry_before_display',[$this,'entry']);
 }
 public function config(): array {
  $defaults=['language'=>'auto','news'=>0,'youtube'=>0,'newsletters'=>0,'blogs'=>0,'linkding'=>'','metube'=>'','full_text'=>false,'deduplicate'=>true,'threshold'=>0.62,'blocked'=>'','interested'=>'','reader_hosts'=>'','article_xpath'=>'//article | //main','hide_podcasts'=>false];
  foreach ($defaults as $key=>$value) $defaults[$key]=$this->getUserConfigurationValue($key,$value);
  return $defaults;
 }
 public static function messages(string $lang): array {
  if (!in_array($lang,['en','tr','de','fr','es','zh'],true)) $lang='en';
  return json_decode((string)file_get_contents(__DIR__.'/i18n/'.$lang.'.json'),true)??[];
 }
 public function language(): string {
  $lang=(string)$this->config()['language'];
  if($lang==='auto') $lang=substr((string)(FreshRSS_Context::userConf()->language??'en'),0,2);
  return in_array($lang,['en','tr','de','fr','es','zh'],true)?$lang:'en';
 }
 public function variables(array $vars): array {
  $c=$this->config();$vars['contentStudio']=['language'=>$this->language(),'messages'=>self::messages($this->language()),'categories'=>array_intersect_key($c,array_flip(['news','youtube','newsletters','blogs'])),'linkding'=>$c['linkding'],'metube'=>$c['metube'],'fullText'=>(bool)$c['full_text'],'deduplicate'=>(bool)$c['deduplicate'],'threshold'=>(float)$c['threshold'],'blocked'=>array_values(array_filter(array_map('trim',explode(',',(string)$c['blocked'])))),'interested'=>array_values(array_filter(array_map('trim',explode(',',(string)$c['interested'])))),'hidePodcasts'=>(bool)$c['hide_podcasts']];return $vars;
 }
 public function entry(FreshRSS_Entry $entry): FreshRSS_Entry {
  $c=$this->config();$kind=array_search($entry->feed()->categoryId(),array_map('intval',array_intersect_key($c,array_flip(['news','youtube','newsletters','blogs']))),true);
  if($kind!==false){$data=['id'=>$entry->id(),'kind'=>$kind,'tags'=>$entry->tags(),'cover'=>$entry->attributeArray('thumbnail')['url']??''];
   $entry->_content($entry->content(false).'<span hidden data-studio-entry="'.htmlspecialchars(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE),ENT_QUOTES,'UTF-8').'"></span>');}
  return $entry;
 }
 public function handleConfigureAction(): void {
  if(!Minz_Request::isPost())return;
  if(!hash_equals(FreshRSS_Auth::csrfToken(),Minz_Request::paramString('_csrf')))throw new RuntimeException('Invalid CSRF token');
  $c=$this->config();foreach(['news','youtube','newsletters','blogs'] as $k)$c[$k]=max(0,(int)(Minz_Request::paramString($k,true)?:'0'));
  $ids=array_filter(array_map('intval',array_intersect_key($c,array_flip(['news','youtube','newsletters','blogs']))));
  if(count($ids)!==count(array_unique($ids)))throw new RuntimeException('Choose a different category for each stream.');
  $lang=(Minz_Request::paramString('language')?:'auto');$c['language']=in_array($lang,['auto','en','tr','de','fr','es','zh'],true)?$lang:'en';
  $link=trim(Minz_Request::paramString('linkding',true));if($link!==''&&!preg_match('~^https?://[^\s]+$~i',$link))throw new RuntimeException('Invalid bookmark URL');$c['linkding']=$link;
  $download=trim(Minz_Request::paramString('metube',true));if($download!==''&&!preg_match('~^https?://[^\\s]+$~i',$download))throw new RuntimeException('Invalid MeTube URL');$c['metube']=$download;
  foreach(['blocked','interested','reader_hosts','article_xpath'] as $k)$c[$k]=mb_substr(Minz_Request::paramString($k,true),0,4000);
  foreach(['full_text','deduplicate','hide_podcasts'] as $k)$c[$k]=Minz_Request::paramBoolean($k);
  $c['threshold']=min(0.95,max(0.4,(float)(Minz_Request::paramString('threshold')?:'0.62')));
  foreach($c as $key=>$value)$this->setUserConfigurationValue($key,$value);
 }
}

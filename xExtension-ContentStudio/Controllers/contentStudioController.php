<?php
// SPDX-License-Identifier: GPL-3.0-or-later
declare(strict_types=1);
final class FreshExtension_contentStudio_Controller extends FreshRSS_ActionController {
 public function readerAction(): void {
  header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
  if(!FreshRSS_Auth::hasAccess()||($_SERVER['REQUEST_METHOD']??'')!=='POST'||!hash_equals(FreshRSS_Auth::csrfToken(),Minz_Request::paramString('_csrf'))){http_response_code(403);echo '{}';exit;}
  $extension=ContentStudioExtension::$instance;
  if(!$extension||!$extension->config()['full_text']){http_response_code(403);echo json_encode(['error'=>'reader_disabled']);exit;}
  try{
   $entry=FreshRSS_Factory::createEntryDao()->searchById(Minz_Request::paramString('id'));
   if(!$entry)throw new RuntimeException('not_found');
   $config=$extension->config();$allowed=array_map('intval',[$config['news'],$config['newsletters'],$config['blogs']]);
   if(!in_array($entry->feed()->categoryId(),array_filter($allowed),true))throw new RuntimeException('not_found');
   $cache=$entry->attributeArray('content_studio_reader');
   if(($cache['time']??0)>time()-86400&&($cache['url']??'')===$entry->link())$result=$cache['result'];
   else{
    $url=html_entity_decode($entry->link());$hosts=array_filter(array_map('trim',explode(',',(string)$config['reader_hosts'])));
    $page=StudioReader::fetch($url,$hosts);$xpath=(string)$config['article_xpath'];
    if(str_starts_with(trim($xpath),'{')){$map=json_decode($xpath,true);$xpath=is_array($map)?($map[parse_url($page['url'],PHP_URL_HOST)]??$map['default']??'//article | //main'):'//article | //main';}
    $result=StudioReader::extract($page['html'],$page['url'],$xpath);
    $entry->_attribute('content_studio_reader',['time'=>time(),'url'=>$entry->link(),'result'=>$result]);$entry->_lastModified(time());$values=$entry->toArray();unset($values['is_read'],$values['is_favorite'],$values['lastUserModified']);FreshRSS_Factory::createEntryDao()->updateEntry($values);
   }
   echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
  }catch(Throwable $e){http_response_code(422);echo json_encode(['error'=>in_array($e->getMessage(),['not_found','restricted','host_not_allowed','no_content','too_large','fetch_failed','unsafe_url'],true)?$e->getMessage():'fetch_failed']);}exit;
 }
}

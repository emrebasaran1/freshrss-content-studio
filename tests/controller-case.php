<?php
// Isolated request-boundary test harness. No FreshRSS instance or network needed.
$case=$argv[1]??'valid';
class FreshRSS_ActionController{}
class FreshRSS_Auth{public static function hasAccess(){global $case;return $case!=='unauthorized';}public static function csrfToken(){return 'fixture-csrf';}}
class Minz_Request{public static function paramString($key){global $case;return $key==='_csrf'?($case==='csrf'?'invalid':'fixture-csrf'):'fixture-entry';}}
class ContentStudioExtension{public static $instance;public function config(){global $case;return ['full_text'=>$case!=='disabled','news'=>1,'newsletters'=>2,'blogs'=>3];}}
class EntryFixture{public function feed(){return $this;}public function categoryId(){global $case;return $case==='wrong-category'?4:1;}public function link(){return 'https://example.org/';}public function attributeArray($key){return ['time'=>time(),'url'=>$this->link(),'result'=>['html'=>'<p>Cached public text</p>','image'=>'','notice'=>'']];}}
class DaoFixture{public function searchById($id){return new EntryFixture();}}
class FreshRSS_Factory{public static function createEntryDao(){return new DaoFixture();}}
ContentStudioExtension::$instance=new ContentStudioExtension();
$_SERVER['REQUEST_METHOD']=$case==='get'?'GET':'POST';
require __DIR__.'/../xExtension-ContentStudio/Controllers/contentStudioController.php';
(new FreshExtension_contentStudio_Controller())->readerAction();

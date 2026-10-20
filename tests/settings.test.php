<?php
declare(strict_types=1);
class Minz_Extension{
 public array $values=[];
 public function getUserConfigurationValue($k,$fallback){return $this->values[$k]??$fallback;}
 protected function setUserConfigurationValue($k,$value){$this->values[$k]=$value;}
}
class FreshRSS_Auth{public static function csrfToken(){return 'fixture-csrf';}}
class FreshRSS_Context{public static function userConf(){return (object)['language'=>'zh-cn'];}}
class Minz_Request{
 public static array $params=[];
 public static function isPost(){return true;}
 public static function paramString(string $k,bool $plaintext=false):string{$s=(string)(self::$params[$k]??'');return $plaintext?$s:htmlspecialchars($s,ENT_COMPAT,'UTF-8');}
 public static function paramBoolean(string $k):bool{return !empty(self::$params[$k]);}
}
require __DIR__.'/../xExtension-ContentStudio/extension.php';
$e=new ContentStudioExtension();
foreach(['en','tr','de','fr','es','zh'] as $language){
 Minz_Request::$params=['_csrf'=>'fixture-csrf','language'=>$language,'news'=>'21','youtube'=>'35','newsletters'=>'44','blogs'=>'56','article_xpath'=>'{"example.org":"//div[@id=\"article\"]"}','linkding'=>'https://example.org/new?a=1&b=2','threshold'=>'0.62'];
 $e->handleConfigureAction();if($e->language()!==$language)throw new RuntimeException('Language save failed');
 if($e->config()['article_xpath']!==Minz_Request::$params['article_xpath'])throw new RuntimeException('XPath JSON was escaped');
 if($e->config()['linkding']!==Minz_Request::$params['linkding'])throw new RuntimeException('URL was escaped');
 if(!isset(ContentStudioExtension::messages($language)['download']))throw new RuntimeException('Translation missing');
}
$e->values['language']='auto';if($e->language()!=='zh')throw new RuntimeException('Chinese auto language failed');
Minz_Request::$params=['_csrf'=>'fixture-csrf','news'=>'21','blogs'=>'21'];
try{$e->handleConfigureAction();throw new LogicException('Duplicate category accepted');}catch(RuntimeException $ex){if(!str_contains($ex->getMessage(),'different category'))throw $ex;}
echo "Six-language save, raw XPath/URL and category checks passed\n";

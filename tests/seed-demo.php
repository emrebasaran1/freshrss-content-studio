<?php
// Disposable FreshRSS test installations only. This script writes synthetic data.
// Usage: php seed-demo.php /path/to/FreshRSS
require ($argv[1]??'/var/www/FreshRSS').'/cli/_cli.php';
cliInitUser('demo');
$categoryDao=FreshRSS_Factory::createCategoryDao();$feedDao=FreshRSS_Factory::createFeedDao();$entryDao=FreshRSS_Factory::createEntryDao();$mapping=[];
foreach(['news'=>'News','youtube'=>'YouTube','newsletters'=>'Newsletters','blogs'=>'Blogs'] as $key=>$name){
 $id=$categoryDao->addCategory(['name'=>$name]);$mapping[$key]=$id;
 $feed=new FreshRSS_Feed('https://example.org/'.$key.'/feed.xml',false);$feed->_name($name.' Sample');$feed->_categoryId($id);$feedId=$feedDao->addFeedObject($feed);
 $titles=$key==='news'?['Research team releases new battery study','New battery study released by research team','City garden opens with community workshops']:[$name.': a quiet place for focused reading',$name.': practical ideas for everyday learning'];
 foreach($titles as $n=>$title){
  $html='<p>This is synthetic demonstration content. It contains no personal feeds or server information. Read at your own pace, without recommendation algorithms.</p><h2>A useful comparison</h2><p>Small improvements add up. A deliberate reading routine can make a busy information stream easier to explore.</p><table><tr><th>Approach</th><th>Purpose</th></tr><tr><td>Focused</td><td>Choose what to read next</td></tr></table>';
  $url=$key==='youtube'?'https://www.youtube.com/watch?v=M7lc1UVf-VE':'https://example.org/'.$key.'/article-'.$n;
  $entry=new FreshRSS_Entry($feedId,'demo-'.$key.'-'.$n,$title,'Demo Author',$html,$url,time()-$n*60,false,false,['Learning']);
  $entry->_id((string)((int)(microtime(true)*1000000)+$n));$entryDao->addEntry($entry->toArray(),false);
 }
}
$feedDao->updateCachedValues();$conf=FreshRSS_Context::userConf();$conf->extensions_enabled=['Content Studio'=>true];$conf->extensions=['Content Studio'=>$mapping+['language'=>'en','deduplicate'=>true,'full_text'=>false]];$conf->view_mode='normal';$conf->save();
echo json_encode($mapping)."\n";

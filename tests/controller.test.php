<?php
foreach(['valid'=>['html'=>'<p>Cached public text</p>','image'=>'','notice'=>''],'unauthorized'=>new stdClass(),'csrf'=>new stdClass(),'get'=>new stdClass(),'disabled'=>['error'=>'reader_disabled'],'wrong-category'=>['error'=>'not_found']] as $case=>$expected){
 $cmd=escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/controller-case.php').' '.escapeshellarg($case);
 $output=shell_exec($cmd);$actual=json_decode($output??'');if(json_encode($actual)!==json_encode($expected))throw new RuntimeException('Controller case failed: '.$case);
}
echo "6 controller request-boundary checks passed\n";

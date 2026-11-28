<?php
namespace Modules\AiReception\Native;
/** Conservative extraction of explicit preferences from tenant-owned catalogues. No booking authority. */
final class RequestSelection {
 public static function extract(string $text,array $services,array $professionals,string $today):array {
  $text=mb_strtolower($text,'UTF-8');if(preg_match('/\b(no|sin|excepto|ni)\b/u',$text))return [];$changes=[];
  foreach(['service_id'=>$services,'professional_id'=>$professionals] as $field=>$rows){$matched=[];foreach($rows as $row){$row=(array)$row;$name=mb_strtolower($row['name'],'UTF-8');if(preg_match('/(?<!\pL)'.preg_quote($name,'/').'(?!\pL)/u',$text))$matched[]=(int)$row['id'];}if(count($matched)===1)$changes[$field]=$matched[0];}
  $date=new \DateTimeImmutable($today);if(preg_match('/\b(\d{4}-\d{2}-\d{2})\b/u',$text,$m)){[$y,$mo,$d]=array_map('intval',explode('-',$m[1]));if(checkdate($mo,$d,$y))$changes['day']=$m[1];}
  elseif(strpos($text,'pasado mañana')!==false)$changes['day']=$date->modify('+2 days')->format('Y-m-d');elseif(strpos($text,'mañana')!==false)$changes['day']=$date->modify('+1 day')->format('Y-m-d');
  else foreach(['lunes'=>1,'martes'=>2,'miércoles'=>3,'miercoles'=>3,'jueves'=>4,'viernes'=>5,'sábado'=>6,'sabado'=>6,'domingo'=>7] as $word=>$day)if(preg_match('/\b'.$word.'\b/u',$text)){$delta=($day-(int)$date->format('N')+7)%7;$changes['day']=$date->modify('+'.$delta.' days')->format('Y-m-d');break;}
  if(preg_match('/\b([01]?\d|2[0-3]):([0-5]\d)\b/u',$text,$m))$changes['start']=str_pad($m[1],2,'0',STR_PAD_LEFT).':'.$m[2];return $changes;
 }
}

<?php
namespace Modules\AiReception\Native;
use Modules\AiReception\Contracts\DomainCalendar;
/** Domain modules register bindings; core does not import or enumerate their models. */
final class DomainRegistry
{
    public function calendars():array {
        $result=[];
        foreach(app()->getBindings() as $binding=>$definition){
            if(!preg_match('/^aireception\.domain\.([a-z][a-z0-9_]{0,29})$/D',$binding,$m))continue;
            $adapter=app($binding);
            if(!$adapter instanceof DomainCalendar)throw new \LogicException('Domain calendar contract required.');
            $result[$m[1]]=$adapter;
        }
        return $result;
    }
    public function capabilities():array {
        $a=app(Access::class);$a->require('aireception','calendar');$db=app('db')->connection('tenant');$business=$a->business();
        $domains=$db->table('aireception_professional_links as l')->join('aireception_professionals as p','p.id','=','l.professional_id')->where('p.business_id',$business)->where('p.active',true)->pluck('l.domain')->unique()->all();
        return ['business_information'=>true,'service_catalog'=>true,'availability'=>true,'booking'=>$a->allows('aireception','book'),'human_handoff'=>$a->allows('aireception','conversations'),'domain_calendars'=>array_values(array_intersect($domains,array_keys($this->calendars()))),'external_providers'=>false];
    }
}

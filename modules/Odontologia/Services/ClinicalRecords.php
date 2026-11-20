<?php
namespace Modules\Odontologia\Services;
/** Deliberately separate from reception tools. Encryption at rest with Laravel APP_KEY. */
final class ClinicalRecords
{
    private function db(){return app('db')->connection('tenant');}
    private function authorize(int $person): void {
        app(\Modules\AiReception\Native\Access::class)->require('odontologia','clinical');
        if(!config('odontologia.clinical_records_enabled',false))abort(403,'Registros clinicos desactivados.');
        if(!$this->db()->table('persons')->where('id',$person)->where('type','customers')->exists())abort(404);
    }
    private function audit(int $person,?int $id,string $action,?int $version=null,?string $snapshot=null):void {
        $this->db()->table('odontologia_clinical_audit')->insert(['person_id'=>$person,'record_id'=>$id,'actor_id'=>auth('web')->id(),'action'=>$action,'version'=>$version,'encrypted_snapshot'=>$snapshot,'created_at'=>date('Y-m-d H:i:s')]);
    }
    public function list(int $person):array {
        $this->authorize($person);
        return $this->db()->transaction(function()use($person){
            $rows=$this->db()->table('odontologia_clinical_records')->where('person_id',$person)->orderBy('id')->get();
            $this->audit($person,null,'view_history');$result=[];
            foreach($rows as $row){$data=(array)$row;$data['content']=json_decode(app('encrypter')->decrypt($row->encrypted_content),true);unset($data['encrypted_content']);$result[]=$data;}
            return $result;
        });
    }
    public function save(int $person,string $kind,array $content,?int $id=null,?int $version=null):int {
        $this->authorize($person);
        if(!in_array($kind,['history','allergies','evolution','treatment_plan','consent','odontogram'],true))abort(422);
        if($kind==='odontogram'){
            if(array_keys($content)!==['teeth'] || !is_array($content['teeth']) || count($content['teeth'])>160)abort(422);
            $seen=[];
            foreach($content['teeth'] as $tooth){
                if(!is_array($tooth) || count($tooth)!==3 || !preg_match('/^(?:[1-4][1-8]|[5-8][1-5])$/D',(string)($tooth['tooth']??'')) || !in_array($tooth['surface']??'', ['whole','mesial','distal','buccal','lingual','occlusal'],true) || !is_string($tooth['note']??null) || strlen($tooth['note'])>1000)abort(422);
                $key=$tooth['tooth'].':'.$tooth['surface'];if(isset($seen[$key]))abort(422);$seen[$key]=true;
            }
        }elseif(array_keys($content)!==['note'] || !is_string($content['note']) || trim($content['note'])==='' || strlen($content['note'])>12000)abort(422);
        $encrypted=app('encrypter')->encrypt(json_encode($content));
        return $this->db()->transaction(function()use($person,$kind,$encrypted,$id,$version){
            $now=date('Y-m-d H:i:s');$next=1;
            if($id){
                $old=$this->db()->table('odontologia_clinical_records')->where('person_id',$person)->where('id',$id)->lockForUpdate()->first();
                if(!$old)abort(404);if((int)$old->version!==$version || $old->kind!==$kind)abort(409,'Registro modificado; actualiza antes de guardar.');
                $this->audit($person,$id,'previous_version',(int)$old->version,$old->encrypted_content);$next=$version+1;
                $this->db()->table('odontologia_clinical_records')->where('id',$id)->update(['encrypted_content'=>$encrypted,'version'=>$next,'author_id'=>auth('web')->id(),'updated_at'=>$now]);
            }else{$id=$this->db()->table('odontologia_clinical_records')->insertGetId(['person_id'=>$person,'kind'=>$kind,'encrypted_content'=>$encrypted,'version'=>1,'author_id'=>auth('web')->id(),'created_at'=>$now,'updated_at'=>$now]);}
            $this->audit($person,$id,'save',$next);return (int)$id;
        },3);
    }
}

<?php
namespace Modules\AiReception\Native;
/** Human confirmation boundary. Providers cannot obtain credentials or execute these HTTP methods. */
final class BookingProposals
{
    public function prepare(array $input):array {
        app(Access::class)->require('aireception','book');
        $v=app('validator')->make($input,['person_id'=>'required|integer|min:1','professional_id'=>'required|integer|min:1','service_id'=>'required|integer|min:1','day'=>'required|date_format:Y-m-d','start'=>'required|date_format:H:i'])->validate();
        if(array_diff(array_keys($input),array_keys($v)))abort(422,'Parametros no autorizados.');
        $db=app('db')->connection('tenant');$business=app(Access::class)->business();$actor=(int)auth('web')->id();
        $person=$db->table('persons')->where('type','customers')->where('id',$v['person_id'])->first();if(!$person)abort(404);
        $engine=app(EngineFactory::class)->make('aireception');$slots=$engine->availability($actor,$business,(int)$v['professional_id'],(int)$v['service_id'],$v['day']);
        if(!count(array_filter($slots,function($s)use($v){return $s['start']===$v['start'];})))abort(409,'Horario no disponible.');
        $v['client_name']=$person->name;$v['status']='confirmed';$token=bin2hex(random_bytes(32));$expires=date('Y-m-d H:i:s',time()+300);
        $db->table('aireception_booking_drafts')->insert(['business_id'=>$business,'actor_id'=>$actor,'token_hash'=>hash('sha256',$token),'payload'=>json_encode($v),'expires_at'=>$expires,'created_at'=>date('Y-m-d H:i:s')]);
        return ['confirmation_token'=>$token,'expires_at'=>$expires,'proposal'=>$v,'confirmed'=>false];
    }
    public function confirm(string $token,bool $explicit):array {
        app(Access::class)->require('aireception','book');
        if(!$explicit || !preg_match('/^[a-f0-9]{64}$/D',$token))abort(422,'Confirmacion explicita requerida.');
        $db=app('db')->connection('tenant');$business=app(Access::class)->business();$actor=(int)auth('web')->id();$engine=app(EngineFactory::class)->make('aireception');
        return $db->transaction(function()use($db,$business,$actor,$engine,$token){
            $engine->authorize($actor,$business,true,true);
            $draft=$db->table('aireception_booking_drafts')->where('business_id',$business)->where('actor_id',$actor)->where('token_hash',hash('sha256',$token))->lockForUpdate()->first();
            if(!$draft)abort(404);
            if($draft->result)return json_decode($draft->result,true)+['replayed'=>true];
            if($draft->expires_at<=date('Y-m-d H:i:s'))abort(409,'Propuesta vencida; consulta disponibilidad otra vez.');
            $result=$engine->execute($actor,$business,'book',json_decode($draft->payload,true),'proposal-'.$draft->id);
            app(ReminderSchedule::class)->sync($result['booking']);
            $db->table('aireception_booking_drafts')->where('id',$draft->id)->update(['result'=>json_encode(['booking'=>$result['booking']])]);
            return $result;
        },3);
    }
}

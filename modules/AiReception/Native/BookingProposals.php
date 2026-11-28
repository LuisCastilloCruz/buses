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
    public function prepareChange(string $action,int $person,array $input):array {
        app(Access::class)->require('aireception','book');
        if(!in_array($action,['cancel','reschedule'],true))abort(422);
        $rules=['booking_id'=>'required|integer|min:1'];if($action==='reschedule')$rules+=['day'=>'required|date_format:Y-m-d','start'=>'required|date_format:H:i'];
        $v=app('validator')->make($input,$rules)->validate();if(array_diff(array_keys($input),array_keys($v)))abort(422);
        $business=app(Access::class)->business();$actor=(int)auth('web')->id();$db=app('db')->connection('tenant');
        $b=$db->table('aireception_bookings')->where('id',$v['booking_id'])->where('business_id',$business)->where('person_id',$person)->first();if(!$b)abort(404);
        if(in_array($b->status,['cancelled','attended'],true))abort(409);
        if($action==='reschedule'){$slots=app(EngineFactory::class)->make('aireception')->reschedulingAvailability($actor,$business,(int)$b->id,$v['day']);if(!count(array_filter($slots,function($s)use($v){return $s['start']===$v['start'];})))abort(409,'Horario no disponible.');}
        $v['version']=(int)$b->version;$v['_action']=$action;$v['_person_id']=$person;
        $token=bin2hex(random_bytes(32));$expires=date('Y-m-d H:i:s',time()+300);
        $db->table('aireception_booking_drafts')->insert(['business_id'=>$business,'actor_id'=>$actor,'token_hash'=>hash('sha256',$token),'payload'=>json_encode($v),'expires_at'=>$expires,'created_at'=>date('Y-m-d H:i:s')]);
        return ['confirmation_token'=>$token,'expires_at'=>$expires,'proposal'=>$v,'confirmed'=>false,'action'=>$action];
    }
    public function confirm(string $token,bool $explicit,?callable $contextGuard=null):array {
        app(Access::class)->require('aireception','book');
        if(!$explicit || !preg_match('/^[a-f0-9]{64}$/D',$token))abort(422,'Confirmacion explicita requerida.');
        $db=app('db')->connection('tenant');$business=app(Access::class)->business();$actor=(int)auth('web')->id();$engine=app(EngineFactory::class)->make('aireception');
        return $db->transaction(function()use($db,$business,$actor,$engine,$token,$contextGuard){
            $engine->authorize($actor,$business,true,true);
            if($contextGuard)$contextGuard();
            $draft=$db->table('aireception_booking_drafts')->where('business_id',$business)->where('actor_id',$actor)->where('token_hash',hash('sha256',$token))->lockForUpdate()->first();
            if(!$draft)abort(404);
            if($draft->conversation_id && !$draft->result){$conversation=$db->table('aireception_conversations')->where('id',$draft->conversation_id)->lockForUpdate()->first();if(!$conversation||$conversation->status!=='automated')abort(409,'Conversacion pausada.');$state=app(DialogueSession::class)->load((int)$conversation->id,(int)$conversation->person_id);if(($state['pending']['token_hash']??null)!==hash('sha256',$token))abort(409,'La propuesta ya no corresponde a la solicitud actual.');}
            if($draft->result){$previous=json_decode($draft->result,true);$current=$db->table('aireception_bookings')->where('business_id',$business)->where('id',$previous['booking']['id'])->first();if(!$current || (int)$current->version!==(int)$previous['booking']['version'])abort(409,'La reserva ya cambio; consulta el calendario.');return $previous+['replayed'=>true];}
            if($draft->expires_at<=date('Y-m-d H:i:s'))abort(409,'Propuesta vencida; consulta disponibilidad otra vez.');
            $payload=json_decode($draft->payload,true);$action=$payload['_action']??'book';
            if($action!=='book'){$owned=$db->table('aireception_bookings')->where('id',$payload['booking_id'])->where('business_id',$business)->where('person_id',$payload['_person_id'])->exists();if(!$owned)abort(404);}
            unset($payload['_action'],$payload['_person_id']);
            $result=$engine->execute($actor,$business,$action,$payload,'proposal-'.$draft->id);
            if($action==='book'){$db->table('aireception_bookings')->where('id',$result['booking']['id'])->update(['origin'=>'assisted']);$result['booking']['origin']='assisted';}
            $db->table('aireception_events')->insert(['business_id'=>$business,'actor_id'=>$actor,'booking_id'=>$result['booking']['id'],'action'=>'confirmed_proposal','details'=>json_encode(['action'=>$action,'draft_id'=>$draft->id]),'created_at'=>date('Y-m-d H:i:s')]);
            app(ReminderSchedule::class)->sync($result['booking']);
            $db->table('aireception_booking_drafts')->where('id',$draft->id)->update(['result'=>json_encode(['booking'=>$result['booking']])]);
            if($draft->conversation_id)app(DialogueSession::class)->result((int)$draft->conversation_id,(int)$result['booking']['person_id'],'confirmed',['booking'=>$result['booking']]);
            return $result;
        },3);
    }
}

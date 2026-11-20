<?php
namespace Modules\AiReception\Native;
/** Integer units, with a caller-configured cost scale; never floating point currency. */
final class UsageBudget
{
    private $db;
    public function __construct($db){$this->db=$db;}
    private function scope(int $business): void
    {
        if (app(Access::class)->business() !== $business) abort(403);
        app(Access::class)->require('aireception','conversations');
    }
    public function reserve(int $business,string $period,string $key,string $hash,string $kind,int $maximum): array
    {
        $this->scope($business);
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D',$period) || !preg_match('/^[A-Za-z0-9_-]{8,80}$/D',$key) || !preg_match('/^[a-f0-9]{64}$/D',$hash) || !in_array($kind,['openai','transcription','meta'],true) || $maximum<1 || $maximum>1000000000) abort(422);
        return $this->db->transaction(function()use($business,$period,$key,$hash,$kind,$maximum){
            $b=$this->db->table('aireception_budgets')->where('business_id',$business)->where('period',$period)->lockForUpdate()->first();
            if (!$b) abort(409,'Presupuesto no configurado; derivar a una persona.');
            $old=$this->db->table('aireception_usage')->where('budget_id',$b->id)->where('request_key',$key)->first();
            if($old){if(!hash_equals($old->request_hash,$hash) || $old->kind!==$kind || (int)$old->reserved_units!==$maximum)abort(409,'Idempotencia incompatible.');return ['id'=>$old->id,'state'=>$old->state,'replayed'=>true];}
            if($maximum>(int)$b->limit_units-(int)$b->used_units-(int)$b->reserved_units)abort(409,'Presupuesto agotado; derivar a una persona.');
            $now=date('Y-m-d H:i:s');
            $id=$this->db->table('aireception_usage')->insertGetId(['budget_id'=>$b->id,'request_key'=>$key,'request_hash'=>$hash,'kind'=>$kind,'state'=>'reserved','reserved_units'=>$maximum,'created_at'=>$now,'updated_at'=>$now]);
            $this->db->table('aireception_budgets')->where('id',$b->id)->increment('reserved_units',$maximum);
            return ['id'=>$id,'state'=>'reserved','replayed'=>false];
        },3);
    }
    public function settle(int $business,int $id,int $actual): void
    {
        $this->scope($business);
        if($actual<0)abort(422);
        // Resolve only an entry in this trusted tenant/business; lock budget before entry, as reserve does.
        $entry=$this->db->table('aireception_usage as u')->join('aireception_budgets as b','b.id','=','u.budget_id')->where('b.business_id',$business)->where('u.id',$id)->select('u.*')->first();
        if(!$entry)abort(404);
        $this->db->transaction(function()use($entry,$actual){
            $this->db->table('aireception_budgets')->where('id',$entry->budget_id)->lockForUpdate()->first();
            $u=$this->db->table('aireception_usage')->where('id',$entry->id)->lockForUpdate()->first();
            if($u->state==='settled'){if((int)$u->used_units!==$actual)abort(409);return;}
            if($actual>(int)$u->reserved_units)abort(409,'Consumo excede reserva: mantener bloqueo y conciliar.');
            $this->db->table('aireception_budgets')->where('id',$entry->budget_id)->decrement('reserved_units',(int)$u->reserved_units);
            $this->db->table('aireception_budgets')->where('id',$entry->budget_id)->increment('used_units',$actual);
            $this->db->table('aireception_usage')->where('id',$entry->id)->update(['state'=>'settled','used_units'=>$actual,'updated_at'=>date('Y-m-d H:i:s')]);
        },3);
    }
}

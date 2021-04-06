<?php

namespace App\Imports;

use App\Models\Tenant\Item;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Traits\InventoryTrait;

class InventoryImport implements ToCollection
{
    use Importable,InventoryTrait;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $registered = 0;
        unset($rows[0]);
        foreach ($rows as $row)
        {
            $internal_id  = $row[0];
            $warehouse_id =$row[1];
            $quantity     = $row[2];

            if($internal_id) {
                $item=Item::where('internal_id','=',$internal_id)
                    ->first();

                $itemInv = Inventory::where('item_id','=', $item->id)
                    ->Where('type','=',1)
                    ->Where('warehouse_id','=',$warehouse_id)
                    ->first();

            } else {
                $itemInv = null;
            }

            if(!$itemInv) {   //crea nuevos productos

                $inventory = new Inventory();
                $inventory->type = 1;
                $inventory->description = 'Stock inicial';
                $inventory->item_id = $item->id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->quantity = $quantity;
                $inventory->save();


                if (!$this->checkInventory($item->id, $warehouse_id)) {
                    $inventory = $this->createInitialInventory($item->id, $quantity, $warehouse_id);
                }

                $registered += 1;

            }else{ //actualiza stock

                if($this->checkInventory($item->id, $warehouse_id)) {
                    $this->updateStock($item->id, $quantity, $warehouse_id);
                }

                $registered += 1;

            }
        }
        $this->data = compact('total', 'registered');

    }

    public function getData()
    {
        return $this->data;
    }
}

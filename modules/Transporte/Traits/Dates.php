<?php
namespace Modules\Transporte\Traits;

use Carbon\Carbon;
use Modules\Transporte\Models\TransporteProgramacion;
use Modules\Transporte\Models\TransporteTerminales;
use Illuminate\Support\Collection;


trait Dates{

    private function obtenerPosicion(TransporteTerminales $terminal,Collection $rutas){
        $indexOrigen = $rutas->search(function($item) use ($terminal){
            return $item->id == $terminal->id;
        });

        return $indexOrigen;
    }

    private function obtenerMayores($position,Collection $rutas){
        $rutasSuperiores = $rutas->filter(function($item,$index) use ($position){
            return $index > $position;
        });
        return $rutasSuperiores;
    }

    private function obtenerMenores($position,Collection $rutas){
        $rutasMenores = $rutas->filter(function($item,$index) use ($position){
            return $index < $position;
        });
        return $rutasMenores;
    }

    private function hoursToMinutes(int $value) : int{
        return $value * 60;
    }


    

    private function getAvaibleDates(TransporteProgramacion $programacion, string $fecha){

        $dates = [$fecha];

        // dd($programacion->hora_salida);

        /** @var Modules\Transporte\Models\TransporteProgramacion   */
        $programacionParent = $programacion->programacion;

        $rutas = $programacionParent->rutas()->get();

        $origen = $programacion->origen;

        $timeStamp = new Carbon(sprintf('%s %s',$fecha, $programacion->hora_salida));



        $rutas->prepend($programacionParent->origen);
        $rutas->push($programacionParent->destino);

        $indexOrigen = $this->obtenerPosicion($origen,$rutas);

        $rutasMayores = $this->obtenerMayores($indexOrigen,$rutas);
        $rutasMenores = $this->obtenerMenores($indexOrigen, $rutas);

        $temp = $origen;
        $totalHoras = 0;
        foreach($rutasMayores as $mayor){
            $tiempoEstimado = $programacionParent->destinos_horarios()->where([
                'origen_id' => $temp->id,
                'destino_id' => $mayor->id,
            ])->first();
            if(!is_null($tiempoEstimado)) $totalHoras += $tiempoEstimado->tiempo_estimado;
            $temp = $mayor;
        }

        $rutasMenores->push($origen);
        $temp = $rutasMenores->shift();
        $totalHorasMenores = 0;

        foreach($rutasMenores as $menor){
            $tiempoEstimado = $programacionParent->destinos_horarios()->where([
                'origen_id' => $temp->id,
                'destino_id' => $menor->id,
            ])->first();
            if(!is_null($tiempoEstimado)) $totalHorasMenores += $tiempoEstimado->tiempo_estimado;
            $temp = $menor;
        }


        $totalEnMinutos = $this->hoursToMinutes($totalHoras);

        $newTime = clone $timeStamp;
        $time = $newTime->addMinutes($totalEnMinutos)->format('Y-m-d');

        if(!in_array($time,$dates)) array_push($dates,$time);


        $totalEnMinutos = $this->hoursToMinutes($totalHorasMenores);
        $newTime = clone $timeStamp;
        $time = $newTime->subMinutes($totalEnMinutos)->format('Y-m-d');

        if(!in_array($time,$dates)) array_push($dates,$time);

        return $dates;

    }

}
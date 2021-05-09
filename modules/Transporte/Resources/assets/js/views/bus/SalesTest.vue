<template>

    <div v-loading="loadingProgramaciones">        
       

        <!-- <bus :seats.sync="asientos" @dbclick="dbClick" /> -->

        <div class="row">
            <div class="col-12">
                <el-card class="box-card">
                    <div slot="header" class="clearfix">
                        <span>Venta de boletos terminal  <el-tag >{{ terminal.nombre }}</el-tag></span>
                    </div>

                    <div class="row">
                        <div class="col-5">
                            <div class="form-group">
                                <label for="">Ruta</label>
                                <el-select v-model="ruta" value-key="id"  popper-class="el-select-customers"
                                 @change="getProgramaciones">

                                    <el-option
                                    v-for="lProgramacion in listProgramaciones"
                                    :key="lProgramacion.id"
                                    :label="`${lProgramacion.origen.nombre}-${lProgramacion.destino.nombre}`"
                                    :value="lProgramacion">
                                    <!-- {{ lProgramacion.origen.nombre }} - {{ lProgramacion.destino.nombre }} -->
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <div v-if="ruta" class="col-3">
                            <div  class="form-group">
                                <label for="">Fecha salida</label>
                                <el-date-picker
                                v-model="fecha_salida"
                                type="date"
                                value-format="yyyy-MM-dd"
                                placeholder="Fecha salida" @change="getProgramaciones">
                                </el-date-picker>
                            </div>
                        </div>
                        <div class="col-3">
                            <div  class="form-group">
                                <label for="">Serie</label>
                                <el-input v-model="serie"></el-input>
                            </div>
                        </div>
                        <!-- <div class="col-3">
                            <div class="form-group">
                                <label for="">Ciudad</label>
                                <el-select filterable v-model="ciudad" value-key="id" remote  popper-class="el-select-customers"
                                :remote-method="searchCiudad"
                                :loading="loadingCiudades"
                                @change=" terminal = null ">

                                <el-option
                                v-for="destino in ciudades"
                                :key="destino.id"
                                :label="destino.nombre"
                                
                                :value="destino"></el-option>
                                </el-select>
                            </div>
                        </div> -->
                        <!-- <div v-if="ciudad" class="col-3">
                            <div class="form-group">
                                <label for="">Origen</label>
                                <el-select v-model="terminal" value-key="id"   popper-class="el-select-customers"
                                >
                                <el-option
                                v-for="terminal in terminales"
                                :key="terminal.id"
                                :label="terminal.nombre"
                                
                                :value="terminal"></el-option>
                                </el-select>
                            </div>
                        </div>


                        <div v-if="terminal" class="col-3">
                            <div class="form-group">
                                <label for="">Destino</label>
                                <el-select v-model="destino" value-key="id"   popper-class="el-select-customers"
                                >
                                <el-option
                                v-for="destino in destinos"
                                :key="destino.destino.id"
                                :label="destino.destino.nombre"
                                :value="destino.destino.id"></el-option>
                                </el-select>
                            </div>
                        </div>

                        <div v-if="destino" class="col-3">
                            <div  class="form-group">
                                <label for="">Fecha salida</label>
                                <el-date-picker
                                v-model="fecha_salida"
                                type="date"
                                value-format="yyyy-MM-dd"
                                placeholder="Fecha salida" @change="getProgramaciones">
                                </el-date-picker>
                            </div>
                        </div> -->
                        
                    </div>
                    <div v-if="programaciones.length > 0" class="row mt-2">
                        <div class="col-12">
                            <table class="table table-striped table-border">
                                <thead>
                                    <tr>
                                       
                                        <th>Vehiculo</th>
                                        <th>Hora salida</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="programacion in programaciones" :key="programacion.id">
                                        
                                        <td>{{ programacion.vehiculo.placa }}</td>
                                        <td>{{ programacion.hora_salida }}</td>
                                        <td>
                                            <template v-if="!selectProgramacion">
                                                <el-button type="success" size="mini" @click="seleccionar(programacion)">
                                                    <i class="fa fa-check"></i>
                                                </el-button>
                                            </template>
                                            <template v-else>
                                                <el-button v-if="selectProgramacion.id == programacion.id" type="danger" size="mini" @click="quitar">
                                                    <i class="fa fa-minus-circle"></i>
                                                </el-button>
                                                <el-button v-else type="success" size="mini" @click="seleccionar(programacion)">
                                                    <i class="fa fa-check"></i>
                                                </el-button>
                                            </template>
                                            
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        
                        </div>
                    </div>
                    <div v-else class="row mt-2">
                        <div class="col-12">
                            <el-alert
                                title="No hay programaciones"
                                center
                                type="info"
                                :closable="false">
                            </el-alert>
                        </div>
                    </div>

                    <template v-if="asientos.length > 0" >
                        <div class="row justify-content-center">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Disponible</label>
                                    <svg id="60611b2ba670a" gc-seat-static="0" gc-seat-element-id="2" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="51px" height="38px" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd;" viewBox="0 0 51 38" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 9.554 13.384 58.672 38.443">
                                        <g id="Capa_x0020_1">
                                            <metadata id="CorelCorpID_0Corel-Layer"></metadata>
                                            <path class="fil0 str0" :style="stateAsiento(1)"  d="M12 34c1,0 1,0 2,0l-2 0z"></path>
                                            <path class="fil1 str0" :style="stateAsiento(1)" d="M12 4c-2,0 -3,0 -3,0 -5,2 -6,7 -6,15 0,8 2,14 7,15 0,0 1,0 2,0 1,-1 1,-4 0,-6 2,-9 2,-9 0,-18 1,-1 1,-4 0,-6z"></path>
                                            <path class="fil0 str0" :style="stateAsiento(1)" d="M14 4c-1,0 -2,0 -2,0l2 0z"></path>
                                            <path class="fil0 str0" :style="stateAsiento(1)" d="M49 19c0,-8 -1,-14 -3,-15 -3,-1 -10,0 -19,0 -4,0 -9,0 -13,0 1,1 1,4 0,6 2,9 2,10 0,18 1,2 1,5 0,6 4,0 8,0 13,0 8,0 17,1 19,0 2,-1 3,-7 3,-15z"></path>

                                            <path class="fil2 str1" :style="stateAsiento(1,{isSeat:true})" d="M48 29c-3,0 -6,0 -10,0 -1,0 -2,0 -3,0 -12,0 -22,-1 -24,-1 -3,-1 -1,-4 -1,-9 0,-4 -2,-8 1,-9 4,-1 19,-1 24,-1 1,0 2,0 3,0 4,0 7,0 10,0 2,2 2,18 0,20z"></path>
                                            <path class="fil3 str2" :style="stateAsiento(1,{isBelt:true})" d="M18 4c1,2 1,4 0,6 2,10 2,9 0,18 1,3 1,3 0,6l4 0c1,-1 1,-4 0,-5 2,-9 2,-11 0,-19 1,-2 1,-5 0,-6l-4 0z"></path>
                                            <path class="fil4 str1" :style="stateAsiento(1)" d="M42 34l-18 0c0,0 0,0 0,0l0 3c0,0 0,0 0,0l18 0c0,0 0,0 0,0l0 -3c0,0 0,0 0,0z"></path>
                                            <path class="fil4 str1" :style="stateAsiento(1)" d="M42 1l-18 0c0,0 0,0 0,0l0 3c0,0 0,0 0,0l18 0c0,0 0,0 0,0l0 -3c0,0 0,0 0,0z"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Ocupado</label>
                                    <svg id="60611b2ba670a" gc-seat-static="0" gc-seat-element-id="2" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="51px" height="38px" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd;" viewBox="0 0 51 38" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 9.554 13.384 58.672 38.443">
                                        <g id="Capa_x0020_1">
                                            <metadata id="CorelCorpID_0Corel-Layer"></metadata>
                                            <path class="fil0 str0" :style="stateAsiento(2)"  d="M12 34c1,0 1,0 2,0l-2 0z"></path>
                                            <path class="fil1 str0" :style="stateAsiento(2)" d="M12 4c-2,0 -3,0 -3,0 -5,2 -6,7 -6,15 0,8 2,14 7,15 0,0 1,0 2,0 1,-1 1,-4 0,-6 2,-9 2,-9 0,-18 1,-1 1,-4 0,-6z"></path>
                                            <path class="fil0 str0" :style="stateAsiento(2)" d="M14 4c-1,0 -2,0 -2,0l2 0z"></path>
                                            <path class="fil0 str0" :style="stateAsiento(2)" d="M49 19c0,-8 -1,-14 -3,-15 -3,-1 -10,0 -19,0 -4,0 -9,0 -13,0 1,1 1,4 0,6 2,9 2,10 0,18 1,2 1,5 0,6 4,0 8,0 13,0 8,0 17,1 19,0 2,-1 3,-7 3,-15z"></path>

                                            <path class="fil2 str1" :style="stateAsiento(2,{isSeat:true})" d="M48 29c-3,0 -6,0 -10,0 -1,0 -2,0 -3,0 -12,0 -22,-1 -24,-1 -3,-1 -1,-4 -1,-9 0,-4 -2,-8 1,-9 4,-1 19,-1 24,-1 1,0 2,0 3,0 4,0 7,0 10,0 2,2 2,18 0,20z"></path>
                                            <path class="fil3 str2" :style="stateAsiento(2,{isBelt:true})" d="M18 4c1,2 1,4 0,6 2,10 2,9 0,18 1,3 1,3 0,6l4 0c1,-1 1,-4 0,-5 2,-9 2,-11 0,-19 1,-2 1,-5 0,-6l-4 0z"></path>
                                            <path class="fil4 str1" :style="stateAsiento(2)" d="M42 34l-18 0c0,0 0,0 0,0l0 3c0,0 0,0 0,0l18 0c0,0 0,0 0,0l0 -3c0,0 0,0 0,0z"></path>
                                            <path class="fil4 str1" :style="stateAsiento(2)" d="M42 1l-18 0c0,0 0,0 0,0l0 3c0,0 0,0 0,0l18 0c0,0 0,0 0,0l0 -3c0,0 0,0 0,0z"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Reservado</label>
                                    <svg id="60611b2ba670a" gc-seat-static="0" gc-seat-element-id="2" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="51px" height="38px" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd;" viewBox="0 0 51 38" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 9.554 13.384 58.672 38.443">
                                        <g id="Capa_x0020_1">
                                            <metadata id="CorelCorpID_0Corel-Layer"></metadata>
                                            <path class="fil0 str0" :style="stateAsiento(3)"  d="M12 34c1,0 1,0 2,0l-2 0z"></path>
                                            <path class="fil1 str0" :style="stateAsiento(3)" d="M12 4c-2,0 -3,0 -3,0 -5,2 -6,7 -6,15 0,8 2,14 7,15 0,0 1,0 2,0 1,-1 1,-4 0,-6 2,-9 2,-9 0,-18 1,-1 1,-4 0,-6z"></path>
                                            <path class="fil0 str0" :style="stateAsiento(3)" d="M14 4c-1,0 -2,0 -2,0l2 0z"></path>
                                            <path class="fil0 str0" :style="stateAsiento(3)" d="M49 19c0,-8 -1,-14 -3,-15 -3,-1 -10,0 -19,0 -4,0 -9,0 -13,0 1,1 1,4 0,6 2,9 2,10 0,18 1,2 1,5 0,6 4,0 8,0 13,0 8,0 17,1 19,0 2,-1 3,-7 3,-15z"></path>

                                            <path class="fil2 str1" :style="stateAsiento(3,{isSeat:true})" d="M48 29c-3,0 -6,0 -10,0 -1,0 -2,0 -3,0 -12,0 -22,-1 -24,-1 -3,-1 -1,-4 -1,-9 0,-4 -2,-8 1,-9 4,-1 19,-1 24,-1 1,0 2,0 3,0 4,0 7,0 10,0 2,2 2,18 0,20z"></path>
                                            <path class="fil3 str2" :style="stateAsiento(3,{isBelt:true})" d="M18 4c1,2 1,4 0,6 2,10 2,9 0,18 1,3 1,3 0,6l4 0c1,-1 1,-4 0,-5 2,-9 2,-11 0,-19 1,-2 1,-5 0,-6l-4 0z"></path>
                                            <path class="fil4 str1" :style="stateAsiento(3)" d="M42 34l-18 0c0,0 0,0 0,0l0 3c0,0 0,0 0,0l18 0c0,0 0,0 0,0l0 -3c0,0 0,0 0,0z"></path>
                                            <path class="fil4 str1" :style="stateAsiento(3)" d="M42 1l-18 0c0,0 0,0 0,0l0 3c0,0 0,0 0,0l18 0c0,0 0,0 0,0l0 -3c0,0 0,0 0,0z"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            
                            
                            
                        </div>
                        <div class="row">
                            <bus :seats.sync="asientos" @dbclick="dbClick"  />
                        </div>
                        
                    </template>
                    

                </el-card>
            </div>
           
        </div>
        <venta-asiento 
        :visible.sync="visible" 
        :asiento="asiento" 
        :estados-asientos="estadoAsientos" 
        :programacion="selectProgramacion"
        :fecha-salida="fecha_salida"
        @onUpdateItem="onUpdateItem"
        :serie="serie"
         />
    </div>
    
    
</template>
<script>
import Bus from './Bus';
import VentaAsiento from './VentaAsiento.vue';
export default {

    props:{

        listProgramaciones:{
            type:Array,
            required:true,
            default:() => []
        },
        terminal:{
            type:Object,
            required:true,
            default:{},
        },
        estadoAsientos:{
            type:Array,
            required:true,
            default:() => []
        }
        
    },
    components:{
        Bus,
        VentaAsiento
    },
    created(){
        this.searchCiudad();
    },
    data(){
        return ({
            vehiculo:null,
            guardarSeats:false,
            asientos:[],
            loadingCiudades:false,
            ciudades:[],
            ciudad:null,

            terminales:[],
            ruta:null,
            

            fecha_salida:'',
            destino:null,
            destinos:[],

            loadingProgramaciones:false,
            programaciones:[],
            selectProgramacion:{},

            visible:false,
            asiento:{},
            serie:null

        });
    },
    computed:{
        
    },
    watch:{
        // ciudad(newVal){
        //     this.terminales = newVal ? newVal.terminales : [];
        // },
        // terminal(newVal){
        //     this.destinos = newVal ? newVal.programaciones : [];
        // },
    },
    methods:{

        stateAsiento(estado,config={}){

            /** Manejo de los estados del asiento */
            if(estado == 1){//Disponible
                return {
                    fill:'#fff',
                    animation:'none'
                }
            }else if(estado == 2 ){ //Ocupado

                if(config.isBelt){ //Hay un path que se pinta de azul
                    return{
                        fill:'#00AEFF'
                    };
                }
                return {
                    fill:'#1b99a5',
                    animation:'none'
                }
                
            }else if(estado == 3){ //Reservado

                if(config.isSeat){
                    return {
                        fill:'#fff',
                        animation:'reservado 1s infinite'
                    }
                }

                return {
                    fill:'#fff',
                    animation:'none'
                }

                
                
            }
        },

        quitar(){
            this.selectProgramacion = null;
            this.asientos = [];
        },

        async getProgramaciones(){

            // console.log(this.encomienda.fecha_salida);
            if(this.fecha_salida){
                this.loadingProgramaciones = true;
                this.programaciones = [];
                this.selectProgramacion = {};
                this.asientos = [];
                let data = {
                    origen_id:this.ruta.terminal_origen_id,
                    destino_id:this.ruta.terminal_destino_id,
                    fecha_salida:this.fecha_salida
                }
                const { data:programaciones } = await this.$http.post(`/transportes/sales/programaciones-disponibles`,data);
                this.loadingProgramaciones = false;
                this.programaciones = programaciones.programaciones;
            }

        },

        dbClick(asiento){
            // asiento.estado_asiento_id = 1;
            if(asiento.type != 'ss') return;
            this.asiento = asiento;
            this.visible = true;
        },

        agregarItem(type){
            //ss = asiento
            //sb = baño
            //ses = escalera
            //sv = televisión       

            switch(type){
                case 'ss':
                    /** Obtengo solo los que son asientos normales */
                    let posicion = this.asientos.filter( asiento => asiento.type == 'ss' );
                    this.asientos.push({
                        id:null,
                        top:'50px',
                        left:'50px',
                        type:'ss',
                        estado_asiento_id:1,
                        numero_asiento:(posicion.length + 1)
                    });
                    break;
                case 'sb':
                    this.asientos.push({
                        id:null,
                        top:'50px',
                        left:'50px',
                        type:'sb',
                        estado_asiento_id:1,
                        numero_asiento:0
                    });
                    break;
                case 'ses':
                    this.asientos.push({
                        id:null,
                        top:'50px',
                        left:'50px',
                        type:'ses',
                        estado_asiento_id:1,
                        numero_asiento:0
                    });
                    break;
                case 'sv':
                    this.asientos.push({
                        id:null,
                        top:'50px',
                        left:'50px',
                        type:'sv',
                        estado_asiento_id:1,
                        numero_asiento:0
                    });
                    break;
            }

        },

        searchCiudad(value= ''){
            this.loadingCiudades = true;
            this.$http.get(`/transportes/sales/get-ciudades?search=${value}`)
            .then( response => {
                const { data } = response;
                this.ciudades = data.data;
                this.loadingCiudades = false;
            }).catch(error => {
                this.axiosError(error);
            })

        },
        seleccionar(programacion){
            this.selectProgramacion = programacion;
            this.asientos = programacion.vehiculo.seats;
        },
        async onUpdateItem(){
            let program = this.selectProgramacion;
            await this.getProgramaciones();
            this.selectProgramacion = this.programaciones
            .find(  programacion => programacion.id == program.id );
            this.asientos = this.selectProgramacion.vehiculo.seats;
        }

        
    }
}
</script>

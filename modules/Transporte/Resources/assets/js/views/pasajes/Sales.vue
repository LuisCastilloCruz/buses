<template>

    <div v-loading="load">

        <div class="row">
            <div class="col-12">
                <el-card class="box-card">
                    <div slot="header" class="row justify-content-between">
                        <!-- <el-button  type="primary" @click="$emit('update:sale',false)"><i class="fa fa-arrow-left"></i></el-button> -->
                        <span class="mr-2"><a class="btn btn-primary btn-sm" href="/transportes/pasajes"> <i class="fa fa-arrow-left"></i>  </a></span>
                        <span>Venta de boletos terminal  <el-tag >{{ terminal.nombre }}</el-tag></span>
                        <el-button type="primary" @click="nuevaVenta">Nuevo</el-button>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                               <label for="">Origen</label>
                                <el-select v-model="terminalId" filterable remote disabled  popper-class="el-select-customers"
                                    placeholder="Buscar origen"
                                    :remote-method="searchTerminales"
                                    :loading="loadingTerminales"
                                    @change="searchDestinos"
                                    >
                                    <el-option v-for="terminal in terminales" :key="terminal.id" :value="terminal.id" :label="terminal.nombre">
                                    </el-option>
                                </el-select>
                                
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="">Destino</label>
                                <el-select v-model="destinoId" 
                                :loading="loadingDestinos" 
                                popper-class="el-select-customers" 
                                placeholder="Destino"
                                @change="getProgramaciones"
                                >
                                    <template v-for="destino in destinos">
                                       <!--<el-option  :key="destino.terminal_destino_id" :value="destino.destino.id" :label="`${destino.destino.nombre}`">-->
                                         <el-option  :key="destino.id" :value="destino.id" :label="`${destino.nombre}`">
                                        </el-option>
                                    </template>
                                    
                                </el-select>
                            </div>
                        </div>

                        <div class="col-md-4 row align-items-end">
                            <div class="form-group">
                                <label for="">Tipo de venta</label>
                                <el-select v-model="tipoVenta" 
                                popper-class="el-select-customers" 
                                placeholder="Tipo de venta"
                                :disabled="pasajero ? true : false"
                                >
                                    <el-option :value="1" label="Venta Libre">
                                    </el-option>
                                    <el-option  :value="2" label="Venta Normal">
                                    </el-option>
                                </el-select>

                               
                            </div>
                        </div>
                        

                        
                        <div v-if="destinoId" class="col-3">
                            <div  class="form-group">
                                <label for="">Fecha salida</label>
                                <el-date-picker
                                v-model="fecha_salida"
                                type="date"
                                value-format="yyyy-MM-dd"
                                placeholder="Fecha salida"
                                @change="getProgramaciones">
                                </el-date-picker>
                            </div>
                        </div>
                        

                        <div v-loading="loadingProgramaciones" v-if="destinoId && tipoVenta == 2" class="col-5">
                            <div v-if="programaciones.length > 0" class="row mt-2">
                                <div class="col-12">
                                    <table class="table table-striped table-border">
                                        <thead>
                                        <tr>
                                            <th>Terminal</th>    
                                            <th>Vehiculo</th>
                                            <th>Hora salida</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="programacion in programaciones" :key="programacion.id">
                                            <td>{{ programacion.destino.nombre }}</td>   
                                            <td>{{ programacion.transporte.placa }}</td>
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
                        </div>

                        <div v-if="destinoId && tipoVenta == 1" class="col-md-4">
                            <div class="from-group">
                                <label for="">Hora salida</label>
                                <el-input type="time" v-model="horaSalida"></el-input>
                            </div>
                        </div>
                       

                        <div class="col-md-4">
                            <template v-if="asientos.length > 0 && tipoVenta == 2" >
                            <div class="row justify-content-center">
                                <div class="col-3">
                                    <div v-if="vehiculo" class="row justify-content-center">
                                        <div class="form-group">
                                            <label for="" class="pl-3 text-right pr-2" style="width:40%;float: left">Piso</label>
                                            <div style="width: 60%;float: left">
                                                <el-select v-model="piso" placeholder="Piso" :disabled="vehiculo.pisos == 1">
                                                    <el-option v-for="floor in vehiculo.pisos" :label="floor" :key="floor" :value="floor" >
                                                    </el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

                            

                            </template>
                        </div>

                    </div>
                    <div v-if="asientos.length > 0 && tipoVenta == 2" class="row">
                        <bus v-if="piso == 1" :seats.sync="asientosPisoUno" @dbclick="dbClick"  />
                        <bus v-if="piso == 2" :seats.sync="asientosPisoDos" @dbclick="dbClick"  />
                    </div>
                    

                    <venta-asiento-libre
                    :asiento="asiento"
                    :tipo-venta="tipoVenta"
                    :transporte-pasaje="pasajero"
                    :estados-asientos="estadoAsientos"
                    :programacion="selectProgramacion"
                    :fecha-salida="fecha_salida"
                    @onUpdateItem="onUpdateItem"
                    :establishment="establishment"
                    :series="series"
                    :document-types-invoice="documentTypesInvoice"
                    :payment-method-types="paymentMethodTypes"
                    :payment-destinations="paymentDestinations"
                    :configuration="configuration"
                    @onSuccessVenta="onSuccessVenta"
                    @anularBoleto="anularBoleto"
                    :document_type_03_filter="document_type_03_filter"
                    :is-cash-open="isCashOpen"
                    :destinoId="destinoId"
                    :origen="origen"
                    :horaSalida="horaSalida"
                    />
                   


                    

                    


                </el-card>
            </div>

        </div>
        

        <document-options
        :showDialog.sync="showDialogDocumentOptions"
        :recordId="documentId"
        :isContingency="false"
        :showClose="true"
        :configuration="configuration"
        ></document-options>

        <documents-voided 
        :showDialog.sync="showDialogVoided"
        :recordId="documentId"></documents-voided>
        
        <detalle-boleto 
        :document-types-invoice="documentTypesInvoice"
        :visible.sync="visible"
        :programacion="selectProgramacion"
        :estados-asientos="estadoAsientos"
        :fecha-salida="fecha_salida"
        :establishment="establishment"
        :series="series"
        :payment-destinations="paymentDestinations"
        :payment-method-types="paymentMethodTypes"
        :configuration="configuration"
        :asiento="asiento"
        @anularBoleto="anularBoleto"
        />


        <documents-voided 
        :showDialog.sync="showDialogVoided"
        :recordId="documentId"></documents-voided>
    
    </div>


</template>
<script>
import Bus from './Bus';
import VentaAsientoLibre from './VentaAsientoLibre.vue';
import DocumentOptions from "@views/documents/partials/options.vue";
import DocumentsVoided from '@views/documents/partials/voided.vue';
import DetalleBoleto from './DetalleBoleto.vue';
export default {

    props:{
        isCashOpen:{
            type:Boolean,
            required:true,
            default:false,
        },
        document_type_03_filter:{
            type:Boolean,
            required:true,
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
        },
        establishment:{
            type:Object,
            required:true
        },
        series:{
            type:Array,
            default:() => []
        },
        documentTypesInvoice:{
            type:Array,
            default:() => []
        },
        paymentMethodTypes: {
            type: Array,
            required: true,
        },
        paymentDestinations: {
            type: Array,
            required: true,
        },
        configuration:{
            type: Object,
            required: true,
        },
        itemPasajero:{
            type:Object|null,
            default:() => null
        }

    },
    components:{
        Bus,
        VentaAsientoLibre,
        DocumentOptions,
        DocumentsVoided,
        DetalleBoleto
    },
    watch:{
        terminalId(newVal){
            this.origen = this.terminales.find( ter => ter.id == this.terminalId );
        },
        // destinoId(newVal){
        //     let exist = this.destinos.find( destino => destino.destino.id == newVal );
        //     if(exist) this.destino = exist.destino;
        // }
    },
    async created(){
        this.load = true;
        // this.searchCiudad();
        this.$eventHub.$on('reloadData',async() => {
            this.cancelarBoleto();
        });
      
        await this.searchTerminales();
        this.terminalId = this.terminal.id;
        await this.searchDestinos();
        await this.onCreate();
        this.load = false;
    },
    data(){
        return ({
            load:false,
            tipoVenta:2,
            visibleAsientoLibre:false,
            pasajero:this.itemPasajero,

            pasajeroId:null,
            showDialogVoided:false,
            vehiculo:null,
            guardarSeats:false,
            asientos:[],
            loadingCiudades:false,
            ciudades:[],
            ciudad:null,
            

            terminales:[],
            ruta:null,


            fecha_salida: moment().format('YYYY-MM-DD'),
            destinoId:null,
            destinos:[],

            loadingProgramaciones:false,
            programaciones:[],
            selectProgramacion:{},
            piso:1,

            visible:false,
            asiento:null,

            //document
            showDialogDocumentOptions:false,
            documentId:null,

            loadingDestinos:false,
            terminalId:null,

            loadingTerminales:false,
            horaSalida:null,
            destino:null,
            origen:null,
        });
    },
    computed:{
        

        asientosPisoUno:function(){
            return this.asientos.filter(  asiento => asiento.piso == 1);
        },
        asientosPisoDos:function(){
            return this.asientos.filter(  asiento => asiento.piso == 2);
        }

    },
    methods:{

        nuevaVenta(){
            this.pasajero = null;
            this.selectProgramacion = {};
            this.programaciones = [];
            this.destinoId = null;
            this.fecha_salida = null;
            this.horaSalida = null;
            this.origen = null;
        },

        async onCreate(){
            if(this.itemPasajero){
                this.tipoVenta = this.itemPasajero.tipo_venta;
                this.terminalId = this.itemPasajero.origen_id;
                this.fecha_salida = this.itemPasajero.fecha_salida;
                this.destinoId = this.itemPasajero.destino_id;
                this.asiento = this.itemPasajero.asiento;
                this.horaSalida = this.itemPasajero.hora_salida;
                if(this.tipoVenta == 2){
                    await this.getProgramaciones();
                    let programacion = this.programaciones.find( p  => p.id === this.itemPasajero.programacion.id)
                    this.seleccionar(programacion);
                }
               
            }

        },
        changeTipoVenta(){

            if(this.tipoVenta == 2){
                let programacion = this.programaciones.find( p  => p.id === this.itemPasajero.programacion.id)
                this.seleccionar(programacion);
            }

        },

        async onSuccessVenta(documentId){
            this.documentId = documentId;
            this.showDialogDocumentOptions = true;
            // if(this.tipoVenta == 2){
            //     await this.onUpdateItem()
            //     this.documentId = documentId;
            //     this.showDialogDocumentOptions = true;
            // }else {
            //     this.$emit('onSuccessVenta',documentId);
            //     this.$emit('update:sale',false);
            // }
            
        },
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
            if(this.fecha_salida){
                this.loadingProgramaciones = true;
                this.programaciones = [];
                this.selectProgramacion = {};
                this.asientos = [];
                let data = {
                    origen_id:this.terminalId,
                    destino_id:this.destinoId,
                    fecha_salida:this.fecha_salida
                }
                const { data:programaciones } = await this.$http.post(`/transportes/sales/programaciones-disponibles`,data);
                this.loadingProgramaciones = false;
                this.programaciones = programaciones.programaciones;
            }

        },
        async searchTerminales(input = ''){
            this.loadingTerminales = true;
            const { data } = await this.$http.get(`/transportes/encomiendas/get-terminales?search=${input}`);
            this.loadingTerminales = false;
            this.terminales = data.terminales;
        },

        dbClick(asiento){
            
            if(asiento.type != 'ss') return;
            if(asiento.estado_asiento_id == 2) {
                this.asiento = asiento;
                this.visible = true;
                return;
            };
            this.asientos = this.asientos.map( seat => {
                if(seat.estado_asiento_id == 4){
                    seat.estado_asiento_id = 1;
                }
                return seat;
            } );
            asiento.estado_asiento_id = 4;
            this.asiento = asiento;

            let element = document.getElementById('precio-boleto');
            element.focus();
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
            this.visibleAsientoLibre = false;
            this.selectProgramacion = programacion;
            if(this.tipoVenta == 2){
                this.vehiculo = programacion.transporte;
                this.asientos = programacion.transporte.asientos;
            }
            
        },
        async onUpdateItem(){
            if(this.tipoVenta == 2){
                let program = this.selectProgramacion;
                await this.getProgramaciones();
                this.selectProgramacion = this.programaciones
                .find(  programacion => programacion.id == program.id );
                this.asientos = this.selectProgramacion.transporte.asientos;
                this.vehiculo = this.selectProgramacion.transporte;
            }
            
        },

        anularBoleto(pasaje){
            this.documentId = pasaje.document_id;
            this.pasajeroId = pasaje.id;
            this.showDialogVoided = true;

        },
        async cancelarBoleto(){
            try{
                const { data } = await axios.delete(`/transportes/pasajes/${this.pasajeroId}/delete`);

                if(!data.success){
                    this.$message({
                        type: 'error',
                        message: data.message
                    });
                }

                this.$message({
                    type: 'success',
                    message: data.message
                });

                this.onUpdateItem();
                
            }catch(error){

                this.$message({
                    type: 'error',
                    message: 'Lo sentimos ha ocurrido un error'
                });

            }
        },
        async searchDestinos(){
            this.loadingDestinos = true;
            const { data } = await this.$http.get(`/transportes/pasajes/${this.terminalId}/get-destinos`);
            this.loadingDestinos = false;
            this.destinos = data.destinos;
        },




    }
}
</script>

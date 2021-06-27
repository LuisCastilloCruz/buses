<template>
    <div>
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <el-button type="info" @click="agregarItem('sb')">Baño</el-button>
                
                <el-button type="info" @click="agregarItem('ses')">Scalera</el-button>
                <el-button type="info" @click="agregarItem('sv')">Televisión</el-button>
                <el-button v-if="!remove" type="danger" @click="remove = true">Eliminar</el-button>
                <el-button v-else @click="remove = false">Cancelar</el-button>

            </div>
            <div class="col-12 d-flex justify-content-center">
                <div class="col-3">
                    <div class="form-group">
                        <label for="">Piso</label>
                        <el-select v-model="piso" placeholder="Piso">
                            <el-option v-for="floor in pisos" :label="floor" :key="floor" :value="floor" >
                            </el-option>
                        </el-select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="row text-center justify-content-center">
                        <label for="">Asiento</label>
                        <el-input type="number" v-model="asiento.numero_asiento"></el-input>
                        <el-button class="mt-2" type="info" @click="agregarItem('ss')">+</el-button>
                    </div>
                    <!-- <div class="form-group row">
                        

                    </div> -->
                </div>
                
            </div>
        </div>
        <bus v-if="piso == 1" :seats.sync="asientosPisoUno" drag :remove="remove" @onDelete="eliminar" />
        <bus v-if="piso == 2" :seats.sync="asientosPisoDos" drag :remove="remove" @onDelete="eliminar" />

        <div class="row mt-2">
            <div class="col-12 d-flex justify-content-center">
                <el-button :loading="loading" type="primary" @click="guardar">
                    Guardar
                </el-button>
            </div>
        </div>
    </div>
</template>
<script>
import Bus from '../bus/Bus';
export default {
    props:{
        seats:{
            type:Array,
            required:true,
            default: () => []
        },
        vehiculo:{
            type:Object,
            required:true,
            default:() => null
        }
    },
    components:{
        Bus
    },
    created(){
        this.asientos = this.seats;
        this.piso = 1;
        this.pisos = parseInt(this.vehiculo.pisos);
        this.initAsiento()
    },
    data(){
        return({
            asientos:[],
            loading:false,
            piso:null,
            transporte:this.vehiculo,
            remove:false,
            pisos:null,
            numeroAsiento:null,
            asiento:null
        });
    },
    watch:{
        asientos(newVal){
            this.$emit('input',newVal);
        },
        transporte(newVal){
            this.$emit('input-transporte',newVal);
        }
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

        agregarItem(type){
            //ss = asiento
            //sb = baño
            //ses = escalera
            //sv = televisión   

            if(type === 'ss') {
                if(!this.asiento.numero_asiento) return this.$message.info('Debe agregar número de asiento');
                let exist = this.asientos.find( s => s.numero_asiento == this.asiento.numero_asiento  );
                if(exist) return this.$message.info('Numero de asiento ya existe');
            }

            
            this.asiento.piso = this.piso;
            this.asiento.type = type;
            this.asiento.numero_asiento = type === 'ss' ? this.asiento.numero_asiento : 0;

            this.asientos.push(Object.assign({},this.asiento));
            this.initAsiento();
            // switch(type){
            //     case 'ss':
            //         /** Obtengo solo los que son asientos normales */
            //         // let posicion = this.asientos.filter( asiento => asiento.type == 'ss');
                    
            
                    
                    
            //         this.asiento.
            //         this.asientos.push({
            //             id:null,
            //             top:'50px',
            //             left:'50px',
            //             type:'ss',
            //             estado_asiento_id:1,
            //             piso:this.piso,
            //             numero_asiento:this.numeroAsiento
            //         });
            //         this.numeroAsiento = null;
            //         break;
            //     case 'sb':
            //         this.asientos.push({
            //             id:null,
            //             top:'50px',
            //             left:'50px',
            //             type:'sb',
            //             piso:this.piso,
            //             estado_asiento_id:1,
            //             numero_asiento:0
            //         });
            //         break;
            //     case 'ses':
            //         this.asientos.push({
            //             id:null,
            //             top:'50px',
            //             left:'50px',
            //             type:'ses',
            //             piso:this.piso,
            //             estado_asiento_id:1,
            //             numero_asiento:0
            //         });
            //         break;
            //     case 'sv':
            //         this.asientos.push({
            //             id:null,
            //             top:'50px',
            //             left:'50px',
            //             type:'sv',
            //             piso:this.piso,
            //             estado_asiento_id:1,
            //             numero_asiento:0
            //         });
            //         break;
            // }
        },
        guardar(evt){
            this.loading = true;
            this.$http.put(`/transportes/vehiculos/${this.vehiculo.id}/guardar-asientos`,{
                asientos:this.asientos,
            }).then( response => {
                this.transporte = response.data.vehiculo;
                this.asientos = response.data.vehiculo.seats;
                this.$message({
                    type: 'success',
                    message: response.data.message
                });
                this.loading = false;
                this.initAsiento();
            }).catch(error => {
                this.axiosError(error);
            });
        },
        eliminar(asiento,index){

            this.$msgbox({
                title:'Eliminar',
                message:'¿Desea eliminar el asiento?',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancelar',
                beforeClose:(action, instance, done) => {
                    if (action === 'confirm') {
                        instance.confirmButtonLoading = true;
                        instance.confirmButtonText = 'Cargando...';
                        setTimeout(async() => {
                            if(asiento.id){
                                try{
                                    const { data } = await this.$http.delete(`/transportes/vehiculos/${asiento.id}/eliminar`);
                                    instance.confirmButtonLoading = false;
                                    if(!data.success){ 
                                        this.$message({
                                            type: 'error',
                                            message: data.message
                                        }); 
                                        done();
                                        return;
                                    }
                                    this.asientos.splice(index,1);
                                    this.$message({
                                        type: 'success',
                                        message: data.message
                                    }); 

                                    done();


                                }catch(error){
                                    this.$axiosError(error);
                                    done();
                                }
                            }else {
                                this.asientos.splice(index,1);
                                instance.confirmButtonLoading = false;
                                this.$message({
                                    type: 'success',
                                    message: 'Se ha eliminado el asiento'
                                }); 
                                done();
                            } 
                        }, 1000);
                       
                        
                    } else {
                        done();
                    }
                }
            }).then(() => this.remove = false);
           


        },
        initAsiento(){
            this.asiento = {
                id:null,
                top:'50px',
                left:'50px',
                type:'ss',
                estado_asiento_id:1,
                piso:1,
                numero_asiento:null
            };
        }

    }
    
}
</script>
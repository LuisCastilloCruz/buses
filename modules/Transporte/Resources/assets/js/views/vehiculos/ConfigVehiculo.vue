<template>
    <div>
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <el-button type="info" @click="agregarItem('sb')">Baño</el-button>
                <el-button type="info" @click="agregarItem('ss')">Asiento</el-button>
                <el-button type="info" @click="agregarItem('ses')">Scalera</el-button>
                <el-button type="info" @click="agregarItem('sv')">Televisión</el-button>
            </div>
            <div class="col-12 d-flex justify-content-center">
                <div class="col-3">
                    <div class="form-group">
                        <label for="">Piso</label>
                        <el-select v-model="piso" placeholder="Piso">
                            <el-option v-for="floor in vehiculo.pisos" :label="floor" :key="floor" :value="floor" >
                            </el-option>
                        </el-select>
                    </div>
                </div>
            </div>
        </div>
        <bus v-if="piso == 1" :seats.sync="asientosPisoUno" drag @onDelete="eliminar" />
        <bus v-if="piso == 2" :seats.sync="asientosPisoDos" drag />

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
    },
    data(){
        return({
            asientos:[],
            loading:false,
            piso:null,
            transporte:this.vehiculo
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
            switch(type){
                case 'ss':
                    /** Obtengo solo los que son asientos normales */
                    let posicion = this.asientos.filter( asiento => asiento.type == 'ss');
                    this.asientos.push({
                        id:null,
                        top:'50px',
                        left:'50px',
                        type:'ss',
                        estado_asiento_id:1,
                        piso:this.piso,
                        numero_asiento:(posicion.length + 1)
                    });
                    break;
                case 'sb':
                    this.asientos.push({
                        id:null,
                        top:'50px',
                        left:'50px',
                        type:'sb',
                        piso:this.piso,
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
                        piso:this.piso,
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
                        piso:this.piso,
                        estado_asiento_id:1,
                        numero_asiento:0
                    });
                    break;
            }
        },
        guardar(evt){
            this.loading = true;
            this.$http.put(`/transportes/vehiculos/${this.vehiculo.id}/guardar-asientos`,{
                asientos:this.asientos,
            }).then( response => {
                this.transporte = response.data.vehiculo;
                this.$message({
                    type: 'success',
                    message: response.data.message
                });
                this.loading = false;
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
                beforeClose:async(action, instance, done) => {
                    if (action === 'confirm') {
                        instance.confirmButtonLoading = true;
                        instance.confirmButtonText = 'Cargando...';
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
                        
                    } else {
                        done();
                    }
                }
            });
           


        }

    }
    
}
</script>
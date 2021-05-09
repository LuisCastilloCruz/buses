<template>
    <div>
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <el-button type="info" @click="agregarItem('sb')">Baño</el-button>
                <el-button type="info" @click="agregarItem('ss')">Asiento</el-button>
                <el-button type="info" @click="agregarItem('ses')">Scalera</el-button>
                <el-button type="info" @click="agregarItem('sv')">Televisión</el-button>
            </div>
        </div>
        <bus :seats.sync="asientos" drag />

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
    },
    data(){
        return({
            asientos:[],
            loading:false,
        });
    },
    watch:{
        asientos(newVal){
            this.$emit('input',newVal);
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
        guardar(evt){
            this.loading = true;
            this.$http.put(`/transportes/vehiculos/${this.vehiculo.id}/guardar-asientos`,{
                asientos:this.asientos,
            }).then( response => {
                this.$message({
                    type: 'success',
                    message: response.data.message
                });
                this.loading = false;
            }).catch(error => {
                this.axiosError(error);
            });
        },

    }
    
}
</script>
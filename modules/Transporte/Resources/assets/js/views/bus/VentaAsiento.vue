<template>
    <el-dialog title="Venta" :visible="visible" @close="onClose" @open="onCreate" :close-on-click-modal="false">
        <div class="row">

            <div class="col-5">
                <div class="form-group">
                    <label for="dni">Pasajero</label>
                    <el-select v-model="pasajeroId" filterable remote  popper-class="el-select-customers"
                        placeholder="Buscar remitente"
                        :remote-method="searchPasajero"
                        :loading="loadingPasajero"
                        >
                        <el-option v-for="pasaje in pasajeros" :key="pasaje.id" :value="pasaje.id" :label="pasaje.name">

                        </el-option>
                    </el-select>
                </div>
            </div>
            <!-- <div class="col-3">
                <div class="form-group">
                    <label for="dni">Serie</label>
                    <el-input v-model="serie"></el-input>
                </div>
            </div> -->
            <div class="col-3">
                <div class="form-group">
                    <label for="dni">Asiento</label>
                    <el-input disabled :value="asiento.numero_asiento"></el-input>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <div class="form-group">
                    <label for="dni">Estado de asiento</label>
                    <el-select v-model="estadoAsiento"  popper-class="el-select-customers"
                        placeholder="Estado asiento"
                        
                        >
                        <el-option v-for="estado in estadosAsientos" :key="estado.id" :value="estado.id" :label="estado.nombre">

                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="dni">Precio</label>
                    <el-input v-model="precio" type="number"></el-input>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12 d-flex justify-content-center">
                <el-button :loading="loading" type="primary" @click="realizarVenta">Guardar</el-button>
                <el-button type="secondary" @click="onClose">Cancelar</el-button>
            </div>
        </div>
    </el-dialog>
</template>
<script>
export default {

    props:{
        visible: {
            type: Boolean,
            required: true,
            default: false,
        },
        asiento: {
            type: Object,
            required: true,
            default: {},
        },
        programacion: {
            type: Object,
            required: true,
            default: {},
        },
        estadosAsientos:{
            type:Array,
            required:true,
            default:() => []
        },
        fechaSalida: {
            type: String,
            required: true,
            default: '',
        },
        serie:{
            type:String|null,
            required:true,
            default:null

        }
    },
    data(){
        return ({
            pasajeros:[],
            pasajero:null,
            loadingPasajero:false,
            

            estadoAsiento:null,
            pasajeroId:null,
            precio:null,

            loading:false,

        });
    },
    methods:{
        onClose(){
            this.$emit("update:visible", false);
            
            this.pasajeroId = null;
            this.estadoAsiento = null;
            this.precio = null;
        },
        async searchPasajero(input=''){
            this.loadingPasajero = true;
            const { data } = await this.$http.get(`/transportes/encomiendas/get-clientes?search=${input}`);
            this.loadingPasajero = false;
            this.pasajeros = data.clientes;
        },
        onCreate(){
            this.searchPasajero();
        },
        realizarVenta(){
            let data = {
                pasajero_id:this.pasajeroId,
                asiento_id:this.asiento.id,
                estado_asiento_id:this.estadoAsiento,
                serie:this.serie,
                programacion_id:this.programacion.id,
                fecha_salida:this.fechaSalida,
                precio:this.precio
            };
            this.loading = true;
            this.$http.post('/transportes/sales/realizar-venta-boleto',data)
            .then( ({data}) => {
                this.loading = false;
                this.$emit('onUpdateItem');
                this.$message({
                    type: 'success',
                    message: data.message
                });
                this.onClose()
            }).catch( error => {
                this.axiosError(error);
            }).finally(() => {
                this.loading = false;
            });
        }

    }
}
</script>
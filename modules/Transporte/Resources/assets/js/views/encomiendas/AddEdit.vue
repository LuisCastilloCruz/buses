<template>
    <el-dialog
        :title="title"
        :visible="visible"
        @close="onClose"
        @open="onCreate"
        width="800px"
        :close-on-click-modal="false"
        
    >
        <form v-loading="load" autocomplete="off" @submit.prevent="onSubmit">
            <div class="form-body">
               
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="dni">Remitente</label>
                            <el-select v-model="encomienda.remitente_id" filterable remote  popper-class="el-select-customers"
                                placeholder="Buscar remitente"
                                :remote-method="searchRemitente"
                                :loading="loadingRemitente"
                                >
                                <el-option v-for="remitente in remitentes" :key="remitente.id" :value="remitente.id" :label="remitente.name">

                                </el-option>
                            </el-select>
                            <div v-if="errors.remitente_id" class="invalid-feedback">{{ errors.remitente_id[0] }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="nombre">Destinatario</label>
                            <el-select v-model="encomienda.destinatario_id" filterable remote  popper-class="el-select-customers"
                                placeholder="Buscar destinatario"
                                :remote-method="searchDestinatario"
                                :loading="loadingDestinatario"
                                >
                                <el-option v-for="destinatario in destinatarios" :key="destinatario.id" :value="destinatario.id" :label="destinatario.name">
                                </el-option>
                            </el-select>
                            <!-- <input type="text" id="nombre" class="form-control" v-model="form.nombre" :class="{ 'is-invalid': errors.nombre }"/> -->
                            <div v-if="errors.nombre" class="invalid-feedback">{{ errors.nombre[0] }}</div>
                        </div>
                    </div>
                </div>
                <!-- <div class="row justify-content-center">
                    <div class="col-6">
                        <el-select v-model="terminalId" filterable remote  popper-class="el-select-customers"
                            placeholder="Buscar terminal"
                            :remote-method="searchRemitente"
                            :loading="loadingRemitente"
                            >
                            <el-option v-for="terminal in terminales" :key="terminal.id" :value="terminal.id" :label="terminal.nombre">
                            </el-option>
                        </el-select>
                    </div>
                </div> -->
                <div class="row mt-3">
                    <div class="col-4">
                        <div class="form-group">
                            <label for="">Origen</label>
                            <el-select v-model="terminalId" filterable remote  popper-class="el-select-customers"
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
                            <el-select v-model="destinoId" :loading="loadingDestinos" popper-class="el-select-customers" placeholder="Destino" >
                                <el-option v-for="destino in destinos" :key="destino.id" :value="destino.destino.id" :label="`${destino.destino.nombre}`">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="">Fecha salida</label>
                            <el-date-picker
                            :class="{'is-invalid':errors.fecha_salida}"
                            v-model="encomienda.fecha_salida"
                            type="date"
                            value-format="yyyy-MM-dd"
                            placeholder="Fecha salida" @change="seleccionarFecha">
                            </el-date-picker>
                            <div v-if="errors.fecha_salida" class="invalid-feedback">{{ errors.fecha_salida[0] }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="">Pago</label>
                            <el-select v-model="encomienda.estado_pago_id"  popper-class="el-select-customers" :class="{'is-invalid':errors.estado_pago_id}" placeholder="Pago" >
                                <el-option v-for="estadoPago in estadosPago" :key="estadoPago.id" :value="estadoPago.id" :label="`${estadoPago.nombre}`">
                                </el-option>
                            </el-select>
                            <div v-if="errors.estado_pago_id" class="invalid-feedback">{{ errors.estado_pago_id[0] }}</div>
                        </div>

                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="">Estado Envio</label>
                            <el-select v-model="encomienda.estado_envio_id"  popper-class="el-select-customers" :class="{'is-invalid':errors.estado_envio_id}" placeholder="Estado encomienda" >
                                <el-option v-for="estadoEnvio in estadosEnvio" :key="estadoEnvio.id" :value="estadoEnvio.id" :label="`${estadoEnvio.nombre}`">
                                </el-option>
                            </el-select>
                            <div v-if="errors.estado_envio_id" class="invalid-feedback">{{ errors.estado_envio_id[0] }}</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <h3>Lista de productos</h3>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-3">
                                <el-input v-model="producto.nombre" placeholder="Nombre"></el-input>
                            </div>
                            <div class="col-3">
                                <el-input v-model="producto.descripcion" placeholder="Descripción"></el-input>
                            </div>
                            <div class="col-3">
                                <el-input type="number" v-model="producto.precio" placeholder="Precio"></el-input>
                            </div>
                            <el-button type="primary" @click="agregarProducto">Agregar</el-button>
                        </div>

                    </div>
                    <div class="col-12 mt-2">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripcion</th>
                                    <th>Precio</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr v-for="(producto,index) in productos" :key="index">
                                    <td>{{ producto.nombre }}</td>
                                    <td>{{ producto.descripcion }}</td>
                                    <td>{{ producto.precio }}</td>
                                    <th> 
                                        <el-button type="danger" @click="eliminarProducto(index)">
                                            <i class="fa fa-trash"></i>
                                        </el-button> 
                                    </th>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>

                <div class="row mt-3 mb-3">
                    <div class="col-12">
                        <h3>Programaciones</h3>
                    </div>
                    <div class="col-12 table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Vehículo</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Hr. Salida</th>
                                    <th>Tiempo aproximado</th>
                                    <th></th>
                                </tr>

                            </thead>
                            <tbody v-loading="loadingTable">
                                <tr v-for="programacion in programaciones" :key="programacion.id">
                                    <th>
                                        <el-checkbox v-model="encomienda.programacion_id">

                                        </el-checkbox>
                                    </th>
                                    <td>{{ programacion.vehiculo.placa }}</td>
                                    <td>{{ programacion.origen.nombre }}</td>
                                    <td>{{ programacion.destino.nombre }}</td>
                                    <td>{{ programacion.hora_salida }}</td>
                                    <td>{{ programacion.tiempo_aproximado }} h</td>
                                    <!-- <td >
                                        <div v-if="edit" class="text-center">
                                            <el-button type="primary" @click="guardarEncomienda(programacion)">
                                                Seleccionar
                                            </el-button>
                                        </div>
                                    </td> -->
                                </tr>
                            </tbody>

                        </table>
                    </div>
                </div>
               
                <!-- <div class="form-group">
                    <label for="licencia">Terminal</label>
                    <input type="text" id="licencia" class="form-control" v-model="form.licencia" :class="{ 'is-invalid': errors.licencia }"/>
                    <div v-if="errors.licencia" class="invalid-feedback">{{ errors.licencia[0] }}</div>
                </div>
                <div class="form-group">
                    <label for="categoria">Categoria</label>
                    <input type="text" id="categoria" class="form-control" v-model="form.categoria" :class="{ 'is-invalid': errors.categoria }"/>
                    <div v-if="errors.categoria" class="invalid-feedback">{{ errors.categoria[0] }}</div>
                </div> -->

                <!-- <div class="row text-center mt-2">
                    <div class="col-6">
                        <el-button
                            native-type="submit"
                            :disabled="loading"
                            type="primary"
                            class="btn-block"
                            :loading="loading"
                        >Guardar</el-button
                        >
                    </div>
                    <div class="col-6">
                        <el-button class="btn-block" @click="onClose">Cancelar</el-button>
                    </div>
                </div> -->
            </div>
        </form>
    </el-dialog>
</template>

<script>
export default {
    props: {
        visible: {
            type: Boolean,
            required: true,
            default: false,
        },
        itemEncomienda:{
            type:Object,
            required:false,
            default:null
        },
        estadosPago:{
            type:Array,
            required:true,
        },
        estadosEnvio:{
            type:Array,
            required:true,
        },
        edit:{
            type:Boolean,
            required:true,
            default:false,
        }
    },
    data() {
        return {
            encomienda: {
                descripcion:null,
                remitente_id:null,
                destinatatio_id:null,
                estado_pago_id:null,
                estado_envio_id:null,
                programacion_id:null,
                fecha_salida:null,
            },
            title: "Encomienda",
            errors: {},
            loading: false,
            loadingRemitente:false,
            loadingDestinatario:false,
            loadingTerminales:false,
            loadingDestinos:false,
            loadingTable:false,
            remitentes:[],
            destinatarios:[],
            destinos:[],
            terminales:[],
            terminalId:null,
            destinoId:null,
            programaciones:[],
            programacion:null,
            load:false,
            productos:[],
            producto:{}
        };
    },
    watch:{
        terminalId(newVal){
            if(newVal)this.searchDestinos();
            
        },
    },
    methods: {

        async seleccionarFecha(){
            // console.log(this.encomienda.fecha_salida);
            this.loadingTable = true;
            this.programaciones = [];
            let data = {
                origen_id:this.terminalId,
                destino_id:this.destinoId,
                fecha_salida:this.encomienda.fecha_salida
            }
            const { data:programaciones } = await this.$http.post(`/transportes/encomiendas/programaciones-disponibles`,data);
            this.loadingTable = false;
            this.programaciones = programaciones.programaciones;
            
        },

        async initializeSelects(){
            //remitentes
            this.loadingRemitente = true;
            const { data:remitentes } = await this.$http.get(`/transportes/encomiendas/get-clientes?search=`);
            this.loadingRemitente = false;
            this.remitentes = remitentes.clientes;

            //destinatarios
            this.loadingDestinatario = true;
            const { data:destinatarios } = await this.$http.get(`/transportes/encomiendas/get-clientes?search=`);
            this.loadingDestinatario = false;
            this.destinatarios = destinatarios.clientes;
        },
        async searchRemitente(input){
            this.loadingRemitente = true;
            const { data } = await this.$http.get(`/transportes/encomiendas/get-clientes?search=${input}`);
            this.loadingRemitente = false;
            this.remitentes = data.clientes;
        },
        async searchDestinatario(input){
            this.loadingDestinatario = true;
            const { data } = await this.$http.get(`/transportes/encomiendas/get-clientes?search=${input}`);
            this.loadingDestinatario = false;
            this.destinatarios = data.clientes;
        },
        async searchTerminales(input = ''){
            this.loadingTerminales = true;
            const { data } = await this.$http.get(`/transportes/encomiendas/get-terminales?search=${input}`);
            this.loadingTerminales = false;
            this.terminales = data.terminales;
        },

        async searchDestinos(){
            this.loadingDestinos = true;
            const { data } = await this.$http.get(`/transportes/encomiendas/${this.terminalId}/get-destinos`);
            this.loadingDestinos = false;
            this.destinos = data.programaciones;
        },
        onUpdate() {
            this.loading = true;
            this.$http
                .put(`/transportes/choferes/${this.chofer.id}/update`, this.form)
                .then((response) => {
                    this.$emit("onUpdateItem", response.data.data);
                    this.onClose();
                })
                .finally(() => {
                    this.loading = false;
                    this.errors = {};
                })
                .catch((error) => {
                    this.axiosError(error);
                });
        },
        async onStore() {
            await this.$http.post(`/transportes/encomiendas/store`,this.encomienda)
            .then(({ data }) => {
                this.$emit('onAddItem',data.encomienda);
                this.onClose();
            }).finally(() => {
                this.errors = {};
            }).catch((error) => {
                this.axiosError(error);
            });    
        },
        onSubmit() {
            if (this.chofer) {
                this.onUpdate();
            } else {
                this.onStore();
            }
        },
        onClose() {
            this.programaciones = [];
            this.terminalId = null;
            this.destinoId = null;
            // this.onEdit = false;
            this.$emit("update:visible", false);
        },
        guardarEncomienda(programacion){
            this.encomienda.programacion_id = programacion.id;
            this.$msgbox({
                title:'¿Desea guardar la encomienda?',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel',
                beforeClose:async(action,instance,done) => {
                    if(action === 'confirm'){
                        instance.confirmButtonLoading = true;
                        instance.confirmButtonText = 'Guardando...';
                        await this.onStore();
                        instance.confirmButtonLoading = false;
                        done();
                    }else {
                        done();
                    }
                }

            });
        },
        agregarProducto(evt){
            if(this.producto.nombre && this.producto.descripcion && this.producto.precio){
                this.productos.push(this.producto);
                this.producto = {};
            }
        },
        eliminarProducto(index){
            this.productos.splice(index,1);
        },
        async onCreate() {
            this.load = true;
            await this.initializeSelects();
            await this.searchTerminales();
            this.load = false;

           
            if(this.edit){
                this.encomienda = {...this.itemEncomienda};
                this.terminalId = this.encomienda.programacion.terminal_origen_id;
                this.destinoId = this.encomienda.programacion.terminal_destino_id;
                this.programaciones.push(this.encomienda.programacion);
            }else {
                this.encomienda = {
                    descripcion:null,
                    remitente_id:null,
                    destinatatio_id:null,
                    estado_pago_id:null,
                    estado_envio_id:null,
                    programacion_id:null,
                    fecha_salida:null,
                }
            }
            // if (this.chofer) {
            //     this.form = this.chofer;
            //     this.title = "Editar chofer";
            // } else {
            //     this.title = "Crear chofer";
            //     this.form = {
            //         active: true,
            //     };
            // }
        },
    },
};
</script>
